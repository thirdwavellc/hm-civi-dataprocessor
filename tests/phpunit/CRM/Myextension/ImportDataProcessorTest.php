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
class CRM_Myextension_MyHeadlessTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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

  public function checkDataProcessor($result,$id){
  	$returnvalue = True;
  	$values = $result['values'][$id];
  	if($values['name']!='test')
  		$returnvalue = False;
  	if($values['title']!='Test')
  		$returnvalue = False;
  	if($values['description']!='This is a test Data Processor')
  		$returnvalue = False;
  	if($values['is_active']!=1)
  		$returnvalue = False;

  	return $returnvalue;
  }

  public function checkDataProcessorFields($fields){
  	$keys = array_keys($fields['values']);
  	$returnvalue = True;
  	$field_0 = $fields['values'][$keys[0]];
  	if($field_0['name']!='contact_id')
  		$returnvalue = False;
  	if($field_0['configuration']['field']!='id')
  		$returnvalue = False;
  	if($field_0['configuration']['datasource']!='contact')
  		$returnvalue = False;

  	$field_1 = $fields['values'][$keys[1]];
	if($field_1['name']!='first_name')
  		$returnvalue = False;
  	if($field_1['configuration']['field']!='first_name')
  		$returnvalue = False;
  	if($field_1['configuration']['datasource']!='contact')
  		$returnvalue = False;

  	$field_2 = $fields['values'][$keys[2]];
  	if($field_2['name']!='gender')
  		$returnvalue = False;
  	if($field_2['configuration']['field']!='gender_id')
  		$returnvalue = False;
  	if($field_2['configuration']['datasource']!='contact')
  		$returnvalue = False;

  	return $returnvalue;

  }

  public function checkDataProcessorSource($datasource){
  	$keys = array_keys($datasource['values']);
  	$returnvalue = True;
  	$datasource_check = $datasource['values'][$keys[0]];
  	if($datasource_check['name']!='contact')
  		$returnvalue = False;
  	if($datasource_check['title']!='Contact')
  		$returnvalue = False;
  	if($datasource_check['type']!='contact')
  		$returnvalue = False;
  	if($datasource_check['configuration']['filter']['is_deleted']['op']!='=')
  		$returnvalue = False;
  	if($datasource_check['configuration']['filter']['is_deleted']['value']!=0)
  		$returnvalue = False;

  	return $returnvalue;

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
  		$file = __DIR__.'/test.json';
    	$configuration = file_get_contents($file);	

		$importCode = json_decode($configuration, true);
 	 	$importResult = CRM_Dataprocessor_Utils_Importer::import($importCode, '', true);
 	 	$id = $importResult['original_id'];
 	 	$result = civicrm_api3('DataProcessor', 'get',  array("id"=> $id));
 	 	$fields = civicrm_api3('DataProcessorField', 'get',  array("data_processor_id"=> $id));
 	 	$datasource = civicrm_api3('DataProcessorSource', 'get',  array("data_processor_id"=> $id));
 	 	$outputs = civicrm_api3('DataProcessorOutput', 'get',  array("data_processor_id"=> $id));


 	 	if($this->checkDataProcessor($result,$id))
 	 		$this->assertTrue(true);
 	 	else
 	 		$this->assertFalse(true);

 	 	if($this->checkDataProcessorFields($fields))
 	 		$this->assertTrue(true);
 	 	else
 	 		$this->assertFalse(true);

 	 	if($this->checkDataProcessorSource($datasource))
 	 		$this->assertTrue(true);
 	 	else
 	 		$this->assertFalse(true);

 	 	if($this->checkDataProcessorOutput($outputs))
 	 		$this->assertTrue(true);
 	 	else
 	 		$this->assertFalse(true);

  }

}
