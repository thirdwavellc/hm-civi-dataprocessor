<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Simple test that we can create a data processor.
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
class CRM_Dataprocessor_CreateDataProcessorTest extends CRM_Dataprocessor_TestBase {

  public function testCreateDataProcessor() {
    $id_dataprocessor = $this->createTestDataProcessorFixture();
    $processor = $this->data_processors[$id_dataprocessor];
    $this->assertEquals('title', $processor['title']);
    $this->assertEquals('Creating a Test Description', $processor['description']);
    $this->assertEquals(1, $processor['is_active']);
    $this->assertEquals('test', $processor['name']);
  }

}
