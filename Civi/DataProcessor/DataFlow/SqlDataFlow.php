<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

use Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface;
use \Civi\DataProcessor\DataSpecification\DataSpecification;

abstract class SqlDataFlow extends AbstractDataFlow {

  /**
   * @var null|\CRM_Core_DAO
   */
  protected $dao = null;

  /**
   * @var null|int
   */
  protected $count = null;

  protected $whereClauses = array();

  protected $sqlStatement;

  protected $sqlCountStatement;

  /**
   * Returns an array with the fields for in the select statement in the sql query.
   *
   * @return string[]
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  abstract public function getFieldsForSelectStatement();

  /**
   * Returns an array with the fields for in the group by statement in the sql query.
   *
   * @return string[]
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  abstract public function getFieldsForGroupByStatement();

  /**
   * Returns the From Statement.
   *
   * @return string
   */
  abstract public function getFromStatement();

  /**
   * Initialize the data flow
   *
   * @return void
   */
  public function initialize() {
    if ($this->isInitialized()) {
      return;
    }

    try {
      $from = $this->getFromStatement();
      $where = $this->getWhereStatement();
      $groupBy = $this->getGroupByStatement();

      $countSql = "SELECT COUNT(*) {$from} {$where} {$groupBy}";
      $this->sqlCountStatement = $countSql;
      $this->count = \CRM_Core_DAO::singleValueQuery($countSql);

      $sql = "{$this->getSelectQueryStatement()} {$where} {$groupBy}";

      // Build Limit and Offset.
      $limitStatement = "";
      if ($this->offset !== FALSE && $this->limit !== FALSE) {
        $limitStatement = "LIMIT {$this->offset}, {$this->limit}";
      }
      elseif ($this->offset === FALSE && $this->limit !== FALSE) {
        $limitStatement = "LIMIT 0, {$this->limit}";
      }
      elseif ($this->offset !== FALSE && $this->limit === FALSE) {
        $calculatedLimit = $this->count - $this->offset;
        $limitStatement = "LIMIT {$this->offset}, {$calculatedLimit}";
      }
      $sql .= " {$limitStatement}";
      $this->sqlStatement = $sql;
      $this->dao = \CRM_Core_DAO::executeQuery($sql);
    } catch (\Exception $e) {
      throw new \Exception(
        "Error in query. 
        \r\nCount query: {$this->sqlCountStatement}
        \r\nQuery: $this->sqlStatement", 0, $e);
    }
  }

  /**
   * Returns whether this flow has been initialized or not
   *
   * @return bool
   */
  public function isInitialized() {
    if ($this->dao !== null) {
      return true;
    }
    return false;
  }

  /**
   * Resets the initialized state. This function is called
   * when a setting has changed. E.g. when offset or limit are set.
   *
   * @return void
   */
  protected function resetInitializeState() {
    $this->dao = null;
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
    foreach($this->dataSpecification->getFields() as $field) {
      $alias = $field->alias;
      $record[$fieldNamePrefix.$field->alias] = $this->dao->$alias;
    }
    return $record;
  }

  /**
   * @return int
   */
  public function recordCount() {
    if (!$this->isInitialized()) {
      $this->initialize();
    }
    return $this->count;
  }

  /**
   * @return string
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getSelectQueryStatement() {
    $select = implode(", ", $this->getFieldsForSelectStatement());
    $from = $this->getFromStatement();
    return "SELECT {$select} {$from}";
  }

  public function getGroupByStatement() {
    $groupByFields = $this->getFieldsForGroupByStatement();
    if (!count($groupByFields)) {
      return "";
    }
    return "GROUP BY ".implode(", ", $groupByFields);
  }

  /**
   * Returns the where statement for this query.
   *
   * @return string
   */
  public function getWhereStatement() {
    $clauses = array("1");
    foreach($this->getWhereClauses() as $clause) {
      $clauses[] = $clause->getWhereClause();
    }
    return "WHERE ". implode(" AND ", $clauses);
  }

  /**
   * @param \Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface $clause
   *
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  public function addWhereClause(WhereClauseInterface $clause) {
    foreach($this->whereClauses as $c) {
      if ($c->getWhereClause() == $clause->getWhereClause()) {
        return $this; // Where clause is already added do not add it again.
      }
    }
    $this->whereClauses[] = $clause;
    return $this;
  }

  /**
   * @param \Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface $clause
   *
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  public function removeWhereClause(WhereClauseInterface $clause) {
    foreach($this->whereClauses as  $i => $c) {
      if ($c->getWhereClause() == $clause->getWhereClause()) {
        unset($this->whereClauses[$i]);
      }
    }
    return $this;
  }

  /**
   * Return all the where clauses
   *
   * @return array
   */
  public function getWhereClauses() {
    return $this->whereClauses;
  }

    /**
     * Returns debug information
     *
     * @return string
     */
  public function getDebugInformation() {
    return $this->sqlStatement;
  }

}