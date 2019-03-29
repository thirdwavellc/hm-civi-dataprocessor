<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_DataprocessorSearch_Form_ActivitySearch extends CRM_DataprocessorSearch_Form_AbstractSearch {

  /**
   * Return the data processor name
   *
   * @return String
   */
  protected function getDataProcessorName() {
    $dataProcessorName = str_replace('civicrm/dataprocessor_activity_search/', '', CRM_Utils_System::getUrlPath());
    return $dataProcessorName;
  }

  /**
   * Returns the name of the output for this search
   *
   * @return string
   */
  protected function getOutputName() {
    return 'activity_search';
  }

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  protected function isConfigurationValid() {
    if (!isset($this->dataProcessorOutput['configuration']['activity_id_field'])) {
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
    return $this->dataProcessorOutput['configuration']['activity_id_field'];
  }

  /**
   * @return string
   */
  protected function getEntityTable() {
    return 'civicrm_activity';
  }

  /**
   * Builds the list of tasks or actions that a searcher can perform on a result set.
   *
   * @return array
   */
  public function buildTaskList() {
    if (!$this->_taskList) {
      $this->_taskList = CRM_Activity_Task::permissionedTaskTitles(CRM_Core_Permission::getPermission());
    }
    return $this->_taskList;
  }

}