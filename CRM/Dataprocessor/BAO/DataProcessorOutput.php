<?php
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_BAO_DataProcessorOutput extends CRM_Dataprocessor_DAO_DataProcessorOutput {

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
      throw new Exception('id can not be empty when attempting to delete a data processor filter');
    }

    $field = new CRM_Dataprocessor_DAO_DataProcessorOutput();
    $field->data_processor_id = $id;
    $field->find(FALSE);
    while ($field->fetch()) {
      civicrm_api3('DataProcessorOutput', 'delete', array('id' => $field->id));
    }
  }

  /**
   * Delete function so that the hook for deleting an output gets invoked.
   *
   * @param $id
   */
  public static function del($id) {
    CRM_Utils_Hook::pre('delete', 'DataProcessorOutput', $id, CRM_Core_DAO::$_nullArray);

    $dao = new CRM_Dataprocessor_BAO_DataProcessorOutput();
    $dao->id = $id;
    if ($dao->find(true)) {
      $dao->delete();
    }

    CRM_Utils_Hook::post('delete', 'DataProcessorOutput', $id, CRM_Core_DAO::$_nullArray);
  }

}
