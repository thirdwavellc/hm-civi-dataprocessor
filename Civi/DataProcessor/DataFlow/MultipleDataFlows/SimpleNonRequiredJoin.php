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
use Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface;

class SimpleNonRequiredJoin  extends  SimpleJoin {

  /**
   * @var WhereClauseInterface[]
   */
  protected $filterClauses = array();

  public function __construct($left_prefix = null, $left_field = null, $right_prefix = null, $right_field = null, $type = "INNER") {
    $type = 'LEFT';
    parent::__construct($left_prefix, $left_field, $right_prefix, $right_field, $type);
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
    $configuration[' type'] = 'LEFT';
    return parent::setConfiguration($configuration);
  }


  /**
   * @param WhereClauseInterface $clause
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function addFilterClause(WhereClauseInterface $clause) {
    $this->filterClauses[] = $clause;
    return $this;
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

    $extraClause  = "";
    $dataFlow = $sourceDataFlowDescription->getDataFlow();
    if ($dataFlow  instanceof  SqlTableDataFlow) {
      $whereClauses = $dataFlow->getWhereClauses();
      foreach($whereClauses as $whereClause) {
        $this->filterClauses[] = $whereClause;
        $dataFlow->removeWhereClause($whereClause);
      }
    }
    if (count($this->filterClauses)) {
      $extraClauses = array();
      foreach($this->filterClauses as $filterClause) {
        $extraClauses[] = $filterClause->getWhereClause();
      }
      $extraClause = " AND (".implode(" AND ", $extraClauses). ")";
    }

    return "{$this->type} JOIN `{$table}` `{$table_alias}` {$joinClause} {$extraClause}";
  }


}