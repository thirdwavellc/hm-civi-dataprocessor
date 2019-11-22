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
class CRM_Myextension_CreateDataProcessorFieldTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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
        $factory = dataprocessor_get_factory();
        $data_sources = $factory->getDataSources();

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
              // Params for setting data source parameters

          $params['data_processor_id'] = $data_processor_id;
          $params['title'] = 'testDataField';
          $params['type'] = 'raw';
          $params['configuration'] = array("field" => "id","datasource" =>"contact");

          civicrm_api3('DataProcessorField', 'create', $params);

          $result_field = civicrm_api3('DataProcessorField', 'get');

          if(isset($result_field['id'])){          

              $id_datafield = $result_field['id'];

              $this->assertEquals('testDataField', $result_field['values'][$id_datafield]['title']);
              $this->assertEquals('raw', $result_field['values'][$id_datafield]['type']);
              $this->assertEquals($data_processor_id, $result_field['values'][$id_datafield]['data_processor_id']);
              $this->assertEquals('id', $result_field['values'][$id_datafield]['configuration']['field']);
              $this->assertEquals('contact', $result_field['values'][$id_datafield]['configuration']['datasource']);

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

      // $factory = dataprocessor_get_factory();
      // $data_sources = $factory->getDataSources();
      

  }


}
