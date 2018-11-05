<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

class SimpleJoin implements JoinInterface, SqlJoinInterface {

  private $isInitialized = false;

  /**
   * @var string
   *   The name of the left field
   */
  protected $left_field;

  /**
   * @var string
   *   The name of the right field
   */
  protected $right_field;

  /**
   * @var string
   *   The prefix for the left field, or in SQL join mode the left table
   */
  protected $left_prefix;

  /**
   * @var string
   *   The prefix for the right field, or in SQL join mode the right table
   */
  protected $right_prefix;

  /**
   * @var String
   */
  protected $right_table;

  /**
   * @var String
   */
  protected $left_table;

  /**
   * @var String
   *   The join type, e.g. INNER, LEFT, OUT etc..
   */
  protected  $type = "INNER";

  /**
   * @var AbstractProcessorType
   */
  private $dataProcessor;

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
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function setConfiguration($configuration) {
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
    return $this;
  }

  /**
   * @param AbstractProcessorType $dataProcessor
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   * @throws \Exception
   */
  public function setDataProcessor(AbstractProcessorType $dataProcessor) {
    $this->dataProcessor = $dataProcessor;
  }

  /**
   * Returns true when this join is compatible with this data flow
   *
   * @param \Civi\DataProcessor\DataFlow\AbstractDataFlow $
   * @return bool
   */
  public function worksWithDataFlow(AbstractDataFlow $dataFlow) {
    if (!$dataFlow instanceof SqlDataFlow) {
      return false;
    }
    $this->initialize();
    if ($dataFlow->getTableAlias() == $this->left_table) {
      return true;
    }
    if ($dataFlow->getTableAlias() == $this->right_table) {
      return true;
    }
    return false;
  }

  public function initialize() {
    if ($this->isInitialized) {
      return $this;
    }
    if ($this->left_prefix && $this->left_field) {
      $this->left_table = $this->left_prefix;
      $left_source = $this->dataProcessor->getDataSourceByName($this->left_prefix);
      if ($left_source) {
        $leftTable = $left_source->ensureField($this->left_field);
        if ($leftTable && $leftTable instanceof SqlTableDataFlow) {
          $this->left_table = $leftTable->getTableAlias();
        }
      }
    }
    if ($this->right_prefix && $this->right_field) {
      $this->right_table = $this->right_prefix;
      $right_source = $this->dataProcessor->getDataSourceByName($this->right_prefix);
      if ($right_source) {
        $rightTable = $right_source->ensureField($this->right_field);
        if ($rightTable && $rightTable instanceof SqlTableDataFlow) {
          $this->right_table = $rightTable->getTableAlias();
        }
      }
    }

    $this->isInitialized = true;
    return $this;
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
    } elseif ($this->type == 'LEFT') {
      if (isset($left_record[$this->left_prefix.$this->left_field]) && !isset($right_record[$this->right_prefix.$this->right_field])) {
        return true;
      }
    } elseif ($this->type == 'RIGHT') {
      if (!isset($left_record[$this->left_prefix.$this->left_field]) && isset($right_record[$this->right_prefix.$this->right_field])) {
        return true;
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
    $this->initialize();
    $joinClause = "";
    if ($sourceDataFlowDescription->getJoinSpecification()) {
      $joinClause = "ON `{$this->left_table}`.`{$this->left_field}` = `{$this->right_table}`.`{$this->right_field}`";
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