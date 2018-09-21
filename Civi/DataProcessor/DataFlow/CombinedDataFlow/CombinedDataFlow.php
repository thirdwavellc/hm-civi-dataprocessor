<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\CombinedDataFlow;

use \Civi\DataProcessor\DataFlow\AbstractDataFlow;
use \Civi\DataProcessor\DataFlow\EndOfFlowException;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\MultipleSourceDataFlows;
use \Civi\DataProcessor\DataSpecification\DataSpecification;


class CombinedDataFlow extends AbstractDataFlow implements MultipleSourceDataFlows {

  /**
   * @var DataFlowDescription[]
   */
  protected $sourceDataFlowDescriptions = array();

  /**
   * @var int
   */
  protected $currentRecordIndex = 0;

  /**
   * @var array
   */
  protected $recordSet = array();

  /**
   * @var int
   */
  protected $recordCount = 0;

  /**
   * @var bool
   */
  protected $initialized = false;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $dataSpecification;

  public function __construct() {
    $this->dataSpecification = new DataSpecification();
  }

  /**
   * Adds a source data flow
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription $dataFlowDescription
   * @return void
   */
  public function addSourceDataFlow(DataFlowDescription $dataFlowDescription) {
    $this->sourceDataFlowDescriptions[] = $dataFlowDescription;
  }

  /**
   * Initialize the data flow
   *
   * @return void
   */
  public function initialize() {
    if ($this->isInitialized()) {
      return;
    }

    $this->recordSet = array();
    $this->recordCount = 0;

    if (count($this->sourceDataFlowDescriptions) < 1) {
      $this->initialized = true;
      return;
    }

    $allRecords = array();
    foreach($this->sourceDataFlowDescriptions as $dataFlowDescription) {
      $records = $dataFlowDescription->getDataFlow()->allRecords($dataFlowDescription->getDataFlow()->getName());
      $allRecords = $this->joinArray($allRecords, $records, $dataFlowDescription->getJoinSpecification());
    }
    $this->recordCount = count($allRecords);

    if ($this->offset || $this->limit) {
      $offset = $this->offset !== FALSE ? $this->offset : 0;
      for ($i = $offset; $i < count($allRecords); $i++) {
        $this->recordSet[] = $allRecords[$i];
        if ($this->limit && count($this->recordSet) >= $this->limit) {
          break;
        }
      }
    } else {
      $this->recordSet = $allRecords;
    }

    $this->initialized = true;
  }

  /**
   * Join two arrays together based on the combine specification
   * This functions like an INNER JOIN in sql.
   *
   * @param $left
   * @param $right
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification|null
   *
   * @return array
   */
  protected function joinArray($left, $right, JoinSpecification $combineSpecification=null) {
    $out = array();

    if ($combineSpecification === null && empty($left)) {
      return $right;
    } elseif ($combineSpecification === null && empty($right)) {
      return $left;
    }

    foreach($left as $left_index => $left_record) {
      foreach($right as $right_index => $right_record) {
        if ($combineSpecification === null || $combineSpecification->isJoinable($left_record, $right_record)) {
          $out[] = array_merge($left_record, $right_record);
          unset($left[$left_index]);
          unset($right[$right_index]);
        }
      }
    }

    return $out;
  }

  /**
   * Returns whether this flow has been initialized or not
   *
   * @return bool
   */
  public function isInitialized() {
    return $this->initialized;
  }

  /**
   * Resets the initialized state. This function is called
   * when a setting has changed. E.g. when offset or limit are set.
   *
   * @return void
   */
  protected function resetInitializeState() {
    $this->initialized = false;
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

    if (!isset($this->recordSet[$this->currentRecordIndex])) {
      throw new EndOfFlowException();
    }
    $record = $this->recordSet[$this->currentRecordIndex];
    $out = array();
    if (strlen($fieldNamePrefix)) {
      foreach ($record as $field => $value) {
        $out[$fieldNamePrefix . $field] = $value;
      }
    }
    $this->currentRecordIndex++;
    return $record;
  }

  /**
   * @return int
   */
  public function recordCount() {
    if (!$this->isInitialized()) {
      $this->initialize();
    }
    return $this->recordCount;
  }


  /**
   * @return DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getDataSpecification() {
    $dataSpecification = new DataSpecification();
    foreach($this->dataFlows as $dataFlow) {
      $dataSpecification->merge($dataFlow['dataflow']->getDataSpecification(), $dataFlow->getName());
    }
    return $dataSpecification;
  }

  public function getName() {
    return 'combined_data_flow';
  }

}