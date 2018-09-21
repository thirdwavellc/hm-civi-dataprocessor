<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_BAO_Filter extends CRM_Dataprocessor_DAO_Filter {

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
    $filter = new CRM_Dataprocessor_DAO_Filter();
    if (!empty($params)) {
      $filters = self::fields();
      foreach ($params as $key => $value) {
        if (isset($filters[$key])) {
          $filter->$key = $value;
        }
      }
    }
    $filter->find();
    while ($filter->fetch()) {
      $row = array();
      self::storeValues($filter, $row);

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
      throw new Exception('Params can not be empty when adding or updating a data processor filter');
    }

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', 'DataProcessorFilter', $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', 'DataProcessorFilter', NULL, $params);
    }

    $filter = new CRM_Dataprocessor_DAO_Filter();
    $filters = self::fields();
    foreach ($params as $key => $value) {
      if (isset($filters[$key])) {
        $filter->$key = $value;
      }
    }
    if (isset($filter->configuration) && is_array($filter->configuration)) {
      $filter->configuration = json_encode($filter->configuration);
    }

    $filter->save();
    self::storeValues($filter, $result);

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', 'DataProcessorFilter', $filter->id, $filter);
    }
    else {
      CRM_Utils_Hook::post('create', 'DataProcessorFilter', $filter->id, $filter);
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
    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_filter` WHERE `name` = %1 AND `data_processor_id` = %2";
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
   * Function to delete a Data Processor Filter with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor filter');
    }

    CRM_Utils_Hook::pre('delete', 'DataProcessorFilter', $id, CRM_Core_DAO::$_nullArray);

    $filter = new CRM_Dataprocessor_DAO_Filter();
    $filter->id = $id;
    $filter->delete();

    CRM_Utils_Hook::post('delete', 'DataProcessorFilter', $id, CRM_Core_DAO::$_nullArray);

    return;
  }

  /**
   * Function to delete a Data Processor Filter with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithDataProcessorId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor filter');
    }

    $filter = new CRM_Dataprocessor_DAO_Filter();
    $filter->data_processor_id = $id;
    $filter->find(FALSE);
    while ($filter->fetch()) {
      self::deleteWithId($filter->id);
    }
  }

}