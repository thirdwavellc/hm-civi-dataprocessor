<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_Utils_DataSourceFields {

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @oaram int $dataProcessorId
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFieldsInDataSources($dataProcessorId) {
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($dataProcessorId);
    foreach($dataProcessor->getDataSources() as $dataSource) {
      foreach($dataSource->getAvailableFilterFields()->getFields() as $field) {
        $fieldSelect[$dataSource->getSourceName().'::'.$field->name] = $dataSource->getSourceTitle().' :: '.$field->title;
      }
    }
    return $fieldSelect;
  }

}