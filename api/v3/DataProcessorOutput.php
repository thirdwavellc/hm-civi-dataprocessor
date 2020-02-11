<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessorOutput.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_output_create_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorOutput::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
    if ($fieldname != 'id' && isset($field['required']) && $field['required']) {
      $spec[$fieldname]['api.required'] = true;
    }
  }
}

/**
 * DataProcessorOutput.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_output_create($params) {
  $return = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  $dataProcessorId = civicrm_api3('DataProcessorOutput', 'getvalue', array('id' => $return['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  return $return;
}

/**
 * DataProcessorOutput.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_output_delete($params) {
  $dataProcessorId = civicrm_api3('DataProcessorOutput', 'getvalue', array('id' => $params['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  $return = _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  CRM_Dataprocessor_Utils_Cache::clearAllDataProcessorCaches();
  return $return;
}

/**
 * DataProcessorOutput.get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_data_processor_output_get_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorOutput::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
  }
}

/**
 * DataProcessorOutput.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_output_get($params) {
  $return = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  foreach($return['values'] as $id => $value) {
    if (isset($value['configuration'])) {
      $return['values'][$id]['configuration'] = json_decode($value['configuration'], TRUE);
    } else {
      $return['values'][$id]['configuration'] = array();
    }
  }
  return $return;
}
