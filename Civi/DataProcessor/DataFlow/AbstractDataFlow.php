<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

use \Civi\DataProcessor\DataSpecification\DataSpecification;
use \Civi\DataProcessor\DataFlow\Manipulator\AbstractManipulator;
use \Civi\DataProcessor\DataFlow\Filter\AbstractFilter;

abstract class AbstractDataFlow {

  /**
   * @var null|array
   */
  private $_allRecords = null;

  /**
   * @var AbstractManipulator[]
   */
  protected $manipulators = array();

  /**
   * @var AbstractFilter[]
   */
  protected $filters = array();

  /**
   * @var false|int
   */
  protected $offset = false;

  /**
   * @var false|int
   */
  protected $limit = false;

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
   * @return DataSpecification
   */
  abstract public function getDataSpecification();

  /**
   * Returns a name for this data flow.
   *
   * @return string
   */
  abstract public function getName();

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
      $record = $this->manipulateRecord($record, $fieldNamePrefix);
      if ($this->filterRecord($record)) {
        return $record;
      }
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
          $record = $this->manipulateRecord($record, $fieldNameprefix);
          if ($this->filterRecord($record)) {
            $this->_allRecords[] = $record;
          }
        }
      } catch (EndOfFlowException $e) {
        // Do nothing
      }
    }

    return $this->_allRecords;
  }

  /**
   * @param \Civi\DataProcessor\DataFlow\Manipulator\AbstractManipulator $manipulator
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function addManipulator(AbstractManipulator $manipulator) {
    $this->manipulators[] = $manipulator;
    return $this;
  }

  /**
   * @param \Civi\DataProcessor\DataFlow\Filter\AbstractFilter $filter
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function addFilter(AbstractFilter $filter) {
    $this->filters[] = $filter;
    return $this;
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
   * Manilpulates a record
   *
   * @param string $fieldNamePrefix
   *   The prefix before the name of the field within the record
   * @param array $record
   * @return array
   */
  protected function manipulateRecord($record, $fieldNamePrefix='') {
    foreach($this->manipulators as $manipulator) {
      $record = $manipulator->manipulate($record, $fieldNamePrefix);
    }
    return $record;
  }

  /**
   * Filters a record. Returns true when a record is valid, and false when the record does not
   * match the filter criteria.
   *
   * @param array $record
   * @return bool
   */
  protected function filterRecord($record) {
    foreach($this->filters as $filter) {
      if (!$filter->filter($record)) {
        return false;
      }
    }
    return true;
  }

  /**
   * Manipulates the dataspecification for this data flow.
   *
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function manipulateDataSpecification(DataSpecification $dataSpecification) {
    foreach($this->manipulators as $manipulator) {
      $dataSpecification = $manipulator->manipulateDataSpecification($dataSpecification);
    }
    return $dataSpecification;
  }


}