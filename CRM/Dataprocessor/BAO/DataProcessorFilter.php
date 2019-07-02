<?php
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_BAO_DataProcessorFilter extends CRM_Dataprocessor_DAO_DataProcessorFilter {

  public static function checkName($title, $data_processor_id, $id=null,$name=null) {
    if (!$name) {
      $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($title));
    }

    $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($name));
    $name_part = $name;

    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_filter` WHERE `name` = %1 AND `data_processor_id` = %2";
    $sqlParams[1] = array($name, 'String');
    $sqlParams[2] = array($data_processor_id, 'String');
    if ($id) {
      $sql .= " AND `id` != %3";
      $sqlParams[3] = array($id, 'Integer');
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
   * Delete function so that the hook for deleting an output gets invoked.
   *
   * @param $id
   */
  public static function del($id) {
    CRM_Utils_Hook::pre('delete', 'DataProcessorFilter', $id, CRM_Core_DAO::$_nullArray);

    $dao = new CRM_Dataprocessor_BAO_DataProcessorFilter();
    $dao->id = $id;
    if ($dao->find(true)) {
      $dao->delete();
    }

    CRM_Utils_Hook::post('delete', 'DataProcessorFilter', $id, CRM_Core_DAO::$_nullArray);
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

    $field = new CRM_Dataprocessor_DAO_DataProcessorFilter();
    $field->data_processor_id = $id;
    $field->find(FALSE);
    while ($field->fetch()) {
      civicrm_api3('DataProcessorFilter', 'delete', array('id' => $field->id));
    }
  }

}
