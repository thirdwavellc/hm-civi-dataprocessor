<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test we can create an output.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Dataprocessor_OutputResultTest extends CRM_Dataprocessor_TestBase {

  var $check_results = array(
    1 => array(
      'id_test' => 1,
      'first_name_test' => 'Default Organization',
    ),

    2 => array(
      'id_test' => 2,
      'first_name_test' => 'Second Domain',
    ),

  );


  public function checkRecord($record, $id) {

    $check = array();

    foreach (array_keys($record) as $key){
      $check[$key] = $record[$key]->formattedValue;
    }

    $this->assertEquals($check, $this->check_results[$id]);
  }


  public function testCreateDataProcessorField() {

    $id_dataprocessor = $this->createTestDataProcessorFixture();
    $id_datasource = $this->createTestDataProcessorSourceFixture();

    // Create the ID field
    $params = [];
    $params['data_processor_id'] = $id_dataprocessor;
    $params['title'] = 'id_test';
    $params['type'] = 'raw';
    $params['configuration'] = array("field" => "id", "datasource" => "testdatasource");
    $id_datafield_id = civicrm_api3('DataProcessorField', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_datafield_id, "Failed to call DataProcessorField.create with " . json_encode($params));


    // Create the first name field.
    $params['title'] = 'first_name_test';
    $params['configuration'] = array("field" => "display_name", "datasource" => "testdatasource");
    $id_datafield_first_name = civicrm_api3('DataProcessorField', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_datafield_first_name, "Failed to call DataProcessorField.create with " . json_encode($params));

    $result_field = civicrm_api3('DataProcessorField', 'get');

    $field_check = TRUE; //True if no error in adding Data Processor Field
    foreach ($result_field['values'] as $value) {
      $this->assertArrayHasKey('id', $value, "Failed setting up the DataProcessorField");
    }

    $params = [];      // Params for setting data source parameters
    $params['data_processor_id'] = $id_dataprocessor;
    $params['type'] = 'contact_search';
    $params['configuration'] = array("title" => "contact","contact_id_field" =>"id_test");

    $id_dataprocessor_output = civicrm_api3('DataProcessorOutput', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_dataprocessor_output, "Failed calling DataProcessorOutput.create with " . json_encode($params));

    $result_output = civicrm_api3('DataProcessorOutput', 'get', ['id' => $id_dataprocessor_output]);
    $this->assertEquals(1, $result_output['count']);

    $dataProcessor = $this->data_processors[$id_dataprocessor];
    $dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);;

    try {
      while ($record = $dataProcessorClass->getDataFlow()->nextRecord()) {
        $check_results_id = $record['id_test']->formattedValue;
        $this->checkRecord($record, $check_results_id, "Failed checking output");
      }
    }
    catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }
  }

}
