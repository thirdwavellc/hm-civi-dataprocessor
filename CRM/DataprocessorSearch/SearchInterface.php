<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

interface CRM_DataprocessorSearch_SearchInterface {

  /**
   * Returns the url for the search
   *
   * @param \CRM_Dataprocessor_BAO_Output $output
   * @param $dataprocessor
   *
   * @return string
   */
  public function getSearchUrl($output, $dataprocessor);

  /**
   * @return string
   */
  public function getSearchControllerClass();

}