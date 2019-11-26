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
class CRM_Dataprocessor_CreateDataProcessorDataSourceTest extends CRM_Dataprocessor_TestBase {
  public function testCreateDataProcessorDataSource() {

    $data_processor_id = $this->createTestDataProcessorFixture();

    $factory = dataprocessor_get_factory();
    if ($factory === NULL) {
      $this->fail("Test cannot complete, unable to obtain data processor factory.");
    }
    $data_sources = $factory->getDataSources();

    foreach ($data_sources as $key => $value) {
      $params = [];

      // Params for setting data source parameters
      $params['data_processor_id'] = $data_processor_id;
      $params['title'] = 'testDataSource';
      $params['type'] = $key;

      $source_id = civicrm_api3('DataProcessorSource', 'create', $params)['id'];

      // Retrieving the data processor source
      $result_datasource = civicrm_api3('DataProcessorSource', 'get', ['id' => $source_id]);
      $this->assertEquals(1, $result_datasource['count']);
      $this->assertArrayHasKey($source_id, $result_datasource['values'],  "Failed to add DataProcessorSource");
      $this->assertEquals('testDataSource', $result_datasource['values'][$source_id]['title']);
      $this->assertEquals($key, $result_datasource['values'][$source_id]['type']);
    }
  }

}
