<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

use Civi\DataProcessor\DataSpecification\DataSpecification;

class SqlTableDataFlow extends SqlDataFlow {

  /**
   * @var string
   *   The name of the database table
   */
  protected $table;

  /**
   * @var string
   *   The alias of the database table
   */
  protected $table_alias;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $dataSpecification;


  public function __construct($table, $table_alias, DataSpecification $dataSpecification) {
    $this->table = $table;
    $this->table_alias = $table_alias;
    $this->dataSpecification = $dataSpecification;
  }

  public function getName() {
    return $this->table_alias;
  }

  /**
   * @return DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getDataSpecification() {
    $dataSpecification = $this->manipulateDataSpecification($this->dataSpecification);
    return $dataSpecification;
  }

  /**
   * Returns the From Statement.
   *
   * @return string
   */
  public function getFromStatement() {
    return "FROM `{$this->table}` `{$this->table_alias}`";
  }

  /**
   * Returns an array with the fields for in the select statement in the sql query.
   *
   * @return string[]
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getFieldsForSelectStatement() {
    $fields = array();
    foreach($this->dataSpecification->getFields() as $field) {
      $fields[] = "`{$this->table_alias}`.`{$field->name}` AS `{$field->alias}`";
    }
    return $fields;
  }

  /**
   * @return string
   */
  public function getTable() {
    return $this->table;
  }

  /**
   * @return string
   */
  public function getTableAlias() {
    return $this->table_alias;
  }

}