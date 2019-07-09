<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessorFilter.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_filter_create_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorFilter::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
    if ($fieldname != 'id' && isset($field['required']) && $field['required']) {
      $spec[$fieldname]['api.required'] = true;
    }
  }
}

/**
 * DataProcessorFilter.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_filter_create($params) {
  if (!isset($params['weight']) && !isset($params['id'])) {
    $params['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Dataprocessor_DAO_DataProcessorFilter', array('data_processor_id' => $params['data_processor_id']));
  }
  $id = null;
  if (isset($params['id'])) {
    $id = $params['id'];
  }
  $name = null;
  if (isset($params['name'])) {
    $name = $params['name'];
  }
  $params['name'] = CRM_Dataprocessor_BAO_DataProcessorFilter::checkName($params['title'], $params['data_processor_id'], $id, $name);
  $return = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  $dataProcessorId = civicrm_api3('DataProcessorFilter', 'getvalue', array('id' => $return['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  return $return;
}

/**
 * DataProcessorFilter.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_filter_delete($params) {
  $dataProcessorId = civicrm_api3('DataProcessorFilter', 'getvalue', array('id' => $params['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * DataProcessorFilter.get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_data_processor_filter_get_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorFilter::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
  }
}

/**
 * DataProcessorFilter.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_filter_get($params) {
  if (!isset($params['options']) || !isset($params['options']['sort'])) {
    $params['options']['sort'] = 'weight ASC';
  }
  $return = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  foreach($return['values'] as $id => $value) {
    if (isset($value['configuration'])) {
      $return['values'][$id]['configuration'] = json_decode($value['configuration'], TRUE);
    }
    if (isset($value['filter_value'])) {
      $return['values'][$id]['filter_value'] = json_decode($value['filter_value'], TRUE);
    } else {
      $return['values'][$id]['filter_value'] = array();
    }
  }
  return $return;
}


/**
 * DataProcessorFilter.check_name API specification
 *
 * @param $params
 */
function _civicrm_api3_data_processor_filter_check_name_spec($params) {
  $params['id'] = array(
    'name' => 'id',
    'title' => E::ts('ID'),
  );
  $params['title'] = array(
    'name' => 'title',
    'title' => E::ts('Title'),
    'api.required' => true,
  );
  $params['data_processor_id'] = array(
    'name' => 'data_processor_id',
    'title' => E::ts('Data Processor Id'),
    'api.required' => true,
  );
  $params['name'] = array(
    'name' => 'name',
    'title' => E::ts('Name'),
  );
}

/**
 * DataProcessorFilter.check_name API
 *
 * @param $params
 */
function civicrm_api3_data_processor_filter_check_name($params) {
  $name = CRM_Dataprocessor_BAO_DataProcessorFilter::checkName($params['title'], $params['data_processor_id'], $params['id'], $params['name']);
  return array(
    'name' => $name,
  );
}
