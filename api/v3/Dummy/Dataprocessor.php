<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Dummy.Dataprocessor API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_dummy_Dataprocessor_spec(&$spec) {

}

/**
 * Dummy.Dataprocessor API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_dummy_Dataprocessor($params) {
  $return = array();
  $dummy = new \Civi\DataProcessor\DummyDataProcessor();
  $return = $dummy->dataprocessor->getDataFlow()->allRecords();
  return civicrm_api3_create_success($return);
}
