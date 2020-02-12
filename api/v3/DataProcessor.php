<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessor.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_create_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessor::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
  }
}

/**
 * DataProcessor.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_create($params) {
  $id = null;
  if (isset($params['id'])) {
    $id = $params['id'];
  }
  if (!isset($params['id']) && !isset($params['status'])) {
    $params['status'] = CRM_Dataprocessor_Status::STATUS_IN_DATABASE;
  }
  if (isset($params['title'])) {
    $params['name'] = CRM_Dataprocessor_BAO_DataProcessor::checkName($params['title'], $id, $params['name']);
  }
  if (!isset($params['id']) && !isset($params['type'])) {
    $params['type'] = 'default';
  }
  $return = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($return['id']);
  CRM_Dataprocessor_Utils_Cache::clearAllDataProcessorCaches();
  return $return;
}

/**
 * DataProcessor.delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_data_processor_delete_spec(&$spec) {
  $spec['id']['api.required'] = true;
}

/**
 * DataProcessor.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_delete($params) {
  CRM_Dataprocessor_BAO_DataProcessorOutput::deleteWithDataProcessorId($params['id']);
  CRM_Dataprocessor_BAO_DataProcessorField::deleteWithDataProcessorId($params['id']);
  CRM_Dataprocessor_BAO_DataProcessorFilter::deleteWithDataProcessorId($params['id']);
  CRM_Dataprocessor_BAO_DataProcessorSource::deleteWithDataProcessorId($params['id']);
  CRM_Dataprocessor_Utils_Cache::clearAllDataProcessorCaches();
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * DataProcessor.get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_data_processor_get_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessor::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
  }
}

/**
 * DataProcessor.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_get($params) {
  $return = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  foreach($return['values'] as $id => $value) {
    if (isset($value['configuration'])) {
      $return['values'][$id]['configuration'] = json_decode($value['configuration'], TRUE);
    } else {
      $return['values'][$id]['configuration'] = array();
    }
    if (isset($value['storage_configuration'])) {
      $return['values'][$id]['storage_configuration'] = json_decode($value['storage_configuration'], TRUE);
    } else {
      $return['values'][$id]['storage_configuration'] = array();
    }
  }
  return $return;
}

/**
 * DataProcessor.check_name API specification
 *
 * @param $params
 */
function _civicrm_api3_data_processor_check_name_spec($params) {
  $params['id'] = array(
    'name' => 'id',
    'title' => E::ts('ID'),
  );
  $params['title'] = array(
    'name' => 'title',
    'title' => E::ts('Title'),
    'api.required' => true,
  );
  $params['name'] = array(
    'name' => 'name',
    'title' => E::ts('Name'),
  );
}

/**
 * DataProcessor.check_name API
 *
 * @param $params
 */
function civicrm_api3_data_processor_check_name($params) {
  $name = CRM_Dataprocessor_BAO_DataProcessor::checkName($params['title'], $params['id'], $params['name']);
  return array(
    'name' => $name,
  );
}

/**
 * DataProcessor.import API specification
 *
 * @param $params
 */
function _civicrm_api3_data_processor_import_spec($params) {
  $params['extension'] = [
    'name' => 'extension',
    'title' => E::ts('Extension'),
    'api.required' => FALSE,
  ];
  return $params;
}

/**
 * DataProcessor.Import API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_data_processor_import($params) {
  $returnValues = array();
  $extension = null;
  if (isset($params['extension'])) {
    $extension = $params['extension'];
  }
  $returnValues['import'] = CRM_Dataprocessor_Utils_Importer::importFromExtensions($extension);
  $returnValues['is_error'] = 0;
  return $returnValues;
}
