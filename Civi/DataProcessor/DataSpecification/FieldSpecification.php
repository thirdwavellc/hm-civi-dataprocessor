<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class FieldSpecification implements SqlFieldSpecification {

  /**
   * @var String
   */
  public $name;

  /**
   * @var String
   */
  public $type;

  /**
   * @var String
   */
  public $title;

  /**
   * @var String
   */
  public $alias;

  /**
   * @var null|array
   */
  public $options = null;

  /**
   * @var null|String
   */
  protected $sqlValueFormatFunction = null;

  public function __construct($name, $type, $title, $options=null, $alias=null) {
    if (empty($alias)) {
      $this->alias = $name;
    } else {
      $this->alias = $alias;
    }
    $this->name = $name;
    $this->type = $type;
    $this->title = $title;
    $this->options = $options;
  }

  public function getOptions() {
    return $this->options;
  }

  /**
   * @param $function
   */
  public function setMySqlFunction($function) {
    $this->sqlValueFormatFunction = $function;
  }

  /**
   * Returns the select statement for this field.
   * E.g. COUNT(civicrm_contact.id) AS contact_id_count
   *
   * @param String $table_alias
   * @return string
   */
  public function getSqlSelectStatement($table_alias) {
    if ($this->sqlValueFormatFunction) {
      return "{$this->sqlValueFormatFunction} (`{$table_alias}`.`{$this->name}`) AS `{$this->alias}`";
    }
    return "`{$table_alias}`.`{$this->name}` AS `{$this->alias}`";
  }

  /**
   * Returns the SQL column name for this field.
   * This could be used in join statements
   *
   * @param $table_alias
   * @return string
   */
  public function getSqlColumnName($table_alias) {
    if ($this->sqlValueFormatFunction) {
      return "{$this->sqlValueFormatFunction} (`{$table_alias}`.`{$this->name}`)";
    }
    return "`{$table_alias}`.`{$this->name}`";
  }

  /**
   * Returns the group by statement for this field.
   * E.g. civicrm_contribution.financial_type_id
   * or MONTH(civicrm_contribution.receive_date)
   *
   * @param String $table_alias
   * @return String
   */
  public function getSqlGroupByStatement($table_alias) {
    if ($this->sqlValueFormatFunction) {
      return "{$this->sqlValueFormatFunction} (`{$table_alias}`.`{$this->name}`)";
    }
    return "`{$table_alias}`.`{$this->name}`";
  }

}
