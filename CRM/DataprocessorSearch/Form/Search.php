<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_DataprocessorSearch_Form_Search extends CRM_DataprocessorSearch_Form_AbstractSearch {


  /**
   * Returns the url for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  protected function link($row) {
    return false;
  }

  /**
   * Returns the link text for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  protected function linkText($row) {
    return false;
  }

  /**
   * Return the data processor name
   *
   * @return String
   */
  protected function getDataProcessorName() {
    $dataProcessorName = str_replace('civicrm/dataprocessor_search/', '', CRM_Utils_System::getUrlPath());
    return $dataProcessorName;
  }

  /**
   * Returns the name of the output for this search
   *
   * @return string
   */
  protected function getOutputName() {
    return 'search';
  }

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  protected function isConfigurationValid() {
    if (!isset($this->dataProcessorOutput['configuration']['id_field'])) {
      return false;
    }
    return true;
  }

  /**
   * Returns the name of the ID field in the dataset.
   *
   * @return string
   */
  protected function getIdFieldName() {
    return $this->dataProcessorOutput['configuration']['id_field'];
  }

  /**
   * @return false|string
   */
  protected function getEntityTable() {
    return false;
  }

  /**
   * Builds the list of tasks or actions that a searcher can perform on a result set.
   *
   * @return array
   */
  public function buildTaskList() {
    if (!$this->_taskList) {
      $this->_taskList = CRM_DataprocessorSearch_Task::taskTitles();
    }
    return $this->_taskList;
  }

}
