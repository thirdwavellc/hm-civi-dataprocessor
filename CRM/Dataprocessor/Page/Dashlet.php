<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Main page for Data Processor Output dashlet
 *
 */
class CRM_Dataprocessor_Page_Dashlet extends CRM_Core_Page {
  
  /**
   * @var int
   */
  private $outputId;

  /**
   * @var int
   */
  private $dataProcessorId;

  /**
   * @var array
   */
  private $dataProcessor;

  /**
   * @var Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  private $dataProcessorClass;

  /**
   * Pre Process the results
   *
   * @return void
   */

  protected function preProcess() {
    $this->outputId = CRM_Utils_Request::retrieve('outputId', 'Integer');
    $this->dataProcessorId = CRM_Utils_Request::retrieve('dataProcessorId', 'Integer');

    $this->dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $this->dataProcessorId));
    $this->dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($this->dataProcessor);
    $this->assign('dataProcessorId', $this->dataProcessorId);
    $this->assign('outputId', $this->outputId);
  }

  /**
   * Dataprocessor Output as dashlet.
   *
   * @return void
   */

  public function run() {
    $this->preProcess();
    $this->addColumnHeaders();

    return parent::run();
  }

  /**
   * Add the headers for the columns
   *
   */
  protected function addColumnHeaders() {
    $columnHeaders = array();
    foreach($this->dataProcessorClass->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $columnHeaders[$field->alias] = $field->title;
    }
    $this->assign('columnHeaders', $columnHeaders);
  }
}
