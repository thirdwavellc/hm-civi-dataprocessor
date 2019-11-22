<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
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
class CRM_Myextension_OutputResultTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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


  public function checkRecord($record,$id) {
    
    $check = array();

    foreach(array_keys($record) as $key){
      $check[$key] = $record[$key]->formattedValue;
    }

    if(empty(array_diff($check,$this->check_results[$id])))  // Check for difference in array 
      return True;
    else
      return False;
  }


  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testCreateDataProcessorField() {

      $params['name'] = "test";
      $params['title'] = "title";
      $params['description'] = 'Creating a Test Description';
      $params['is_active'] = 1;

      // Creating a DataProcessor
      civicrm_api3('DataProcessor', 'create', $params);

      // Retrieving the data processor
      $result = civicrm_api3('DataProcessor', 'get');

      if(isset($result['id'])){
      // Retrieving the id of data processor
        $data_processor_id = $result['id'];

        $params = [];
            // Params for setting data source parameters

        $params['data_processor_id'] = $data_processor_id;
        $params['title'] = 'testDataSource';
        $params['type'] = 'contact';

        // Creating a DataProcessor Source
        civicrm_api3('DataProcessorSource', 'create', $params);

        $result_datasource = civicrm_api3('DataProcessorSource', 'get');

        if(isset($result_datasource['id'])){

          $id_datasource = $result_datasource['id'];

          $params = [];
          // Params for setting data field parameters

          $params['data_processor_id'] = $data_processor_id;
          $params['title'] = 'id_test';
          $params['type'] = 'raw';
          $params['configuration'] = array("field" => "id","datasource" =>"testdatasource");

          civicrm_api3('DataProcessorField', 'create', $params);

          $params['title'] = 'first_name_test';
          $params['configuration'] = array("field" => "display_name","datasource" =>"testdatasource");

          civicrm_api3('DataProcessorField', 'create', $params);


          $result_field = civicrm_api3('DataProcessorField', 'get');

          $field_check = True; //True if no error in adding Data Processor Field

          foreach ($result_field['values'] as $key => $value) {
            if(!isset($value['id']))
            {
              $field_check = False;
              break;
            }
          }

          if($field_check){      //If field_check is false then fields of data processor has not been setup    

              $params = [];      // Params for setting data source parameters

              $params['data_processor_id'] = $data_processor_id;
              $params['type'] = 'contact_search';
              $params['configuration'] = array("title" => "contact","contact_id_field" =>"id_test");

              civicrm_api3('DataProcessorOutput', 'create', $params);

              $result_output = civicrm_api3('DataProcessorOutput', 'get');
              $outputId = $result_output['id'];


              $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $data_processor_id));
              $output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $outputId));
              $dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);;

              try
              {
                while($record = $dataProcessorClass->getDataFlow()->nextRecord()){
                  $check_results_id = $record['id_test']->formattedValue;
                  if($this->checkRecord($record,$check_results_id)){
                    $this->assertTrue(true);                  
                  }
                  else{
                    echo "Output results don't match.";
                    $this->assertFalse(true);                  
                  }
                }
              }
              catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
                // Do nothing
              }
          } 
          else{
            echo "Failed to add DataProcessor Field";
            $this->assertFalse(true);                  
          }


        }
        else{

          echo "Failed to add DataProcessorSource";
          $this->assertFalse(true);      
        }
      }
      else{
        echo "DataProcessor Failed to Setup";
        $this->assertFalse(true);    
      }

  }





}
