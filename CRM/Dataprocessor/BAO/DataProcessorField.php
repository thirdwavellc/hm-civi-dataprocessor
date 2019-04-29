<?php
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_BAO_DataProcessorField extends CRM_Dataprocessor_DAO_DataProcessorField {

  public static function checkName($title, $data_processor_id, $id=null,$name=null) {
    if (!$name) {
      $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($title));
    }

    $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($name));
    $name_part = $name;

    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_field` WHERE `name` = %1 AND `data_processor_id` = %2";
    $sqlParams[1] = array($name, 'String');
    $sqlParams[2] = array($data_processor_id, 'String');
    if (isset($id)) {
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

}
