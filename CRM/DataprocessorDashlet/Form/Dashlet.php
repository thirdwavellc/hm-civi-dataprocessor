<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorDashlet_Form_Dashlet extends CRM_DataprocessorSearch_Form_AbstractSearch {

  public function buildQuickform() {
    parent::buildQuickform();
    $this->add('hidden', 'data_processor');
    $this->setDefaults(array('data_processor' => $this->getDataProcessorName()));
  }

  /**
   * Returns the default row limit.
   *
   * @return int
   */
  protected function getDefaultLimit() {
    $defaultLimit = 10;
    if (!empty($this->dataProcessorOutput['configuration']['default_limit'])) {
      $defaultLimit = $this->dataProcessorOutput['configuration']['default_limit'];
    }
    return $defaultLimit;
  }


  /**
   * Returns the name of the ID field in the dataset.
   *
   * @return string
   */
  protected function getIdFieldName() {
    return false;
  }

  /**
   * @return false|string
   */
  protected function getEntityTable() {
    return false;
  }

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
   * Return the data processor ID
   *
   * @return String
   */
  protected function getDataProcessorName() {
    $dataProcessorName = CRM_Utils_Request::retrieve('data_processor', 'String');
    if (empty($dataProcessorName)) {
      $dataProcessorName = $this->controller->exportValue($this->_name, 'data_processor');
    }
    return $dataProcessorName;
  }

  /**
   * Returns the name of the output for this search
   *
   * @return string
   */
  protected function getOutputName() {
    return 'dashlet';
  }

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  protected function isConfigurationValid() {
    return TRUE;
  }

  /**
   * Add buttons for other outputs of this data processor
   */
  protected function addExportOutputs() {
    // Don't add exports
  }

  /**
   * Returns the size of the crireria form element.
   * There are two sizes full and compact.
   *
   * @return string
   */
  protected function getCriteriaElementSize() {
    return 'compact';
  }


}
