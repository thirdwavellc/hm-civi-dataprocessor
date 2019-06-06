<?php
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_BAO_DataProcessor extends CRM_Dataprocessor_DAO_DataProcessor {

  static $importingDataProcessors = array();

  public static function checkName($title, $id=null,$name=null) {
    if (!$name) {
      $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($title));
    }

    $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($name));
    $name_part = $name;

    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor` WHERE `name` = %1";
    $sqlParams[1] = array($name, 'String');
    if ($id) {
      $sql .= " AND `id` != %2";
      $sqlParams[2] = array($id, 'Integer');
    }

    $i = 1;
    while(CRM_Core_DAO::singleValueQuery($sql, $sqlParams) > 0) {
      $i++;
      $name = $name_part .'_'.$i;
      $sqlParams[1] = array($name, 'String');
    }
    return $name;
  }

  /**
   * Returns whether the name is valid or not
   *
   * @param string $name
   * @param int $id optional
   * @return bool
   * @static
   */
  public static function isNameValid($name, $id=null) {
    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor` WHERE `name` = %1";
    $params[1] = array($name, 'String');
    if ($id) {
      $sql .= " AND `id` != %2";
      $params[2] = array($id, 'Integer');
    }
    $count = CRM_Core_DAO::singleValueQuery($sql, $params);
    return ($count > 0) ? false : true;
  }

  /**
   * Returns a configured data processor instance.
   *
   * @param array $dataProcessor
   * @return \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   * @throws \Exception
   */
  public static function dataProcessorToClass($dataProcessor) {
    $factory = dataprocessor_get_factory();
    $dataProcessorClass = $factory->getDataProcessorTypeByName($dataProcessor['type']);
    $sources = civicrm_api3('DataProcessorSource', 'get', array('data_processor_id' => $dataProcessor['id'], 'options' => array('limit' => 0)));
    foreach($sources['values'] as $sourceDao) {
      CRM_Dataprocessor_BAO_DataProcessorSource::addSourceToDataProcessor($sourceDao, $dataProcessorClass);
    }

    $aggregationFields = array();
    foreach($dataProcessorClass->getDataSources() as $source) {
      $aggregationFields = array_merge($aggregationFields, $source->getAvailableAggregationFields());
    }
    foreach($dataProcessor['aggregation'] as $alias) {
      $dataSource = $dataProcessorClass->getDataSourceByName($aggregationFields[$alias]->dataSource->getSourceName());
      if ($dataSource) {
        $dataSource->ensureAggregationFieldInSource($aggregationFields[$alias]->fieldSpecification);
      }
    }

    $filters = civicrm_api3('DataProcessorFilter', 'get', array('data_processor_id' => $dataProcessor['id'], 'options' => array('limit' => 0)));
    foreach($filters['values'] as $filter) {
      $filterHandler = $factory->getFilterByName($filter['type']);
      if ($filterHandler) {
        $filterHandler->setDataProcessor($dataProcessorClass);
        $filterHandler->initialize($filter['name'], $filter['title'], $filter['is_required'], $filter['configuration']);
        $dataProcessorClass->addFilterHandler($filterHandler);
      }
    }

    $fields = civicrm_api3('DataProcessorField', 'get', array('data_processor_id' => $dataProcessor['id'], 'options' => array('limit' => 0)));
    foreach($fields['values'] as $field) {
      $outputHandler = $factory->getOutputHandlerByName($field['type']);
      if ($outputHandler) {
        $outputHandler->setDataProcessor($dataProcessorClass);
        $outputHandler->initialize($field['name'], $field['title'], $field['configuration']);
        $dataProcessorClass->addOutputFieldHandlers($outputHandler);
      }
    }
    return $dataProcessorClass;
  }

  /**
   * Revert a data processor to the state in code.
   */
  public static function revert($data_processor_id) {
    $dao = \CRM_Core_DAO::executeQuery("SELECT status, source_file FROM civicrm_data_processor WHERE id = %1", array(1=>array($data_processor_id, 'Integer')));
    if (!$dao->fetch()) {
      return false;
    }
    if ($dao->status != CRM_Dataprocessor_Status::STATUS_OVERRIDDEN) {
      return false;
    }
    $key = substr($dao->source_file, 0, stripos($dao->source_file, "/"));
    $extension = civicrm_api3('Extension', 'getsingle', array('key' => $key));
    $filename = $extension['path'].substr($dao->source_file, stripos($dao->source_file, "/"));
    $data = file_get_contents($filename);
    $data = json_decode($data, true);

    CRM_Dataprocessor_Utils_Importer::importDataProcessor($data, $dao->source_file, $data_processor_id, CRM_Dataprocessor_Status::STATUS_IN_CODE);
    return true;
  }

  /**
   * Update the status from in code to overriden when a data processor has been changed
   *
   * @param $dataProcessorId
   */
  public static function updateAndChekStatus($dataProcessorId) {
    $sql = "SELECT `status`, `name` FROM `civicrm_data_processor` WHERE `id` = %1";
    $params[1] = array($dataProcessorId, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      if (!in_array($dao->name, self::$importingDataProcessors) && $dao->status == CRM_Dataprocessor_Status::STATUS_IN_CODE) {
        $sql = "UPDATE `civicrm_data_processor` SET `status` = %2 WHERE `id` = %1";
        $params[1] = array($dataProcessorId, 'String');
        $params[2] = array(CRM_Dataprocessor_Status::STATUS_OVERRIDDEN, 'Integer');
        CRM_Core_DAO::executeQuery($sql, $params);
      }
    }
  }

  /**
   * Store the data processor name so we know that we are importing this data processor
   * and should not update its status on the way.
   *
   * @param $dataProcessorName
   */
  public static function setDataProcessorToImportingState($dataProcessorName) {
    self::$importingDataProcessors[] = $dataProcessorName;
  }



}
