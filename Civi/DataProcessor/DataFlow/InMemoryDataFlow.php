<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

class InMemoryDataFlow extends AbstractDataFlow {

  protected $data = [];

  protected $currentPointer = 0;

  protected $isInitialized = FALSE;

  public function __construct($data) {
    parent::__construct();
    $this->data = $data;
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
    $this->currentPointer = 0;
    $this->isInitialized = TRUE;
  }

  /**
   * Returns whether this flow has been initialized or not
   *
   * @return bool
   */
  public function isInitialized() {
    return $this->isInitialized;
  }

/**
   * Resets the initialized state. This function is called
   * when a setting has changed. E.g. when offset or limit are set.
   *
   * @return void
   */
  protected function resetInitializeState() {
    parent::resetInitializeState();
    $this->isInitialized = FALSE;
  }

  /**
   * Returns the next record in an associative array
   *
   * @param string $fieldNameprefix
   *   The prefix before the name of the field within the record.
   * @return array
   * @throws EndOfFlowException
   */
  public function retrieveNextRecord($fieldNameprefix='') {
    if (!isset($this->data[$this->currentPointer])) {
      throw new EndOfFlowException();
    }
    $data = $this->data[$this->currentPointer];
    $record = array();
    foreach($this->dataSpecification->getFields() as $field) {
      $alias = $field->alias;
      $name = $field->name;
      $record[$fieldNameprefix.$alias] = $data[$name];
    }
    $this->currentPointer++;
    return $record;
  }

  /**
   * Returns a name for this data flow.
   *
   * @return string
   */
  public function getName() {
    return 'in_memory';
  }

}
