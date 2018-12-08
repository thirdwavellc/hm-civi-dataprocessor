<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_BAO_Field extends CRM_Dataprocessor_DAO_Field {

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
    $field = new CRM_Dataprocessor_DAO_Field();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $field->$key = $value;
        }
      }
    }
    $field->find();
    while ($field->fetch()) {
      $row = array();
      self::storeValues($field, $row);

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
      throw new Exception('Params can not be empty when adding or updating a data processor field');
    }

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', 'DataProcessorField', $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', 'DataProcessorField', NULL, $params);
    }

    $field = new CRM_Dataprocessor_DAO_Field();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $field->$key = $value;
      }
    }
    if (isset($field->configuration) && is_array($field->configuration)) {
      $field->configuration = json_encode($field->configuration);
    }

    $field->save();
    $id = $field->id;
    $field = new CRM_Dataprocessor_BAO_Field();
    $field->id = $id;
    $field->find(true);
    CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($field->data_processor_id);
    self::storeValues($field, $result);

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', 'DataProcessorField', $field->id, $field);
    }
    else {
      CRM_Utils_Hook::post('create', 'DataProcessorField', $field->id, $field);
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
    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_field` WHERE `name` = %1 AND `data_processor_id` = %2";
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
   * Function to delete a Data Processor Field with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor field');
    }

    CRM_Utils_Hook::pre('delete', 'DataProcessorField', $id, CRM_Core_DAO::$_nullArray);

    $field = new CRM_Dataprocessor_DAO_Field();
    $field->id = $id;
    $field->delete();

    CRM_Utils_Hook::post('delete', 'DataProcessorField', $id, CRM_Core_DAO::$_nullArray);

    return;
  }

  /**
   * Function to delete a Data Processor Field with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithDataProcessorId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor field');
    }

    $field = new CRM_Dataprocessor_DAO_Field();
    $field->data_processor_id = $id;
    $field->find(FALSE);
    while ($field->fetch()) {
      self::deleteWithId($field->id);
    }
  }

}