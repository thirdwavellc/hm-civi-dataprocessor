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
class CRM_Myextension_ExportDataProcessorTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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

  public function checkContent($json){

    $returnvalue = True;

    if($json->name != 'test')
      $returnvalue = False;

    if($json->title != 'title')
      $returnvalue = False;

    if($json->description != 'Creating a Test Description')
      $returnvalue = False;

    if(sizeof($json->data_sources)==1){
      $datasource = reset($json->data_sources);
      if($datasource->name != 'testdatasource')
        $returnvalue = False;
      if($datasource->title != 'testDataSource')
        $returnvalue = False;
      if($datasource->type != 'contact')
        $returnvalue = False;
    }
    else{
      $returnvalue = False;      
    }

    // print_r($json->fields);

    if(sizeof($json->fields)==2){
      $id_field = $json->fields[0];
      if($id_field->name != 'id_test')
        $returnvalue = False;
      if($id_field->configuration->field != 'id')
        $returnvalue = False;
      if($id_field->configuration->datasource != 'testdatasource')
        $returnvalue = False;

      $name_field = $json->fields[1];
      if($name_field->name != 'first_name_test')
        $returnvalue = False;
      if($name_field->configuration->field != 'display_name')
        $returnvalue = False;
      if($name_field->configuration->datasource != 'testdatasource')
        $returnvalue = False;
    }
    else{
      $returnvalue = False;
    }

    if(sizeof($json->outputs)==1){
      $output = reset($json->outputs);
      if($output->type != 'contact_search')
        $returnvalue = False;

      $output_configuration = $output->configuration;

      if($output_configuration->title != 'contact')
        $returnvalue = False;
      if($output_configuration->contact_id_field != 'id_test')
        $returnvalue = False;
    }
    else{
      $returnvalue = False;      
    }

    return $returnvalue;

  }

  public function testExportDataProcessor() {

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

              $file_download_name = 'test.json';
              $mime_type = 'application/json';
              $buffer = json_encode(CRM_Dataprocessor_Utils_Importer::export($data_processor_id), JSON_PRETTY_PRINT);
              if($this->checkContent(json_decode($buffer)))
                $this->assertTrue(true);
              else
                $this->assertFalse(true);



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
