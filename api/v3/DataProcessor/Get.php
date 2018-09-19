<?php
/**
 * DataProcessor.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_data_processor_get($params) {
  $returnValues = CRM_Dataprocessor_BAO_DataProcessor::getValues($params);
  return civicrm_api3_create_success($returnValues, $params, 'DataProcessor', 'Get');
}

/**
 * DataProcessor.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_get_spec(&$spec) {
	$fields = CRM_Dataprocessor_BAO_DataProcessor::fields();
	foreach($fields as $fieldname => $field) {
		$spec[$fieldname] = $field;
	}
}

