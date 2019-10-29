<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Event;

use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleNonRequiredJoin;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\DataSpecification\Utils;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use CRM_Dataprocessor_ExtensionUtil as E;

class EventSource extends AbstractCivicrmEntitySource {

  /**
   * @var SqlTableDataFlow
   */
  protected $locBlockDataFlow;

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Event';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_event';
  }

  /**
   * Load the fields from this entity.
   *
   * @param DataSpecification $dataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function loadFields(DataSpecification $dataSpecification, $fieldsToSkip=array()) {
    parent::loadFields($dataSpecification, $fieldsToSkip);

    // Add the location block fields.
    $daoClass = \CRM_Core_DAO_AllCoreTables::getFullName('LocBlock');
    $aliasPrefix = $this->getSourceName().'_locblock_';
    Utils::addDAOFieldsToDataSpecification($daoClass, $dataSpecification, array('id'), '', $aliasPrefix);
  }

  /**
   * Ensure that the entity table is added the to the data flow.
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  protected function ensureEntity() {
    $dataFlow = parent::ensureEntity();
    $locBlockDataFlowFound = false;
    foreach($this->additionalDataFlowDescriptions as $descriptor) {
      if ($descriptor->getDataFlow()->getName() == $this->getSourceName() . '_locblock') {
        $locBlockDataFlowFound = true;
      }
    }
    if (!$locBlockDataFlowFound) {
      $this->locBlockDataFlow = new SqlTableDataFlow('civicrm_loc_block', $this->getSourceName() . '_locblock');
      $join = new SimpleNonRequiredJoin($this->primaryDataFlow->getName(), 'loc_block_id', $this->getSourceName() . '_locblock', 'id');
      $join->setDataProcessor($this->dataProcessor);
      $this->additionalDataFlowDescriptions[] = new DataFlowDescription($this->locBlockDataFlow, $join);
    }
    return $dataFlow;
  }

  /**
   * Ensure that filter field is accesible in the query
   *
   * @param FieldSpecification $field
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|null
   * @throws \Exception
   */
  public function ensureField(FieldSpecification $field) {
    if ($this->getAvailableFilterFields()->doesAliasExists($field->alias) || $this->getAvailableFilterFields()->doesFieldExist($field->name)) {
      $spec = $this->getAvailableFilterFields()->getFieldSpecificationByAlias($field->alias);
      if (!$spec) {
        $spec = $this->getAvailableFilterFields()->getFieldSpecificationByName($field->name);
      }
      if (stripos($spec->alias, $this->getSourceName().'_locblock_') === 0) {
        $this->ensureEntity();
        return $this->locBlockDataFlow;
      }
    }
    return parent::ensureField($field);
  }

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   * @return SourceInterface
   * @throws \Exception
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification) {
    if (stripos($fieldSpecification->alias, $this->getSourceName().'_locblock_') === 0) {
      $this->ensureEntity();
      $this->locBlockDataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification);
    } else {
      parent::ensureFieldInSource($fieldSpecification);
    }
  }

}
