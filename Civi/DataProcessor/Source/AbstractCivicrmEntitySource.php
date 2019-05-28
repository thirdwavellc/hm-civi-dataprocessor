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
use Civi\DataProcessor\DataSpecification\Utils as DataSpecificationUtils;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

abstract class AbstractCivicrmEntitySource extends AbstractSource {

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


  /**
   * Initialize this data source.
   *
   * @throws \Exception
   */
  public function initialize() {
    if (!$this->primaryDataFlow) {
      $this->primaryDataFlow = $this->getEntityDataFlow();
    }
    $this->addFilters($this->configuration);
    if (count($this->customGroupDataFlowDescriptions) || count($this->additionalDataFlowDescriptions)) {
      $this->dataFlow = new CombinedSqlDataFlow('', $this->primaryDataFlow->getTable(), $this->primaryDataFlow->getTableAlias());
      $this->dataFlow->addSourceDataFlow(new DataFlowDescription($this->primaryDataFlow));
      foreach ($this->additionalDataFlowDescriptions as $additionalDataFlowDescription) {
        $this->dataFlow->addSourceDataFlow($additionalDataFlowDescription);
      }
      foreach ($this->customGroupDataFlowDescriptions as $customGroupDataFlowDescription) {
        $this->dataFlow->addSourceDataFlow($customGroupDataFlowDescription);
      }
    }
    else {
      $this->dataFlow = $this->primaryDataFlow;
    }
  }

  protected function reset() {
    $this->primaryDataFlow = $this->getEntityDataFlow();
    $this->dataFlow = null;
    $this->additionalDataFlowDescriptions = array();
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected function getEntityDataFlow() {
    return new SqlTableDataFlow($this->getTable(), $this->getSourceName(), $this->getSourceTitle());
  }

  /**
   * Load the fields from this entity.
   *
   * @param DataSpecification $dataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function loadFields(DataSpecification $dataSpecification, $fieldsToSkip=array()) {
    $daoClass = \CRM_Core_DAO_AllCoreTables::getFullName($this->getEntity());
    $aliasPrefix = $this->getSourceName().'_';

    DataSpecificationUtils::addDAOFieldsToDataSpecification($daoClass, $dataSpecification, $fieldsToSkip, '', $aliasPrefix);
  }

  /**
   * Add custom fields to the available fields section
   *
   * @param DataSpecification $dataSpecification
   * @param bool $onlySearchAbleFields
   * @param $entity
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   * @throws \Exception
   */
  protected function loadCustomGroupsAndFields(DataSpecification $dataSpecification, $onlySearchAbleFields, $entity=null) {
    if (!$entity) {
      $entity = $this->getEntity();
    }
    $aliasPrefix = $this->getSourceName() . '_';
    DataSpecificationUtils::addCustomFieldsToDataSpecification($entity, $dataSpecification, $onlySearchAbleFields, $aliasPrefix);
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
        $this->addFilter($filter_alias, $filter_field['op'], $filter_field['value']);
      }
    }
  }

  /**
   * Adds an inidvidual filter to the data source
   *
   * @param $filter_field_alias
   * @param $op
   * @param $values
   *
   * @throws \Exception
   */
  protected function addFilter($filter_field_alias, $op, $values) {
    if ($this->getAvailableFilterFields()->doesFieldExist($filter_field_alias)) {
      $spec = $this->getAvailableFilterFields()->getFieldSpecificationByName($filter_field_alias);
      if ($spec instanceof CustomFieldSpecification) {
        $customGroupDataFlow = $this->ensureCustomGroup($spec->customGroupTableName, $spec->customGroupName);
        $customGroupTableAlias = $customGroupDataFlow->getTableAlias();
        $customGroupDataFlow->addWhereClause(
          new SimpleWhereClause($customGroupTableAlias, $spec->customFieldColumnName, $op, $values, $spec->type, TRUE)
        );
      } else {
        $entityDataFlow = $this->ensureEntity();
        $entityDataFlow->addWhereClause(new SimpleWhereClause($this->getSourceName(), $spec->name,$op, $values, $spec->type, TRUE));
      }
    }
  }

  /**
   * Ensure that filter field is accesible in the query
   *
   * @param String $fieldName
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|null
   * @throws \Exception
   */
  public function ensureField($fieldName) {
    if ($this->getAvailableFilterFields()->doesFieldExist($fieldName)) {
      $spec = $this->getAvailableFilterFields()->getFieldSpecificationByName($fieldName);
      if ($spec instanceof CustomFieldSpecification) {
        return $this->ensureCustomGroup($spec->customGroupTableName, $spec->customGroupName);
      }
      return $this->ensureEntity();
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
    $this->ensureEntity(); // Ensure the entity as we need it before joining.
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
      $this->primaryDataFlow = $this->getEntityDataFlow();
      return $this->primaryDataFlow;
    }
    foreach($this->additionalDataFlowDescriptions as $additionalDataFlowDescription) {
      if ($additionalDataFlowDescription->getDataFlow()->getTable() == $this->getTable()) {
        return $additionalDataFlowDescription->getDataFlow();
      }
    }
    $entityDataFlow = $this->getEntityDataFlow();
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
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFields() {
    if (!$this->availableFields) {
      $this->availableFields = new DataSpecification();
      $this->loadFields($this->availableFields, array());
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
      $this->loadFields($this->availableFilterFields, array());
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
        if (!$customGroupDataFlow->getDataSpecification()->doesFieldExist($fieldSpecification->alias)) {
          $customGroupDataFlow->getDataSpecification()
            ->addFieldSpecification($fieldSpecification->alias, $fieldSpecification);
        }
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
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  public function getPrimaryDataFlow() {
    return $this->primaryDataFlow;
  }

}