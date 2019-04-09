<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Activity;

use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\SubqueryDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;
use Civi\DataProcessor\DataSpecification\Utils as DataSpecificationUtils;

use CRM_Dataprocessor_ExtensionUtil as E;

class ActivitySource extends AbstractCivicrmEntitySource {

  /**
   * @var SqlTableDataFlow
   */
  protected $activityDataFlow;

  /**
   * @var SqlTableDataFlow
   */
  protected $activityContactDataFlow;

  public function __construct() {
    parent::__construct();

    // Create the activity data flow and data flow description
    $this->activityDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName().'_activity', $this->getSourceTitle());
    DataSpecificationUtils::addDAOFieldsToDataSpecification('CRM_Activity_DAO_Activity', $this->activityDataFlow->getDataSpecification());

    // Create the activity contact data flow and data flow description
    $this->activityContactDataFlow = new SqlTableDataFlow('civicrm_activity_contact', $this->getSourceName().'_activity_contact');
    DataSpecificationUtils::addDAOFieldsToDataSpecification('CRM_Activity_DAO_ActivityContact', $this->activityContactDataFlow->getDataSpecification(), array('id'), '', 'activity_contact_', E::ts('Activity Contact :: '));
  }

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Activity';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_activity';
  }

  /**
   * Returns the default configuration for this data source
   *
   * @return array
   */
  public function getDefaultConfiguration() {
    return array(
      'filter' => array(
        'is_current_revision' => array (
          'op' => '=',
          'value' => '1',
        ),
        'is_deleted' => array (
          'op' => '=',
          'value' => '0',
        ),
        'is_test' => array (
          'op' => '=',
          'value' => '0',
        ),
        'activity_contact_record_type_id' => array (
          'op' => 'IN',
          'value' => array(3), // Activity Targets
        )
      )
    );
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   * @throws \Exception
   */
  protected function getEntityDataFlow() {
    $activityDataDescription = new DataFlowDescription($this->activityDataFlow);

    $join = new SimpleJoin($this->activityDataFlow->getTableAlias(), 'id', $this->activityContactDataFlow->getTableAlias(), 'activity_id');
    $join->setDataProcessor($this->dataProcessor);
    $activityContactDataDescription = new DataFlowDescription($this->activityContactDataFlow, $join);

    // Create the subquery data flow
    $entityDataFlow = new SubqueryDataFlow($this->getSourceName(), $this->getTable(), $this->getSourceName());
    $entityDataFlow->addSourceDataFlow($activityDataDescription);
    $entityDataFlow->addSourceDataFlow($activityContactDataDescription);

    return $entityDataFlow;
  }

  /**
   * Ensure that the entity table is added the to the data flow.
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  protected function ensureEntity() {
    if ($this->primaryDataFlow && $this->primaryDataFlow instanceof SubqueryDataFlow && $this->primaryDataFlow->getPrimaryTable() === $this->getTable()) {
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
    $join = new SimpleJoin($this->getSourceName(), 'id', $this->getSourceName(), 'entity_id', 'LEFT');
    $join->setDataProcessor($this->dataProcessor);
    $additionalDataFlowDescription = new DataFlowDescription($entityDataFlow,$join);
    $this->additionalDataFlowDescriptions[] = $additionalDataFlowDescription;
    return $additionalDataFlowDescription->getDataFlow();
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
    DataSpecificationUtils::addDAOFieldsToDataSpecification('CRM_Activity_DAO_ActivityContact', $dataSpecification, array('id', 'activity_id'), 'activity_contact_', $aliasPrefix, E::ts('Activity contact :: '));
  }

}