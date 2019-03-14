<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_BAO_Output extends CRM_Dataprocessor_DAO_Output {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $factory = dataprocessor_get_factory();
    $types = $factory->getOutputs();

    $result = array();
    $output = new CRM_Dataprocessor_DAO_Output();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $output->$key = $value;
        }
      }
    }
    $output->find();
    while ($output->fetch()) {
      $row = array();
      self::storeValues($output, $row);

      if (isset($types[$row['type']])) {
        $row['type_name'] = $types[$row['type']];
      } else {
        $row['type_name'] = '';
      }

      if (isset($row['configuration']) && is_string($row['configuration']) && strlen($row['configuration'])) {
        $row['configuration'] = json_decode($row['configuration'], true);
      } else {
        $row['configuration'] = array();
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
      throw new Exception('Params can not be empty when adding or updating a data processor output');
    }

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', 'DataProcessorOutput', $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', 'DataProcessorOutput', NULL, $params);
    }

    $output = new CRM_Dataprocessor_DAO_Output();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $output->$key = $value;
      }
    }
    if (!isset($output->configuration)) {
      $output->configuration = array();
    }
    if (is_array($output->configuration)) {
      $output->configuration = json_encode($output->configuration);
    }

    $output->save();
    $id = $output->id;
    $output = new CRM_Dataprocessor_BAO_Output();
    $output->id = $id;
    $output->find(true);
    CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($output->data_processor_id);
    self::storeValues($output, $result);

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', 'DataProcessorOutput', $output->id, $output);
    }
    else {
      CRM_Utils_Hook::post('create', 'DataProcessorOutput', $output->id, $output);
    }

    return $result;
  }

  /**
   * Function to delete a Data Processor Output with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor output');
    }

    CRM_Utils_Hook::pre('delete', 'DataProcessorOutput', $id, CRM_Core_DAO::$_nullArray);

    $output = new CRM_Dataprocessor_DAO_Output();
    $output->id = $id;
    $output->delete();

    CRM_Utils_Hook::post('delete', 'DataProcessorOutput', $id, CRM_Core_DAO::$_nullArray);

    return;
  }

  /**
   * Function to delete a Data Processor Output with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithDataProcessorId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor output');
    }

    $output = new CRM_Dataprocessor_DAO_Output();
    $output->data_processor_id = $id;
    $output->find(FALSE);
    while ($output->fetch()) {
      self::deleteWithId($output->id);
    }
  }

}