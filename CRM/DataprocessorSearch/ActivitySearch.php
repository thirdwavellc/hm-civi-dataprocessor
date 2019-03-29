<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\Output\OutputInterface;

class CRM_DataprocessorSearch_ActivitySearch implements OutputInterface, CRM_DataprocessorSearch_SearchInterface {

  /**
   * Return the url to a configuration page.
   * Or return false when no configuration page exists.
   *
   * @return string|false
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/output/activity_search';
  }

  /**
   * Returns the url for the search
   *
   * @param \CRM_Dataprocessor_BAO_Output $output
   * @param $dataProcessor
   *
   * @return string
   */
  public function getSearchUrl($output, $dataProcessor) {
    return "civicrm/dataprocessor_activity_search/{$dataProcessor['name']}";
  }

  /**
   * @return string
   */
  public function getSearchControllerClass() {
    return 'CRM_DataprocessorSearch_Controller_ActivitySearch';
  }

}