<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_Utils_Importer {

  public static function import($data, $filename) {
    $data_processor_id = CRM_Dataprocessor_BAO_DataProcessor::getId($data['name']);
    $status = CRM_Dataprocessor_BAO_DataProcessor::getStatus($data['name']);
    $new_status = null;
    $new_id = null;

    switch ($status) {
      case CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_DATABASE:
        // Update to overriden
        CRM_Dataprocessor_BAO_DataProcessor::setStatusAndSourceFile($data['name'], CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN, $filename);
        $new_id = $data_processor_id;
        $new_status = CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN;
        break;
      case CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN:
        $new_id = $data_processor_id;
        $new_status = CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN;
        break;
      default:
        $new_id = self::importDataProcessor($data, $filename, $data_processor_id);
        $new_status = CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_CODE;
        break;
    }

    $return = array(
      'original_id' => $data_processor_id,
      'new_id' => $new_id,
      'original_status' => $status,
      'new_status' => $new_status,
      'file' => $filename,
    );

    return $return;
  }

  /**
   * Import a data processor
   *
   * @param $data
   * @param $filename
   * @param $data_processor_id
   *
   * @return mixed
   * @throws \Exception
   */
  public static function importDataProcessor($data, $filename, $data_processor_id) {
    $params = $data;
    unset($params['data_sources']);
    unset($params['outputs']);
    if ($data_processor_id) {
      $params['id'] = $data_processor_id;
    }
    if (!isset($params['configuration'])) {
      $params['configuration'] = array();
    }
    if (!isset($params['storage_configuration'])) {
      $params['storage_configuration'] = array();
    }
    $params['status'] = CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_CODE;
    $params['source_file'] = $filename;
    $result = CRM_Dataprocessor_BAO_DataProcessor::add($params);
    $id = $result['id'];

    // Clear all existing data sources and outputs
    CRM_Dataprocessor_BAO_Source::deleteWithDataProcessorId($id);
    CRM_Dataprocessor_BAO_Field::deleteWithDataProcessorId($id);
    CRM_Dataprocessor_BAO_Output::deleteWithDataProcessorId($id);

    foreach($data['data_sources'] as $data_source) {
      $params = $data_source;
      $params['data_processor_id'] = $id;
      $result = CRM_Dataprocessor_BAO_Source::add($params);
    }
    foreach($data['fields'] as $field) {
      $params = $field;
      $params['data_processor_id'] = $id;
      $result = CRM_Dataprocessor_BAO_Field::add($params);
    }
    foreach($data['outputs'] as $output) {
      $params = $output;
      $params['data_processor_id'] = $id;
      $result = CRM_Dataprocessor_BAO_Output::add($params);
    }

    return $id;
  }


  /**
   * Imports data processor from files in an extension directory.
   *
   * This scans the extension directory data-processors/ for json files.
   */
  public static function importFromExtensions() {
    $return = array();
    $importedIds = array();
    $extensions = self::getExtensionFileListWithDataProcessors();
    foreach($extensions as $ext_file) {
      $data = json_decode($ext_file['data'], true);
      $return[$ext_file['file']] = self::import($data, $ext_file['file']);
      $importedIds[] = $return[$ext_file['file']]['new_id'];
    }

    // Remove all form processors which are in code or overridden but not imported
    $dao = CRM_Core_DAO::executeQuery("SELECT id, name FROM civicrm_data_processor WHERE id NOT IN (".implode($importedIds, ",").") AND status IN (".CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_CODE.", ".CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN.")");
    while ($dao->fetch()) {
      CRM_Dataprocessor_BAO_DataProcessor::deleteWithId($dao->id);
      $return['deleted data processors'][] = $dao->id.": ".$dao->name;
    }
    return $return;
  }

  /**
   * Returns a list with data-processor files within an extension folder.
   *
   * @return array
   */
  private static function getExtensionFileListWithDataProcessors() {
    $return = array();
    $extensions = civicrm_api3('Extension', 'get', array('options' => array('limit' => 0)));
    foreach($extensions['values'] as $ext) {
      if ($ext['status'] != 'installed') {
        continue;
      }

      $path = $ext['path'].'/data-processors';
      if (!is_dir($path)) {
        continue;
      }

      foreach (glob($path."/*.json") as $file) {
        $return[] = array(
          'file' => $ext['key']. '/data-processors/'.basename($file),
          'data' => file_get_contents($file),
        );
      }
    }
    return $return;
  }

}