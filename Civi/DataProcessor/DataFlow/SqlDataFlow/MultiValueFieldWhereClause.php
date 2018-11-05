<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

/**
 * Use this clause to create a where on a civicrm multi value column, where values are seperated by
 * the \CRM_Core_DAO::VALUE_SEPARATOR character.
 *
 * Class SeperatorFieldWhereClause
 *
 * @package Civi\DataProcessor\DataFlow\SqlDataFlow
 */
class MultiValueFieldWhereClause implements WhereClauseInterface {

  protected $table_alias;

  protected $field;

  protected $operator;

  protected $value;

  protected $valueType;

  public function __construct($table_alias, $field, $operator, $value, $valueType = 'String') {
    $this->table_alias = $table_alias;
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
    $this->valueType = $valueType;
  }

  /**
   * Returns the where clause
   * E.g. contact_type = 'Individual'
   *
   * @return string
   */
  public function getWhereClause() {
    $clauses = array();
    if (!is_array($this->value)) {
      $this->value = array($this->value);
    }
    $combine = "OR";
    foreach($this->value as $value) {
      $escapedValue  = \CRM_Utils_Type::escape($value, $this->valueType);
      $compareValue = "%". \CRM_Core_DAO::VALUE_SEPARATOR.$escapedValue.\CRM_Core_DAO::VALUE_SEPARATOR."%";
      switch ($this->operator) {
        case 'LIKE':
        case 'IN':
        case '=':
          $clauses[] = "(`{$this->table_alias}`.`{$this->field}` LIKE  '{$compareValue}')";
          break;
        case 'NOT LIKE':
        case 'NOT IN':
        case '!=':
          $combine = "AND";
          $clauses[] = "(`{$this->table_alias}`.`{$this->field}` NOT LIKE ' {$compareValue}')";
          break;
      }
    }
    if (count($clauses)) {
      return "(" . implode(" {$combine} ", $clauses) . ")";
    }
    return "";
    return "`{$this->table_alias}`.`{$this->field}` {$this->operator} {$this->value}";
  }

}