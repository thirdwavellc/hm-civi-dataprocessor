<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\Source\SourceInterface;

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
    $fieldSelect = array();
    foreach($dataProcessor->getDataSources() as $dataSource) {
      $fieldSelect = array_merge($fieldSelect, self::getAvailableFieldsInDataSource($dataSource, $dataSource->getSourceTitle().' :: ', $dataSource->getSourceName().'::'));
    }
    return $fieldSelect;
  }

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @oaram SourceInterface $dataSource
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFieldsInDataSource(SourceInterface $dataSource, $titlePrefix='', $namePrefix='') {
    $fieldSelect = array();
    foreach($dataSource->getAvailableFilterFields()->getFields() as $field) {
      $fieldSelect[$namePrefix.$field->name] = $titlePrefix.$field->title;
    }
    return $fieldSelect;
  }

}