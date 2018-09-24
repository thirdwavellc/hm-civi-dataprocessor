<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
use Civi\DataProcessor\DataFlow\SqlDataFlow\SimpleWhereClause;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin;
use Civi\DataProcessor\DataSpecification\AggregationField;
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

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
   * @var array<\Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription>
   */
  protected $additionalDataFlowDescriptions = array();

  /**
   * @var AbstractProcessorType
   */
  protected $dataProcessor;

  /**
   * @var array
   */
  protected $configuration;

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
   * @param AbstractProcessorType $dataProcessor
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setDataProcessor(AbstractProcessorType $dataProcessor) {
    $this->dataProcessor = $dataProcessor;
    return $this;
  }

  /**
   * @param array $configuration
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setConfiguration($configuration) {
    $this->configuration = $configuration;
    return $this;
  }

  /**
   * Initialize this data source.
   *
   * @throws \Exception
   */
  public function initialize() {
    if (!$this->primaryDataFlow) {
      $this->primaryDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
    }
    if (!$this->dataFlow) {
      $this->addFilters($this->configuration);

      if (count($this->customGroupDataFlowDescriptions) || count($this->additionalDataFlowDescriptions)) {
        $this->dataFlow = new CombinedSqlDataFlow('', $this->primaryDataFlow->getTable(), $this->primaryDataFlow->getTableAlias());
        $this->dataFlow->addSourceDataFlow(new DataFlowDescription($this->primaryDataFlow));
        foreach ($this->customGroupDataFlowDescriptions as $customGroupDataFlowDescription) {
          $this->dataFlow->addSourceDataFlow($customGroupDataFlowDescription);
        }
        foreach ($this->additionalDataFlowDescriptions as $additionalDataFlowDescription) {
          $this->dataFlow->addSourceDataFlow($additionalDataFlowDescription);
        }
      }
      else {
        $this->dataFlow = $this->primaryDataFlow;
      }
    }
  }

  protected function reset() {
    $this->primaryDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
    $this->dataFlow = null;
    $this->additionalDataFlowDescriptions = array();
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
        $customGroupDataFlow = $this->ensureCustomGroup($spec->customGroupTableName, $spec->customGroupName);
        $customGroupTableAlias = $customGroupDataFlow->getTableAlias();
        $customGroupDataFlow->addWhereClause(
          new SimpleWhereClause($customGroupTableAlias, $spec->customFieldColumnName, $filter['op'], $filter['value'])
        );
      } else {
        $entityDataFlow = $this->ensureEntity();
        $entityDataFlow->addWhereClause(new SimpleWhereClause($this->primaryDataFlow->getTableAlias(), $spec->name, $filter['op'], $filter['value']));
      }
    }
  }

  /**
   * Ensure that filter field is accesible in the query
   *
   * @param String $fieldName
   * @return DataFlowDescription|null
   * @throws \Exception
   */
  public function ensureField($fieldName) {
    if ($this->getAvailableFilterFields()->doesFieldExist($fieldName)) {
      $spec = $this->getAvailableFilterFields()->getFieldSpecificationByName($fieldName);
      if ($spec instanceof CustomFieldSpecification) {
        return $this->ensureCustomGroup($spec->customGroupTableName, $spec->customGroupName);
      }
    }
  }

  /**
   * Ensure a custom group is added the to the data flow.
   *
   * @param $customGroupTableName
   * @param $customGroupName
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  protected function ensureCustomGroup($customGroupTableName, $customGroupName) {
    if (isset($this->customGroupDataFlowDescriptions[$customGroupName])) {
      return $this->customGroupDataFlowDescriptions[$customGroupName]->getDataFlow();
    } elseif ($this->primaryDataFlow && $this->primaryDataFlow->getTable() == $customGroupTableName) {
      return $this->primaryDataFlow;
    }
    $customGroupTableAlias = $this->getSourceName().'_'.$customGroupName;
    $join = new SimpleJoin($this->getSourceName(), 'id', $customGroupTableAlias, 'entity_id', 'LEFT');
    $join->setDataProcessor($this->dataProcessor);
    $this->customGroupDataFlowDescriptions[$customGroupName] = new DataFlowDescription(
      new SqlTableDataFlow($customGroupTableName, $customGroupTableAlias, new DataSpecification()),
      $join
    );
    return $this->customGroupDataFlowDescriptions[$customGroupName]->getDataFlow();
  }

  /**
   * Ensure that the entity table is added the to the data flow.
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  protected function ensureEntity() {
    if ($this->primaryDataFlow && $this->primaryDataFlow->getTable() === $this->getTable()) {
      return $this->primaryDataFlow;
    } elseif (empty($this->primaryDataFlow)) {
      $this->primaryDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
      return $this->primaryDataFlow;
    }
    foreach($this->additionalDataFlowDescriptions as $additionalDataFlowDescription) {
      if ($additionalDataFlowDescription->getDataFlow()->getTable() == $this->getTable()) {
        return $additionalDataFlowDescription->getDataFlow();
      }
    }
    $entityDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
    $join = new SimpleJoin($this->getSourceName(), 'id', $this->primaryDataFlow->getTableAlias(), 'entity_id', 'LEFT');
    $join->setDataProcessor($this->dataProcessor);
    $additionalDataFlowDescription = new DataFlowDescription($entityDataFlow,$join);
    $this->additionalDataFlowDescriptions[] = $additionalDataFlowDescription;
    return $additionalDataFlowDescription->getDataFlow();
  }

  /**
   * Sets the join specification to connect this source to other data sources.
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface $join
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setJoin(JoinInterface $join) {
    foreach($this->customGroupDataFlowDescriptions as $idx => $customGroupDataFlowDescription) {
      if ($join->worksWithDataFlow($customGroupDataFlowDescription->getDataFlow())) {
        $this->primaryDataFlow = $customGroupDataFlowDescription->getDataFlow();
        unset($this->customGroupDataFlowDescriptions[$idx]);
        unset($this->dataFlow);
      }
    }
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|\Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  public function getDataFlow() {
    $this->initialize();
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
        $customGroupDataFlow = $this->ensureCustomGroup($fieldSpecification->customGroupTableName, $fieldSpecification->customGroupName);
        $customGroupDataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->alias, $fieldSpecification);
      } else {
        $entityDataFlow = $this->ensureEntity();
        $entityDataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->alias, $fieldSpecification);
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
        $customGroupDataFlow = $this->ensureCustomGroup($fieldSpecification->customGroupTableName, $fieldSpecification->customGroupName);
        $customGroupDataFlow->addAggregateField($fieldSpecification);
      } else {
        $entityDataFlow = $this->ensureEntity();
        $entityDataFlow->addAggregateField($fieldSpecification);
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