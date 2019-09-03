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
   * @var String
   */
  private $dataProcessorName;

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
    $this->dataProcessorName = CRM_Utils_Request::retrieve('data_processor', 'String');

    $this->dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('name' => $this->dataProcessorName));
    $this->dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($this->dataProcessor);
    $this->assign('dataProcessorName', $this->dataProcessorName);
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
