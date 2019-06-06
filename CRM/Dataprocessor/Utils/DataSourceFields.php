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
   * @param callable $callback
   *   Function to filter certain fields.
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFieldsInDataSources($dataProcessorId, $filterFieldsCallback=null) {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dataProcessorId));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    $fieldSelect = array();
    foreach($dataProcessorClass->getDataSources() as $dataSource) {
      $fieldSelect = array_merge($fieldSelect, self::getAvailableFieldsInDataSource($dataSource, $dataSource->getSourceTitle().' :: ', $dataSource->getSourceName().'::', $filterFieldsCallback));
    }
    return $fieldSelect;
  }

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @oaram SourceInterface $dataSource
   * @param $titlePrefix
   * @param $namePrefix
   * @param callable $callback
   *   Function to filter certain fields.
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFieldsInDataSource(SourceInterface $dataSource, $titlePrefix='', $namePrefix='', $filterFieldsCallback=null) {
    $fieldSelect = array();
    foreach($dataSource->getAvailableFields()->getFields() as $field) {
      $isFieldValid = true;
      if ($filterFieldsCallback) {
        $isFieldValid = call_user_func($filterFieldsCallback, $field);
      }
      if ($isFieldValid) {
        $fieldSelect[$namePrefix . $field->name] = $titlePrefix . $field->title;
      }
    }
    return $fieldSelect;
  }

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @oaram int $dataProcessorId
   * @param callable $callback
   *   Function to filter certain fields.
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFilterFieldsInDataSources($dataProcessorId, $filterFieldsCallback=null) {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dataProcessorId));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    $fieldSelect = array();
    foreach($dataProcessorClass->getDataSources() as $dataSource) {
      $fieldSelect = array_merge($fieldSelect, self::getAvailableFieldsInDataSource($dataSource, $dataSource->getSourceTitle().' :: ', $dataSource->getSourceName().'::', $filterFieldsCallback));
    }
    return $fieldSelect;
  }

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @oaram SourceInterface $dataSource
   * @param $titlePrefix
   * @param $namePrefix
   * @param callable $callback
   *   Function to filter certain fields.
   * @return array
   * @throws \Exception
   */
  public static function getAvailableFilterFieldsInDataSource(SourceInterface $dataSource, $titlePrefix='', $namePrefix='', $filterFieldsCallback=null) {
    $fieldSelect = array();
    foreach($dataSource->getAvailableFilterFields()->getFields() as $field) {
      $isFieldValid = true;
      if ($filterFieldsCallback) {
        $isFieldValid = call_user_func($filterFieldsCallback, $field);
      }
      if ($isFieldValid) {
        $fieldSelect[$namePrefix . $field->name] = $titlePrefix . $field->title;
      }
    }
    return $fieldSelect;
  }

}