<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

class IsNullWhereClause implements WhereClauseInterface {

  protected $table_alias;

  protected $field;

  protected $operator;

  protected $value;

  protected $isJoinClause = FALSE;

  public function __construct($table_alias, $field, $isJoinClause=FALSE) {
    $this->isJoinClause = $isJoinClause;
    $this->table_alias = $table_alias;
    $this->field = $field;
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
    return "`{$this->table_alias}`.`{$this->field}` IS NULL";
  }

}