<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlDataFlow\SimpleWhereClause;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin;
use Civi\DataProcessor\DataSpecification\AggregationField;
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

abstract class AbstractCivicrmEntitySource implements SourceInterface {

  /**
   * @var String
   */
  private $sourceName;

  /**
   * @var String
   */
  private $sourceTitle;

  /**
   * @var \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected $dataFlow;

  /**
   * @var \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected $primaryDataFlow;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFields;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFilterFields;

  /**
   * @var array
   */
  protected $whereClauses = array();


  /**
   * @var array<\Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription>
   */
  protected $customGroupDataFlowDescriptions = array();

  /**
   * Returns the entity name
   *
   * @return String
   */
  abstract protected function getEntity();

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  abstract protected function getTable();

  public function __construct() {

  }

  /**
   * Initialize this data source.
   *
   * @param array $configuration
   * @param string $source_name
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function initialize($configuration) {
    $this->primaryDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
    $this->addFilters($configuration);

    if (count($this->customGroupDataFlowDescriptions)) {
      $this->dataFlow = new CombinedSqlDataFlow('', $this->primaryDataFlow->getTable(), $this->primaryDataFlow->getTableAlias());
      $this->dataFlow->addSourceDataFlow(new DataFlowDescription($this->primaryDataFlow));
      foreach($this->customGroupDataFlowDescriptions as $customGroupDataFlowDescription) {
        $this->dataFlow->addSourceDataFlow($customGroupDataFlowDescription);
      }
    } else {
      $this->dataFlow = $this->primaryDataFlow;
    }

    return $this;
  }

  /**
   * Load the fields from this entity.
   *
   * @param DataSpecification $dataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function loadFields(DataSpecification $dataSpecification, $fieldsToSkip=array()) {
    $dao = \CRM_Core_DAO_AllCoreTables::getFullName($this->getEntity());
    $bao = str_replace("DAO", "BAO", $dao);
    $fields = $dao::fields();
    foreach($fields as $field) {
      if (in_array($field['name'], $fieldsToSkip)) {
        continue;
      }
      $type = \CRM_Utils_Type::typeToString($field['type']);
      $options = $bao::buildOptions($field['name']);
      $alias = $this->getSourceName(). '_'.$field['name'];
      $fieldSpec = new FieldSpecification($field['name'], $type, $field['title'], $options, $alias);
      $dataSpecification->addFieldSpecification($fieldSpec->name, $fieldSpec);
    }
  }

  /**
   * Add custom fields to the available fields section
   *
   * @param DataSpecification $dataSpecification
   * @param bool $onlySearchAbleFields
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function loadCustomGroupsAndFields(DataSpecification $dataSpecification, $onlySearchAbleFields) {
    $customGroupToReturnParam = array(
      'custom_field' => array(
        'id',
        'name',
        'label',
        'column_name',
        'data_type',
        'html_type',
        'default_value',
        'attributes',
        'is_required',
        'is_view',
        'is_searchable',
        'help_pre',
        'help_post',
        'options_per_line',
        'start_date_years',
        'end_date_years',
        'date_format',
        'time_format',
        'option_group_id',
        'in_selector',
      ),
      'custom_group' => array(
        'id',
        'name',
        'table_name',
        'title',
        'help_pre',
        'help_post',
        'collapse_display',
        'style',
        'is_multiple',
        'extends',
        'extends_entity_column_id',
        'extends_entity_column_value',
        'max_multiple',
      ),
    );
    $customGroups = \CRM_Core_BAO_CustomGroup::getTree($this->getEntity(), $customGroupToReturnParam,NULL,NULL,NULL,NULL,NULL,NULL,FALSE,FALSE,FALSE);
    foreach($customGroups as $cgId => $customGroup) {
      if ($cgId == 'info') {
        continue;
      }
      foreach($customGroup['fields'] as $field) {
        if (!$onlySearchAbleFields || $field['is_searchable']) {
          $alias = $this->getSourceName() . '_' . $customGroup['name'] . '_' . $field['name'];
          $customFieldSpec = new CustomFieldSpecification(
            $customGroup['name'], $customGroup['table_name'], $customGroup['title'],
            $field['id'], $field['column_name'], $field['name'], $field['data_type'], $field['label'],
            $alias
          );
          $dataSpecification->addFieldSpecification($customFieldSpec->name, $customFieldSpec);
        }
      }
    }
  }

  /**
   * Add the filters to the where clause of the data flow
   *
   * @param $configuration
   * @throws \Exception
   */
  protected function addFilters($configuration) {
    if (isset($configuration['filter']) && is_array($configuration['filter'])) {
      foreach($configuration['filter'] as $filter_alias => $filter_field) {
        $this->addFilter($filter_alias, $filter_field);
      }
    }
  }

  /**
   * Adds an inidvidual filter to the data source
   *
   * @param $filter_alias
   * @param $filter
   *
   * @throws \Exception
   */
  public function addFilter($filter_alias, $filter) {
    if ($this->getAvailableFilterFields()->doesFieldExist($filter_alias)) {
      $spec = $this->getAvailableFilterFields()->getFieldSpecificationByName($filter_alias);
      if ($spec instanceof CustomFieldSpecification) {
        $customGroupDataFlowDescription = $this->ensureCustomGroup($spec->customGroupTableName, $spec->customGroupName);
        $customGroupTableAlias = $customGroupDataFlowDescription->getDataFlow()
          ->getTableAlias();
        $customGroupDataFlowDescription->getDataFlow()->addWhereClause(
          new SimpleWhereClause($customGroupTableAlias, $spec->customFieldColumnName, $filter['op'], $filter['value'])
        );
      } else {
        $this->primaryDataFlow->addWhereClause(new SimpleWhereClause($this->primaryDataFlow->getTableAlias(), $spec->name, $filter['op'], $filter['value']));
      }
    }
  }

  /**
   * Ensure a custom group is added the to the data flow.
   *
   * @param $customGroupTableName
   * @param $customGroupName
   * @return DataFlowDescription
   */
  protected function ensureCustomGroup($customGroupTableName, $customGroupName) {
    if (!isset($this->customGroupDataFlowDescriptions[$customGroupName])) {
      $customGroupTableAlias = $this->getSourceName().'_'.$customGroupName;
      $this->customGroupDataFlowDescriptions[$customGroupName] = new DataFlowDescription(
        new SqlTableDataFlow($customGroupTableName, $customGroupTableAlias, new DataSpecification()),
        new SimpleJoin($this->getSourceName(), 'id', $customGroupTableAlias, 'entity_id', 'LEFT')
      );
    }
    return $this->customGroupDataFlowDescriptions[$customGroupName];
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|\Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function getDataFlow() {
    return $this->dataFlow;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFields() {
    if (!$this->availableFields) {
      $this->availableFields = new DataSpecification();
      $this->loadFields($this->availableFields);
      $this->loadCustomGroupsAndFields($this->availableFields, false);
    }
    return $this->availableFields;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFilterFields() {
    if (!$this->availableFilterFields) {
      $this->availableFilterFields = new DataSpecification();
      $this->loadFields($this->availableFilterFields);
      $this->loadCustomGroupsAndFields($this->availableFilterFields, true);
    }
    return $this->availableFilterFields;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\AggregationField[]
   * @throws \Exception
   */
  public function getAvailableAggregationFields() {
    $fields = $this->getAvailableFields();
    $aggregationFields = array();
    foreach($fields->getFields() as $field) {
      $aggregationFields[$field->alias] = new AggregationField($field, $this);
    }
    return $aggregationFields;
  }

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   * @return SourceInterface
   * @throws \Exception
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification) {
    if ($this->getAvailableFields()->doesFieldExist($fieldSpecification->name)) {
      if ($fieldSpecification instanceof CustomFieldSpecification) {
        $customGroupDataFlowDescription = $this->ensureCustomGroup($fieldSpecification->customGroupTableName, $fieldSpecification->customGroupName);
        $customGroupDataFlowDescription->getDataFlow()->getDataSpecification()->addFieldSpecification($fieldSpecification->alias, $fieldSpecification);
      } else {
        $this->primaryDataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->alias, $fieldSpecification);
      }
    }
  }

  /**
   * Ensures an aggregation field in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function ensureAggregationFieldInSource(FieldSpecification $fieldSpecification) {
    if ($this->getAvailableFields()->doesFieldExist($fieldSpecification->name)) {
      if ($fieldSpecification instanceof CustomFieldSpecification) {
        $customGroupDataFlowDescription = $this->ensureCustomGroup($fieldSpecification->customGroupTableName, $fieldSpecification->customGroupName);
        $customGroupDataFlowDescription->getDataFlow()->addAggregateField($fieldSpecification);
      } else {
        $this->primaryDataFlow->addAggregateField($fieldSpecification);
      }
    }
  }

  /**
   * @return String
   */
  public function getSourceName() {
    return $this->sourceName;
  }

  /**
   * @param String $name
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceName($name) {
    $this->sourceName = $name;
    return $this;
  }

  /**
   * @return String
   */
  public function getSourceTitle() {
    return $this->sourceTitle;
  }

  /**
   * @param String $title
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceTitle($title) {
    $this->sourceTitle = $title;
    return $this;
  }

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/source/configuration';
  }

}