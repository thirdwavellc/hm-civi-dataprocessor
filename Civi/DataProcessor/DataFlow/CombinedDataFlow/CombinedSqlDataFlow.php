<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\CombinedDataFlow;

use \Civi\DataProcessor\DataFlow\EndOfFlowException;
use Civi\DataProcessor\DataFlow\InvalidFlowException;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\MultipleSourceDataFlows;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\SqlJoinInterface;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use \Civi\DataProcessor\DataSpecification\DataSpecification;


class CombinedSqlDataFlow extends SqlDataFlow implements MultipleSourceDataFlows {

  /**
   * @var \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription[]
   */
  protected $sourceDataFlowDescriptions = array();

  /**
   * @var null|String
   */
  protected $primary_table;

  /**
   * @var null|String
   */
  protected $primary_table_alias;

  /**
   * @var String
   */
  protected $name;

  public function __construct($name = 'combined_sql_data_flow', $primary_table=null, $primary_table_alias=null) {
    parent::__construct();
    $this->primary_table = $primary_table;
    $this->primary_table_alias = $primary_table_alias;
    $this->name = $name;
  }

  /**
   * Adds a source data flow
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription $dataFlowDescription
   * @return void
   * @throws \Civi\DataProcessor\DataFlow\InvalidFlowException
   */
  public function addSourceDataFlow(DataFlowDescription $dataFlowDescription) {
    if (!$dataFlowDescription->getDataFlow() instanceof SqlDataFlow) {
      throw new InvalidFlowException();
    }
    $this->sourceDataFlowDescriptions[] = $dataFlowDescription;
  }

  /**
   * Returns the From Statement.
   *
   * @return string
   */
  public function getFromStatement() {
    $fromStatements = array();
    $sourceDataFlowDescription = reset($this->sourceDataFlowDescriptions);
    if ($sourceDataFlowDescription->getDataFlow() instanceof SqlTableDataFlow) {
      $fromStatements[] = "FROM `{$sourceDataFlowDescription->getDataFlow()->getTable()}` `{$sourceDataFlowDescription->getDataFlow()->getTableAlias()}`";
    } elseif ($sourceDataFlowDescription->getDataFlow() instanceof CombinedSqlDataFlow) {
      $fromStatements[] = "FROM `{$sourceDataFlowDescription->getDataFlow()->getPrimaryTable()}` `{$sourceDataFlowDescription->getDataFlow()->getPrimaryTableAlias()}`";
    }
    $fromStatements = array_merge($fromStatements, $this->getJoinStatement(0));
    return implode(" ", $fromStatements);
  }

  /**
   * Returns the join Statement part.
   *
   * @param int $skip
   * @return string
   */
  public function getJoinStatement($skip=0) {
    $fromStatements = array();
    $i = 0;
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      $i++;
      if ($i > $skip) {
        if ($sourceDataFlowDescription->getJoinSpecification()) {
          $joinStatement = $sourceDataFlowDescription->getJoinSpecification()
            ->getJoinClause($sourceDataFlowDescription);
          if (is_array($joinStatement)) {
            $fromStatements = array_merge($fromStatements, $joinStatement);
          } else {
            $fromStatements[] = $joinStatement;
          }
        }
        if ($sourceDataFlowDescription->getDataFlow() instanceof CombinedSqlDataFlow) {
          $fromStatements = array_merge($fromStatements, $sourceDataFlowDescription->getDataFlow()->getJoinStatement(0));
        }
      }
    }
    return $fromStatements;
  }

  /**
   * Returns an array with the fields for in the select statement in the sql query.
   *
   * @return string[]
   */
  public function getFieldsForSelectStatement() {
    $fields = array();
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      $fields = array_merge($fields, $sourceDataFlowDescription->getDataFlow()->getFieldsForSelectStatement());
    }
    return $fields;
  }

  /**
   * Returns an array with the fields for in the group by statement in the sql query.
   *
   * @return string[]
   */
  public function getFieldsForGroupByStatement() {
    $fields = array();
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      $fields = array_merge($fields, $sourceDataFlowDescription->getDataFlow()->getFieldsForGroupByStatement());
    }
    return $fields;
  }


  /**
   * Returns the next record in an associative array
   *
   * @param string $fieldNamePrefix
   *   The prefix before the name of the field within the record
   * @return array
   * @throws EndOfFlowException
   */
  protected function retrieveNextRecord($fieldNamePrefix='') {
    if (!$this->isInitialized()) {
      $this->initialize();
    }

    if (!$this->dao->fetch()) {
      throw new EndOfFlowException();
    }
    $record = array();
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      foreach ($sourceDataFlowDescription->getDataFlow()->getDataSpecification()->getFields() as $field) {
        $alias = $field->alias;
        $record[$alias] = $this->dao->$alias;
      }
    }
    return $record;
  }

  /**
   * @return DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getDataSpecification() {
    if (!$this->dataSpecification) {
      $this->dataSpecification = new DataSpecification();
      foreach ($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
        $this->dataSpecification->merge($sourceDataFlowDescription->getDataFlow()
          ->getDataSpecification(), $sourceDataFlowDescription->getDataFlow()
          ->getName());
      }
    }
    return $this->dataSpecification;
  }

  public function getName() {
    return $this->name;
  }

  public function getWhereClauses() {
    foreach($this->whereClauses as $clause) {
      $clauses[] = $clause;
    }
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      if ($sourceDataFlowDescription->getDataFlow() instanceof SqlDataFlow) {
        foreach($sourceDataFlowDescription->getDataFlow()->getWhereClauses() as $clause) {
          $clauses[] = $clause;
        }
      }
    }
    return $clauses;
  }

  /**
   * @return null|String
   */
  public function getPrimaryTable() {
    return $this->primary_table;
  }

  /**
   * @return null|String
   */
  public function getPrimaryTableAlias() {
    return $this->primary_table_alias;
  }


}