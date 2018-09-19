<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessorSource.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_source_create_spec(&$spec) {
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
  $spec['name'] = array(
    'title' => E::ts('Name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true
  );
	$spec['title'] = array(
		'title' => E::ts('Title'),
		'type' => CRM_Utils_Type::T_STRING,
		'api.required' => true
	);
	$spec['configuration'] = array(
    'title' => E::ts('Configuration'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
	);
  $spec['join_type'] = array(
    'title' => E::ts('Join Type'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => false
  );
  $spec['join_configuration'] = array(
    'title' => E::ts('Join Configuration'),
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => false,
  );
}

/**
 * DataProcessorSource.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 *
 *
 */
function civicrm_api3_data_processor_source_create($params) {
  $returnValue = CRM_Dataprocessor_BAO_Source::add($params);
	$returnValues[$returnValue['id']] = $returnValue;
  return civicrm_api3_create_success($returnValues, $params, 'DataProcessorSource', 'Create');
}

