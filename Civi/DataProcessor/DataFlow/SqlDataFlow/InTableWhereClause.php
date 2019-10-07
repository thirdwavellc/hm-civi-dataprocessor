<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

class InTableWhereClause implements WhereClauseInterface {

  protected $source_table_alias;

  protected $source_field;

  protected $select_field;

  protected $table;

  protected $table_alias;

  protected $filters;

  protected $operator;

  protected $isJoinClause = FALSE;

  public function __construct($select_field, $table, $table_alias, $filters, $source_table_alias, $source_field, $operator='IN', $isJoinClause=FALSE) {
    $this->isJoinClause = $isJoinClause;
    $this->source_field = $source_field;
    $this->select_field = $select_field;
    $this->table = $table;
    $this->table_alias = $table_alias;
    $this->filters = $filters;
    $this->operator = $operator;
    $this->source_table_alias = $source_table_alias;
  }

  /**
   * Returns true when this where clause can be added to the
   * join or whether this clause should be propagated to the where part of the query
   *
   * @return bool
   */
  public function isJoinClause() {
    return $this->isJoinClause;
  }

  /**
   * Returns the where clause
   * E.g. contact_type = 'Individual'
   *
   * @return string
   */
  public function getWhereClause() {
    $clauses = array("1");
    foreach($this->filters as $clause) {
      if ($clause->getWhereClause()) {
        $clauses[] = $clause->getWhereClause();
      }
    }
    $whereClause = implode(" AND ", $clauses);

    return "`{$this->source_table_alias}`.`{$this->source_field}` {$this->operator} (
              SELECT `{$this->table_alias}`.`{$this->select_field}`
              FROM `{$this->table}` `{$this->table_alias}`
              WHERE {$whereClause}
      )";
  }

}
