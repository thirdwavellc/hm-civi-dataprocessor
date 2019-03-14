<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessorOutput.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_output_create_spec(&$spec) {
  $spec['id'] = array(
		'title' => E::ts('ID'),
		'type' => CRM_Utils_Type::T_INT,
		'api.required' => false
	);
  $spec['data_processor_id'] = array(
    'title' => E::ts('Data Processor ID'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => true,
  );
  $spec['type'] = array(
    'title' => E::ts('Type'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true
  );
	$spec['title'] = array(
		'title' => E::ts('Title'),
		'type' => CRM_Utils_Type::T_STRING,
		'api.required' => true
	);
	$spec['configuration'] = array(
    'title' => E::ts('Description'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
	);
  $spec['permission'] = array(
    'name' => 'permission',
    'title' => E::ts('Permission'),
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['api_entity'] = array(
    'name' => 'api_entity',
    'title' => E::ts('API Entity'),
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['api_action'] = array(
    'name' => 'api_action',
    'title' => E::ts('API Action name'),
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['api_count_action'] = array(
    'name' => 'api_count_action',
    'title' => E::ts('API GetCount Action name'),
    'type' => CRM_Utils_Type::T_STRING
  );
}

/**
 * DataProcessorOutput.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 *
 *
 */
function civicrm_api3_data_processor_output_create($params) {
  $returnValue = CRM_Dataprocessor_BAO_Output::add($params);
	$returnValues[$returnValue['id']] = $returnValue;
  return civicrm_api3_create_success($returnValues, $params, 'DataProcessorOutput', 'Create');
}

