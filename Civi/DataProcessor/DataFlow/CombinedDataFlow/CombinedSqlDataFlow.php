<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\CombinedDataFlow;

use \Civi\DataProcessor\DataFlow\EndOfFlowException;
use Civi\DataProcessor\DataFlow\InvalidFlowException;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\MultipleSourceDataFlows;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use \Civi\DataProcessor\DataSpecification\DataSpecification;


class CombinedSqlDataFlow extends SqlDataFlow implements MultipleSourceDataFlows {

  /**
   * @var \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription[]
   */
  protected $sourceDataFlowDescriptions = array();

  public function __construct() {

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
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      if (count($fromStatements)) {
        $fromStatements[] = $sourceDataFlowDescription->getJoinSpecification()->getJoinClause($sourceDataFlowDescription);
      } else {
        $fromStatements[] = "FROM `{$sourceDataFlowDescription->getDataFlow()->getTable()}` `{$sourceDataFlowDescription->getDataFlow()->getTableAlias()}`";
      }
    }
    return implode(" ", $fromStatements);
  }

  /**
   * Returns an array with the fields for in the select statement in the sql query.
   *
   * @return string[]
   */
  public function getFieldsForSelectStatement() {
    $fields = array();
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      $fields = array_merge($sourceDataFlowDescription->getDataFlow()->getFieldsForSelectStatement(), $fields);
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
        $fieldName = $fieldNamePrefix.$sourceDataFlowDescription->getDataFlow()->getName().$field->name;
        $record[$fieldName] = $this->dao->$alias;
      }
    }
    return $record;
  }

  /**
   * @return DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getDataSpecification() {
    $dataSpecification = new DataSpecification();
    foreach($this->sourceDataFlowDescriptions as $sourceDataFlowDescription) {
      $dataSpecification->merge($sourceDataFlowDescription->getDataFlow()->getDataSpecification(), $sourceDataFlowDescription->getDataFlow()->getName());
    }
    $dataSpecification = $this->manipulateDataSpecification($dataSpecification);
    return $dataSpecification;
  }

  public function getName() {
    return 'combined_sql_data_flow';
  }

}