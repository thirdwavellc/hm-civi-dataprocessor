<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_BAO_DataProcessor extends CRM_Dataprocessor_DAO_DataProcessor {

  static $importingDataProcessors = array();

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $dataProcessor = new CRM_Dataprocessor_DAO_DataProcessor();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $dataProcessor->$key = $value;
        }
      }
    }
    $dataProcessor->find();
    while ($dataProcessor->fetch()) {
      $row = array();
      self::storeValues($dataProcessor, $row);
      if (!empty($row['aggregation'])) {
        $row['aggregation'] = json_decode($row['aggregation'], true);
      } else {
        $row['aggregation'] = array();
      }
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update a DataProcessor
   *
   * @param array $params
   * @return array $result
   * @access public
   * @throws Exception when params is empty
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a data processor');
    }

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', 'DataProcessor', $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', 'DataProcessor', NULL, $params);
    }

    $dataProcessor = new CRM_Dataprocessor_DAO_DataProcessor();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $dataProcessor->$key = $value;
      }
    }
    if (empty($dataProcessor->name)) {
      $dataProcessor->name = self::buildNameFromTitle($dataProcessor->title);
    }
    if (empty($dataProcessor->type)) {
      $dataProcessor->type = 'default';
    }
    if (isset($dataProcessor->configuration) && is_array($dataProcessor->configuration)) {
      $dataProcessor->configuration = json_encode($dataProcessor->configuration);
    }
    if (isset($dataProcessor->aggregation) && is_array($dataProcessor->aggregation)) {
      $dataProcessor->aggregation = json_encode($dataProcessor->aggregation);
    }
    if (isset($dataProcessor->storage_configuration) && is_array($dataProcessor->storage_configuration)) {
      $dataProcessor->storage_configuration = json_encode($dataProcessor->storage_configuration);
    }

    $dataProcessor->save();
    self::storeValues($dataProcessor, $result);
    CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessor->id);

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', 'DataProcessor', $dataProcessor->id, $dataProcessor);
    }
    else {
      CRM_Utils_Hook::post('create', 'DataProcessor', $dataProcessor->id, $dataProcessor);
    }

    return $result;
  }

  /**
   * Function to delete a FormProcessorInstance with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor');
    }

    CRM_Utils_Hook::pre('delete', 'DataProcessor', $id, CRM_Core_DAO::$_nullArray);

    $dataProcessor = new CRM_Dataprocessor_DAO_DataProcessor();
    $dataProcessor->id = $id;
    $dataProcessor->delete();

    CRM_Utils_Hook::post('delete', 'DataProcessor', $id, CRM_Core_DAO::$_nullArray);

    return;
  }

  /**
   * Function to disable a data processor
   *
   * @param int $id
   * @throws Exception when id is empty
   * @access public
   * @static
   */
  public static function disable($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to disable a data processor');
    }
    $dataProcessor = new CRM_Dataprocessor_BAO_DataProcessor();
    $dataProcessor->id = $id;
    $dataProcessor->find(true);
    self::add(array('id' => $dataProcessor->id, 'is_active' => 0));
  }

  /**
   * Function to enable a data processor
   *
   * @param int $id
   * @throws Exception when id is empty
   * @access public
   * @static
   */
  public static function enable($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to enable a data processor');
    }
    $dataProcessor = new CRM_Dataprocessor_BAO_DataProcessor();
    $dataProcessor->id = $id;
    $dataProcessor->find(true);
    self::add(array('id' => $dataProcessor->id, 'is_active' => 1));
  }

  /**
   * Public function to generate name from title
   *
   * @param $label
   * @return string
   * @access public
   * @static
   */
  public static function buildNameFromTitle($title) {
    return preg_replace('@[^a-z0-9_]+@','_', strtolower($title));
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
    $invalidNames = array('getactions', 'getfields', 'get', 'create', 'delete');
    if (in_array(strtolower($name), $invalidNames)) {
      return false;
    }
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
   * @param String $output_type
   * @param String $name
   * @return \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   * @throws \Exception when no data processor is found.
   */
  public static function getDataProcessorById($id) {
    $sql = "
      SELECT civicrm_data_processor.* 
      FROM civicrm_data_processor 
      WHERE id = %1
    ";
    $params[1] = [$id, 'Integer'];
    $dao = CRM_Dataprocessor_BAO_DataProcessor::executeQuery($sql, $params, TRUE, 'CRM_Dataprocessor_BAO_DataProcessor');
    if ($dao->N != 1) {
      throw new \Exception('Could not find Data Processor');
    }
    $dao->fetch();
    return $dao->getDataProcessor();
  }

  /**
   * Returns a configured data processor instance.
   *
   * @param String $output_type
   * @param String $name
   * @return \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   * @throws \Exception when no data processor is found.
   */
  public static function getDataProcessorByOutputTypeAndName($output_type, $name) {
    $sql = "
      SELECT civicrm_data_processor.* 
      FROM civicrm_data_processor 
      INNER JOIN civicrm_data_processor_output ON civicrm_data_processor.id = civicrm_data_processor_output.data_processor_id
      WHERE is_active = 1 AND civicrm_data_processor.name = %1 AND civicrm_data_processor_output.type = %2
    ";
    $params[1] = array($name, 'String');
    $params[2] = array($output_type, 'String');
    $dao = CRM_Dataprocessor_BAO_DataProcessor::executeQuery($sql, $params, TRUE, 'CRM_Dataprocessor_BAO_DataProcessor');
    if ($dao->N != 1) {
      throw new \Exception('Could not find Data Processor');
    }
    $dao->fetch();
    return $dao->getDataProcessor();
  }

  /**
   * Returns a configured data processor instance.
   *
   * @return \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  public function getDataProcessor() {
    $factory = dataprocessor_get_factory();
    $dataProcessor = $factory->getDataProcessorTypeByName($this->type);
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $this->id));
    foreach($sources as $sourceDao) {
      CRM_Dataprocessor_BAO_Source::getSourceClass($sourceDao, $dataProcessor);
    }

    $aggregationFields = CRM_Dataprocessor_BAO_DataProcessor::getAvailableAggregationFields($this->id);
    if (is_string($this->aggregation)) {
      $this->aggregation = json_decode($this->aggregation, true);
    }
    if (!is_array($this->aggregation)) {
      $this->aggregation = array();
    }
    foreach($this->aggregation as $alias) {
      $dataSource = $dataProcessor->getDataSourceByName($aggregationFields[$alias]->dataSource->getSourceName());
      if ($dataSource) {
        $dataSource->ensureAggregationFieldInSource($aggregationFields[$alias]->fieldSpecification);
      }
    }

    $filters = CRM_Dataprocessor_BAO_Filter::getValues(array('data_processor_id' => $this->id));
    foreach($filters as $filter) {
      $filterHandler = $factory->getFilterByName($filter['type']);
      if ($filterHandler) {
        $filterHandler->setDataProcessor($dataProcessor);
        $filterHandler->initialize($filter['name'], $filter['title'], $filter['is_required'], $filter['configuration']);
        $dataProcessor->addFilterHandler($filterHandler);
      }
    }

    $fields = CRM_Dataprocessor_BAO_Field::getValues(array('data_processor_id' => $this->id));
    $outputHandlers = $dataProcessor->getAvailableOutputHandlers();
    foreach($fields as $field) {
      if (isset($outputHandlers[$field['type']])) {
        $outputHandler = $outputHandlers[$field['type']];
        $outputHandler->initialize($field['name'], $field['title'], $field['configuration']);
        $dataProcessor->addOutputFieldHandlers($outputHandler);
      }
    }
    return $dataProcessor;
  }

  public static function getAvailableOutputHandlers($data_processor_id) {
    $dao = new CRM_Dataprocessor_BAO_DataProcessor();
    $dao->id = $data_processor_id;
    $dao->find(true);
    $factory = dataprocessor_get_factory();
    $dataProcessor = $factory->getDataProcessorTypeByName($dao->type);
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $dao->id));
    foreach($sources as $sourceDao) {
      CRM_Dataprocessor_BAO_Source::getSourceClass($sourceDao, $dataProcessor);
    }

    return $dataProcessor->getAvailableOutputHandlers();
  }

  public static function getAvailableFilterHandlers($data_processor_id) {
    $dao = new CRM_Dataprocessor_BAO_DataProcessor();
    $dao->id = $data_processor_id;
    $dao->find(true);
    $factory = dataprocessor_get_factory();
    $dataProcessor = $factory->getDataProcessorTypeByName($dao->type);
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $dao->id));
    foreach($sources as $sourceDao) {
      CRM_Dataprocessor_BAO_Source::getSourceClass($sourceDao, $dataProcessor);
    }

    return $dataProcessor->getAvailableFilterHandlers();
  }

  /**
   * Returns an array with all available fields for aggregation
   *
   * @param $data_processor_id
   *
   * @return array
   */
  public static function getAvailableAggregationFields($data_processor_id) {
    $availableAggregationFields = array();
    $dao = new CRM_Dataprocessor_BAO_DataProcessor();
    $dao->id = $data_processor_id;
    $dao->find(true);
    $factory = dataprocessor_get_factory();
    $dataProcessor = $factory->getDataProcessorTypeByName($dao->type);
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $dao->id));
    foreach($sources as $sourceDao) {
      $source = CRM_Dataprocessor_BAO_Source::getSourceClass($sourceDao, $dataProcessor);
      $availableAggregationFields = array_merge($availableAggregationFields, $source->getAvailableAggregationFields());
    }

    return $availableAggregationFields;
  }

  /**
   * Returns the id of the data processor.
   *
   * @param string $dataProcessorName
   *   The name of the data processor.
   * @return int
   *   The id of the data processor.
   */
  public static function getId($dataProcessorName) {
    $sql = "SELECT `id` FROM `civicrm_data_processor` WHERE `name` = %1";
    $params[1] = array($dataProcessorName, 'String');
    $id = CRM_Core_DAO::singleValueQuery($sql, $params);
    return $id;
  }

  /**
   * Returns the status of the data processor.
   * @See CRM_Dataprocessor_DAO_DataProcessor for possible values.
   *
   * @param string $dataProcessorName
   *   The name of the data processor.
   * @return int
   *   The status of the data processor.
   */
  public static function getStatus($dataProcessorName) {
    $sql = "SELECT `status` FROM `civicrm_data_processor` WHERE `name` = %1";
    $params[1] = array($dataProcessorName, 'String');
    $status = CRM_Core_DAO::singleValueQuery($sql, $params);
    return $status;
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
      if (!in_array($dao->name, self::$importingDataProcessors) && $dao->status == self::STATUS_IN_CODE) {
        $sql = "UPDATE `civicrm_data_processor` SET `status` = %2 WHERE `id` = %1";
        $params[1] = array($dataProcessorId, 'String');
        $params[2] = array(self::STATUS_OVERRIDDEN, 'Integer');
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

  /**
   * Updates the status and source file of the data processor.
   * @See CRM_Dataprocessor_DAO_DataProcessor for possible status values.
   *
   * @param string $dataProcessorName
   *   The name of the data processor.
   * @param int $status
   *   The status value.
   * @param string $source_file
   *   The source file. Leave empty when status is IN_DATABASE.
   */
  public static function setStatusAndSourceFile($dataProcessorName, $status, $source_file) {
    $sql = "UPDATE `civicrm_data_processor` SET `status` = %2, `source_file` = %3 WHERE `name` = %1";
    $params[1] = array($dataProcessorName, 'String');
    $params[2] = array($status, 'Integer');
    $params[3] = array($source_file, 'String');
    CRM_Core_DAO::executeQuery($sql, $params);
  }

  /**
   * Exports a data processor
   *
   * Returns the array with the whole configuration.
   *
   * @param $id
   * @return array
   */
  public static function export($id) {
    $dataProcessors = self::getValues(array('id' => $id));
    if (!isset($dataProcessors[$id])) {
      return array();
    }
    $dataProcessor = $dataProcessors[$id];
    unset($dataProcessor['id']);
    unset($dataProcessor['status']);
    unset($dataProcessor['source_file']);

    $dataSources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $id));
    $dataProcessor['data_sources'] = array();
    foreach($dataSources as $i => $datasource) {
      unset($datasource['id']);
      unset($datasource['data_processor_id']);
      $dataProcessor['data_sources'][] = $datasource;
    }
    $filters = CRM_Dataprocessor_BAO_Filter::getValues(array('data_processor_id' => $id));
    $dataProcessor['filters']  = array();
    foreach($filters as $i => $filter) {
      unset($filter['id']);
      unset($filter['data_processor_id']);
      $dataProcessor['filters'][] = $filter;
    }
    $fields = CRM_Dataprocessor_BAO_Field::getValues(array('data_processor_id' => $id));
    $dataProcessor['fields'] = array();
    foreach($fields as $i => $field) {
      unset($field['id']);
      unset($field['data_processor_id']);
      $dataProcessor['fields'][] = $field;
    }
    $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('data_processor_id' => $id));
    $dataProcessor['outputs'] = array();
    foreach($outputs as $i => $output) {
      unset($output['id']);
      unset($output['data_processor_id']);
      $dataProcessor['outputs'][] = $output;
    }

    $eventData['data_processor'] = &$dataProcessor;
    $event = \Civi\Core\Event\GenericHookEvent::create($eventData);
    \Civi::dispatcher()->dispatch('hook_civicrm_dataprocessor_export', $event);

    return $dataProcessor;
  }

  /**
   * Revert a data processor to the state in code.
   */
  public static function revert($data_processor_id) {
    $dao = \CRM_Core_DAO::executeQuery("SELECT status, source_file FROM civicrm_data_processor WHERE id = %1", array(1=>array($data_processor_id, 'Integer')));
    if (!$dao->fetch()) {
      return false;
    }
    if ($dao->status != CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN) {
      return false;
    }
    $key = substr($dao->source_file, 0, stripos($dao->source_file, "/"));
    $extension = civicrm_api3('Extension', 'getsingle', array('key' => $key));
    $filename = $extension['path'].substr($dao->source_file, stripos($dao->source_file, "/"));
    $data = file_get_contents($filename);
    $data = json_decode($data, true);

    CRM_Dataprocessor_Utils_Importer::importDataProcessor($data, $dao->source_file, $data_processor_id);
    return true;
  }

}