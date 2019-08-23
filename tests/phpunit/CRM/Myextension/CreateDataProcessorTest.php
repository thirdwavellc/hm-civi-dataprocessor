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

  public function testCreateDataProcessor() {

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
          $id = $result['id'];

          $this->assertEquals('test', $result['values'][$id]['name']);
          $this->assertEquals('title', $result['values'][$id]['title']);
          $this->assertEquals('Creating a Test Description', $result['values'][$id]['description']);
          $this->assertEquals(1, $result['values'][$id]['is_active']);
        }
        else{
          echo "DataProcessor Failed to setup";
          $this->assertFalse(true);    
        }

  }


}
