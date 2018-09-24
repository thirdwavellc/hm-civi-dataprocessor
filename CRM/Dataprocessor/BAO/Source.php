<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_BAO_Source extends CRM_Dataprocessor_DAO_Source {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $factory = dataprocessor_get_factory();
    $types = $factory->getDataSources();

    $result = array();
    $source = new CRM_Dataprocessor_DAO_Source();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $source->$key = $value;
        }
      }
    }
    $source->find();
    while ($source->fetch()) {
      $row = array();
      self::storeValues($source, $row);

      if (isset($types[$row['type']])) {
        $row['type_name'] = $types[$row['type']];
      } else {
        $row['type_name'] = '';
      }
      if (!empty($row['configuration'])) {
        $row['configuration'] = json_decode($row['configuration'], true);
      } else {
        $row['configuration'] = array();
      }
      if (!empty($row['join_configuration'])) {
        $row['join_configuration'] = json_decode($row['join_configuration'], true);
      } else {
        $row['join_configuration'] = array();
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
      throw new Exception('Params can not be empty when adding or updating a data processor source');
    }

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', 'DataProcessorSource', $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', 'DataProcessorSource', NULL, $params);
    }

    $source = new CRM_Dataprocessor_DAO_Source();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $source->$key = $value;
      }
    }
    if (isset($source->configuration) && is_array($source->configuration)) {
      $source->configuration = json_encode($source->configuration);
    }
    if (isset($source->join_configuration) && is_array($source->join_configuration)) {
      $source->join_configuration = json_encode($source->join_configuration);
    }

    $source->save();
    self::storeValues($source, $result);

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', 'DataProcessorSource', $source->id, $source);
    }
    else {
      CRM_Utils_Hook::post('create', 'DataProcessorSource', $source->id, $source);
    }

    return $result;
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
   * @param int $data_procssor_id,
   * @param int $id optional
   * @return bool
   * @static
   */
  public static function isNameValid($name, $data_procssor_id, $id=null) {
    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_source` WHERE `name` = %1 AND `data_processor_id` = %2";
    $params[1] = array($name, 'String');
    $params[2] = array($data_procssor_id, 'Integer');
    if ($id) {
      $sql .= " AND `id` != %3";
      $params[3] = array($id, 'Integer');
    }
    $count = CRM_Core_DAO::singleValueQuery($sql, $params);
    return ($count > 0) ? false : true;
  }

  /**
   * Function to delete a Data Processor Source with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor source');
    }

    CRM_Utils_Hook::pre('delete', 'DataProcessorSource', $id, CRM_Core_DAO::$_nullArray);

    $source = new CRM_Dataprocessor_DAO_Source();
    $source->id = $id;
    $source->delete();

    CRM_Utils_Hook::post('delete', 'DataProcessorSource', $id, CRM_Core_DAO::$_nullArray);

    return;
  }

  /**
   * Function to delete a Data Processor Source with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithDataProcessorId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor source');
    }

    $source = new CRM_Dataprocessor_DAO_Source();
    $source->data_processor_id = $id;
    $source->find(FALSE);
    while ($source->fetch()) {
      self::deleteWithId($source->id);
    }
  }

  /**
   * @param $source
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public static function getSourceClass($source, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor) {
    $factory = dataprocessor_get_factory();
    $sourceClass = $factory->getDataSourceByName($source['type']);
    $sourceClass->setSourceName($source['name']);
    $sourceClass->setSourceTitle($source['title']);
    $sourceClass->setConfiguration($source['configuration']);
    $sourceClass->setDataProcessor($dataProcessor);
    $join = null;
    if ($source['join_type']) {
      $join = $factory->getJoinByName($source['join_type']);
      $join->setConfiguration($source['join_configuration']);
      $join->setDataProcessor($dataProcessor);
    }
    $dataProcessor->addDataSource($sourceClass, $join);
    if ($join) {
      $join->initialize();
      $sourceClass->setJoin($join);
    }
    return $sourceClass;
  }

}