<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;

class SimpleJoin implements JoinInterface, SqlJoinInterface {

  /**
   * @var string
   *   The name of the left field
   */
  private $left_field;

  /**
   * @var string
   *   The name of the right field
   */
  private $right_field;

  /**
   * @var string
   *   The prefix for the left field, or in SQL join mode the left table
   */
  private $left_prefix;

  /**
   * @var string
   *   The prefix for the right field, or in SQL join mode the right table
   */
  private $right_prefix;

  /**
   * @var String
   *   The join type, e.g. INNER, LEFT, OUT etc..
   */
  private $type = "INNER";

  public function __construct($left_prefix = null, $left_field = null, $right_prefix = null, $right_field = null, $type = "INNER") {
    $this->left_prefix = $left_prefix;
    $this->left_field = $left_field;
    $this->right_prefix = $right_prefix;
    $this->right_field = $right_field;
    $this->type = $type;
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
    if (isset($configuration['left_field'])) {
      $this->left_field = $configuration['left_field'];
    }
    if (isset($configuration['left_prefix'])) {
      $this->left_prefix = $configuration['left_prefix'];
    }
    if (isset($configuration['right_field'])) {
      $this->right_field = $configuration['right_field'];
    }
    if (isset($configuration['right_prefix'])) {
      $this->right_prefix = $configuration['right_prefix'];
    }
    if (isset($configuration['type'])) {
      $this->type = $configuration['type'];
    }
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
    if ($sourceDataFlowDescription->getDataFlow() instanceof SqlTableDataFlow) {
      $table = $sourceDataFlowDescription->getDataFlow()->getTable();
      $table_alias = $sourceDataFlowDescription->getDataFlow()->getTableAlias();
    } elseif ($sourceDataFlowDescription->getDataFlow() instanceof CombinedSqlDataFlow) {
      $table = $sourceDataFlowDescription->getDataFlow()->getPrimaryTable();
      $table_alias = $sourceDataFlowDescription->getDataFlow()->getPrimaryTableAlias();
    }

    return "{$this->type} JOIN `{$table}` `{$table_alias}` {$joinClause}";
  }


}