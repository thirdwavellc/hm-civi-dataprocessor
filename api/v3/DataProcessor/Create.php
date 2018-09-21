<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessor.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_create_spec(&$spec) {
  $spec['id'] = array(
		'title' => E::ts('ID'),
		'type' => CRM_Utils_Type::T_INT,
		'api.required' => false
	);
	$spec['title'] = array(
		'title' => E::ts('Title'),
		'type' => CRM_Utils_Type::T_STRING,
		'api.required' => true
	);
  $spec['name'] = array(
    'title' => E::ts('Name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true
  );
  $spec['type'] = array(
    'title' => E::ts('Type'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true
  );
	$spec['is_active'] = array(
		'title' => E::ts('Is active'),
		'type' => CRM_Utils_Type::T_BOOLEAN,
		'api.required' => true,
		'api.default' => true,
	);
	$spec['description'] = array(
		'title' => E::ts('Description'),
		'type' => CRM_Utils_Type::T_TEXT,
		'api.required' => false,
	);
  $spec['configuration'] = array(
    'title' => E::ts('Configuration'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
  );
  $spec['aggregation'] = array(
    'title' => E::ts('Aggregation'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
  );
  $spec['storage_type'] = array(
    'title' => E::ts('Storage Type'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => false
  );
  $spec['storage_configuration'] = array(
    'title' => E::ts('Storage Configuration'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
  );
}

/**
 * DataProcessor.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 *
 *
 */
function civicrm_api3_data_processor_create($params) {
  if (!isset($params['id']) && empty($params['title'])) {
    return civicrm_api3_create_error('Title can not be empty when adding a new DataProcessor');
  }

  $returnValue = CRM_Dataprocessor_BAO_DataProcessor::add($params);
	$returnValues[$returnValue['id']] = $returnValue;
  return civicrm_api3_create_success($returnValues, $params, 'DataProcessor', 'Create');
}

