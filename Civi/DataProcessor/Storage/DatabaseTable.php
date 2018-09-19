<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Storage;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\Test\Data;

class DatabaseTable implements StorageInterface {

  const MAX_INSERT_SIZE = 1000; // Insert 1000 records per time.

  /**
   * @var \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  protected $sourceDataFlow;

  /**
   * @var \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  protected $dataFlow = null;

  /**
   * @var string
   */
  protected $table;

  public function __construct($table) {
    $this->table = $table;
  }

  /**
   * Sets the source data flow
   *
   * @param \Civi\DataProcessor\DataFlow\AbstractDataFlow $sourceDataFlow
   * @return \Civi\DataProcessor\Storage\StorageInterface
   */
  public function setSourceDataFlow(AbstractDataFlow $sourceDataFlow) {
    $this->sourceDataFlow = $sourceDataFlow;
    return $this;
  }

  /**
   * Initialize the storage
   *
   * @return void
   */
  public function initialize() {
    if ($this->isInitialized()) {
      return;
    }

    if ($this->timeToRefreshStorage()) {
      $this->refreshStorage();
    }

    $this->dataFlow = new SqlTableDataFlow($this->table, $this->table,$this->sourceDataFlow->getDataSpecification());
  }

  /**
   * Chekcs whether the storage is initialized.
   *
   * @return bool
   */
  public function isInitialized() {
    if ($this->dataFlow !== null) {
      return true;
    }
    return false;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function getDataFlow() {
    if (!$this->isInitialized()) {
      $this->initialize();
    }
    return $this->dataFlow;
  }

  /**
   * Checks whether the storage needs to be refreshed.
   *
   * @return bool
   */
  protected function timeToRefreshStorage() {
    $tableExists = \CRM_Core_DAO::checkTableExists($this->table);
    if (!$tableExists) {
      return true;
    }
    return false;
  }

  protected function refreshStorage() {
    $tableExists = \CRM_Core_DAO::checkTableExists($this->table);
    if ($tableExists) {
      \CRM_Core_DAO::executeQuery("DROP TABLE `{$this->table}`");
    }
    if ($this->sourceDataFlow instanceof SqlDataFlow) {
      // Do a CREATE TABLE SELECT ... FROM ...
      $selectStatement = $this->sourceDataFlow->getSelectQueryStatement();
      $createTableStatement = "CREATE TABLE `{$this->table}` {$selectStatement}";
      \CRM_Core_DAO::executeQuery($createTableStatement);
    } else {
      $dataSpecification = $this->sourceDataFlow->getDataSpecification();
      $this->createTable($dataSpecification);
      $this->insertRecords($this->sourceDataFlow->allRecords(), $dataSpecification);
    }
  }

  /**
   * Create a database table based on the given data specification
   *
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification   *
   * @throws \Exception
   */
  private function createTable(DataSpecification $dataSpecification) {
    $addColumnSqls = array();
    foreach($dataSpecification->getFields() as $field) {
      $type = \Civi\DataProcessor\Utils\Sql::convertTypeToSqlColumnType($field->type);
      $addColumnSqls[] = "`{$field->name}` {$type}";
      $fieldSqls[] = "`{$field->name}`";
    }
    $addColumnSql = implode(", ", $addColumnSqls);
    $createTableStatement = "CREATE TABLE `{$this->table}` ({$addColumnSql}) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci";
    \CRM_Core_DAO::executeQuery($createTableStatement);
  }

  /**
   * Insert the records into the table
   *
   * @param $allRecords
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification
   */
  private function insertRecords($allRecords, DataSpecification $dataSpecification) {
    // Collect the fields
    $fieldSqls = array();
    foreach($dataSpecification->getFields() as $field) {
      $fieldSqls[] = "`{$field->name}`";
    }
    $fieldSql = implode(",", $fieldSqls);
    $baseInsertStatement = "INSERT INTO `{$this->table}` ({$fieldSql})";

    $values = array();
    foreach($allRecords as $record) {
      $row = array();
      foreach($record as $k => $v) {
        $field = $dataSpecification->getFieldSpecificationByName($k);
        $row[] = "'". \CRM_Utils_Type::escape($v, $field->type)."'";
      }
      $values[] = "(". implode(", ", $row). ")";

      // Insert the rows when we exceed the MAX_INSERT_SIZE
      if (count($values) >= self::MAX_INSERT_SIZE) {
        $strValues = implode(", ", $values);
        $insertStatement = "{$baseInsertStatement} VALUES {$strValues}";
        \CRM_Core_DAO::executeQuery($insertStatement);
        $values = array();
      }
    }

    // Insert the remaining rows.
    if (count($values)) {
      $strValues = implode(", ", $values);
      $insertStatement = "{$baseInsertStatement} VALUES {$strValues}";
      \CRM_Core_DAO::executeQuery($insertStatement);
    }
  }
}