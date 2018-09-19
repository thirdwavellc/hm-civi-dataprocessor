<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

class SimpleJoin implements JoinInterface, SqlJoinInterface {

  /**
   * @var string
   *   The name of the left field
   */
  public $left_field;

  /**
   * @var string
   *   The name of the right field
   */
  public $right_field;

  /**
   * @var string
   *   The prefix for the left field, or in SQL join mode the left table
   */
  public $left_prefix;

  /**
   * @var string
   *   The prefix for the right field, or in SQL join mode the right table
   */
  public $right_prefix;

  public function __construct() {

  }

  /**
   * @return string
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/joins/simple_join';
  }

  /**
   * @param array $configuration
   * @param int $data_processor_id
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function initialize($configuration, $data_processor_id) {
    $this->left_field = $configuration['left_field'];
    $this->left_prefix = $configuration['left_prefix'];
    $this->right_field = $configuration['right_field'];
    $this->right_prefix = $configuration['right_prefix'];
  }

  /**
   * Validates the right record against the left record and returns true when the right record
   * has a successfull join with the left record. Otherwise false.
   *
   * @param $left_record
   * @param $right_record
   *
   * @return mixed
   */
  public function isJoinable($left_record, $right_record) {
    if (isset($left_record[$this->left_prefix.$this->left_field]) && isset($right_record[$this->right_prefix.$this->right_field])) {
      if ($left_record[$this->left_prefix.$this->left_field] == $right_record[$this->right_prefix.$this->right_field]) {
        return TRUE;
      }
    }
    return false;
  }

  /**
   * Returns the SQL join statement
   *
   * For example:
   *  INNER JOIN civicrm_contact source_3 ON source_3.id = source_2.contact_id
   * OR
   *  LEFT JOIN civicrm_contact source_3 ON source3.id = source_2.contact_id
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription $sourceDataFlowDescription
   *   The source data flow description used to genereate the join stament.
   *
   * @return string
   */
  public function getJoinClause(DataFlowDescription $sourceDataFlowDescription) {
    $joinClause = "";
    if ($sourceDataFlowDescription->getJoinSpecification()) {
      $joinClause = "ON `{$this->left_prefix}`.`{$this->left_field}` = `{$this->right_prefix}`.`{$this->right_field}`";
    }
    return "INNER JOIN `{$sourceDataFlowDescription->getDataFlow()->getTable()}` `{$sourceDataFlowDescription->getDataFlow()->getTableAlias()}` {$joinClause}";
  }


}