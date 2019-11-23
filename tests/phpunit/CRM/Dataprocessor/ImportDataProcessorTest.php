<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test that we can import a data processor.
 *
 * @group headless
 */
class CRM_Dataprocessor_ImportDataProcessorTest extends CRM_Dataprocessor_TestBase {

  public function checkDataProcessorFields($fields){

  	$keys = array_keys($fields['values']);

  	$field = $fields['values'][$keys[0]];
    $expectations = [
      'name'                     => 'contact_id',
      'configuration.field'      => 'id',
      'configuration.datasource' => 'contact',
    ];
    $this->checkNestedArray($expectations, $field, "While checking the id field.");

  	$field = $fields['values'][$keys[1]];
    $expectations = [
      'name'                     => 'first_name',
      'configuration.field'      => 'first_name',
      'configuration.datasource' => 'contact',
    ];
    $this->checkNestedArray($expectations, $field, "While checking the first name field.");

  	$field = $fields['values'][$keys[2]];
    $expectations = [
      'name'                     => 'gender',
      'configuration.field'      => 'gender_id',
      'configuration.datasource' => 'contact',
    ];
    $this->checkNestedArray($expectations, $field, "While checking the first name field.");

  }

  public function checkDataProcessorSource($datasource){
  	$keys = array_keys($datasource['values']);
  	$datasource_check = $datasource['values'][$keys[0]];

    $expectations = [
      'name' => 'contact',
      'title' => 'Contact',
      'type' => 'contact',
      'configuration.filter.is_deleted.op' => '=',
      'configuration.filter.is_deleted.value' => 0,
    ];
    $this->checkNestedArray($expectations, $datasource_check, "While checking the source.");
  }

  public function checkDataProcessorOutput($output){
  	$keys = array_keys($output['values']);
  	$returnvalue = True;
  	$output_check = $output['values'][$keys[0]];
  	if($output_check['type']!='contact_search')
  		$returnvalue = False;
  	if($output_check['configuration']['title']!='Test')
  		$returnvalue = False;
  	if($output_check['configuration']['contact_id_field']!='contact_id')
  		$returnvalue = False;

  	return $returnvalue;

  }

  public function testImportDataProcessor() {
    $file = __DIR__ . '/test-dataprocessor-import-fixture.json';
    $configuration = file_get_contents($file);

    $importCode = json_decode($configuration, TRUE);
    $importResult = CRM_Dataprocessor_Utils_Importer::import($importCode, '', true);

    $id = $importResult['new_id'];
    $result = civicrm_api3('DataProcessor', 'get',  array("id"=> $id));

    $fields = civicrm_api3('DataProcessorField', 'get',  array("data_processor_id"=> $id));
    $datasource = civicrm_api3('DataProcessorSource', 'get',  array("data_processor_id"=> $id));
    $outputs = civicrm_api3('DataProcessorOutput', 'get',  array("data_processor_id"=> $id));

    // Check the data processor
    $expectations = [
      'name'        => 'test',
      'title'       => 'Test',
      'description' => 'This is a test Data Processor',
      'is_active'   => 1,
    ];
    $this->checkNestedArray($expectations, $result['values'][$id],  "While checking the imported data processor:");

    // Check the fields.
    $this->checkDataProcessorFields($fields);
    $this->checkDataProcessorSource($datasource);
    $this->checkDataProcessorOutput($outputs);
  }

}
