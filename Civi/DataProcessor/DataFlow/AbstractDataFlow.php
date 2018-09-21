<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

use \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use \Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler;


abstract class AbstractDataFlow {

  /**
   * @var null|array
   */
  private $_allRecords = null;

  /**
   * @var \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler[]
   */
  protected $outputFieldHandlers;

  /**
   * @var false|int
   */
  protected $offset = false;

  /**
   * @var false|int
   */
  protected $limit = false;

  /**
   * @var \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription
   */
  protected $dataFlowDescription;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $dataSpecification;

  /**
   * @var FieldSpecification[]
   */
  protected $aggregateFields = array();

  /**
   * Initialize the data flow
   *
   * @return void
   */
  abstract public function initialize();

  /**
   * Returns whether this flow has been initialized or not
   *
   * @return bool
   */
  abstract public function isInitialized();

  /**
   * Resets the initialized state. This function is called
   * when a setting has changed. E.g. when offset or limit are set.
   *
   * @return void
   */
  abstract protected function resetInitializeState();

  /**
   * Returns the next record in an associative array
   *
   * @param string $fieldNameprefix
   *   The prefix before the name of the field within the record.
   * @return array
   * @throws EndOfFlowException
   */
  abstract protected function retrieveNextRecord($fieldNameprefix='');

  /**
   * Returns a name for this data flow.
   *
   * @return string
   */
  abstract public function getName();

  public function __construct() {
  }

  /**
   * Returns the next record or throws EndOfFlowException when the end
   * of the dataflow is reached.
   *
   * @param string $fieldNamePrefix
   *   The prefix before the name of the field within the record.
   * @return array
   * @throws \Civi\DataProcessor\DataFlow\EndOfFlowException
   */
  public function nextRecord($fieldNamePrefix = '') {
    while ($record = $this->retrieveNextRecord($fieldNamePrefix)) {
      return $this->formatRecordOutput($record);
    }
  }

  /**
   * @return int
   */
  public function recordCount() {
    $allRecords = $this->allRecords();
    return count($allRecords);
  }

  /**
   * @return DataSpecification
   */
  public function getDataSpecification() {
    if (!$this->dataSpecification) {
      $this->dataSpecification = new DataSpecification();
    }
    return $this->dataSpecification;
  }

  /**
   * Returns an array of all records in the data flow.
   *
   * @param string $fieldNameprefix
   *   The prefix before the name of the field within the record
   * @return array
   */
  public function allRecords($fieldNameprefix = '') {
    if (!is_array($this->_allRecords)) {
      $this->_allRecords = [];
      try {
        while ($record = $this->retrieveNextRecord($fieldNameprefix)) {
          $this->_allRecords[] = $this->formatRecordOutput($record);
        }
      } catch (EndOfFlowException $e) {
        // Do nothing
      }
    }

    return $this->_allRecords;
  }

  public function formatRecordOutput($record) {
    $formattedRecord = array();
    foreach($this->outputFieldHandlers as $outputFieldHandler) {
      $formattedRecord[$outputFieldHandler->getOutputFieldSpecification()->alias] = $outputFieldHandler->formatField($record, $formattedRecord);
    }
    return $formattedRecord;
  }

  /**
   * @param false|int $offset
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function setOffset($offset) {
    $this->offset = $offset;
    $this->resetInitializeState();
    return $this;
  }

  /**
   * @param false|int $limit
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function setLimit($limit) {
    $this->limit = $limit;
    $this->resetInitializeState();
    return $this;
  }

  /**
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription $dataFlowDescription
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function setDataFlowDescription(DataFlowDescription $dataFlowDescription) {
    $this->dataFlowDescription = $dataFlowDescription;
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription
   */
  public function getDataFlowDescription() {
    return $this->dataFlowDescription;
  }

  /**
   * @param \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler $outputFieldHandler
   */
  public function addOutputFieldHandlers(AbstractFieldOutputHandler $outputFieldHandler) {
    $this->outputFieldHandlers[] = $outputFieldHandler;
  }

  /**
   * @param \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler $outputFieldHandler[]
   */
  public function setOutputFieldHandlers($handlers) {
    $this->outputFieldHandlers = $handlers;
  }

  /**
   * @return \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler[]
   */
  public function getOutputFieldHandlers() {
    return $this->outputFieldHandlers;
  }


  /**
   * Returns debug information
   *
   * @return string
   */
  public function getDebugInformation() {
    return "";
  }

  public function addAggregateField(FieldSpecification $aggregateField) {
    $this->aggregateFields[] = $aggregateField;
  }


}