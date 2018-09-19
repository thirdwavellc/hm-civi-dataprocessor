<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

class SimpleWhereClause implements WhereClauseInterface {

  protected $table_alias;

  protected $field;

  protected $operator;

  protected $value;

  public function __construct($table_alias, $field, $operator, $value, $valueType = 'String') {
    $this->table_alias = $table_alias;
    $this->field = $field;
    $this->operator = $operator;
    if (is_array($value)) {
      $this->value = "(".implode(", ",\CRM_Utils_Type::escapeAll($value, $valueType)).")";
    } else {
      $this->value = \CRM_Utils_Type::escape($value, $valueType);
    }
  }

  /**
   * Returns the where clause
   * E.g. contact_type = 'Individual'
   *
   * @return string
   */
  public function getWhereClause() {
    return "`{$this->table_alias}`.`{$this->field}` {$this->operator} {$this->value}";
  }

}