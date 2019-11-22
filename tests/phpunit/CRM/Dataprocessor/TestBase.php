<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This is a base class for code shared between the data processor tests.
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
class CRM_Dataprocessor_TestBase extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * @var array keyed by ID
   */
  protected $data_processors = [];
  /**
   * @var array keyed by ID
   */
  protected $sources = [];
  /**
   * @var array
   * keyed by field name, not it's ID (for ease of access)
   */
  protected $dataprocessor_fields = [];

  /**
   * @var array
   */
  protected $field_fixtures = [
    'id' => [
      'title' => 'id test field',
      'type'  => 'raw',
      'configuration' => [
        'field'      => 'id',
        'datasource' => 'testdatasource',
      ]
    ],
    'first_name' => [
      'title' => 'first name test',
      'type'  => 'raw',
      'configuration' => [
        'field'      => 'first_name',
        'datasource' => 'testdatasource',
      ]
    ],
  ];

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply(TRUE);
  }

  public function setUp() {
    $c = CRM_Core_DAO::singleValueQuery("SELECT count(*) FROM civicrm_data_processor");
    $this->assertEquals(0, $c, "setup: already $c data processors defined.");
    parent::setUp();
  }

  public function tearDown() {

    foreach (array_keys($this->data_processors) as $id) {
      civicrm_api3('DataProcessor', 'delete', ['id' => $id]);
    }
    parent::tearDown();
  }

  /**
   * Create a data processor for our tests.
   *
   * @return int ID of new data processor.
   */
  public function createTestDataProcessorFixture() {

    $params['name'] = "test";
    $params['title'] = "title";
    $params['description'] = 'Creating a Test Description';
    $params['is_active'] = 1;

    // Creating a DataProcessor
    $data_processor_id = civicrm_api3('DataProcessor', 'create', $params)['id'];
    $this->assertGreaterThan(0, $data_processor_id, "Failed calling DataProcessor.create with " . json_encode($params));

    // Reload the data processor
    $this->data_processors[$data_processor_id] = civicrm_api3('DataProcessor', 'getsingle', ['id' => $data_processor_id]);

    return $data_processor_id;
  }
  /**
   * Create a data processor source for our tests.
   *
   * @return int ID of new data processor source.
   */
  public function createTestDataProcessorSourceFixture() {

    $data_processor_id = array_keys($this->data_processors)[0] ?? 0;
    $this->assertGreaterThan(0, $data_processor_id, "No data processor; call createTestDataProcessorFixture before " . __FUNCTION__);
    $params = [
      'data_processor_id' => $data_processor_id,
      'title'             => 'testDataSource',
      'type'              => 'contact',
    ];

    // Creating a DataProcessor Source
    $id_datasource = civicrm_api3('DataProcessorSource', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_datasource, "Failed creating DataProcessorSource");

    // I'm not sure we need this much checking...
    $result_datasource = civicrm_api3('DataProcessorSource', 'get', ['id' => $id_datasource]);
    $this->assertEquals(1, $result_datasource['count']);
    $this->assertArrayHasKey($id_datasource, $result_datasource['values'],  "Failed to add DataProcessorSource");

    $result_datasource_value = $result_datasource['values'][$id_datasource];
    foreach ($params as $k => $v) {
      $this->assertArrayHasKey($k, $result_datasource_value);
      $this->assertEquals($v, $result_datasource_value[$k]);
    }

    // All good, store and return the ID.
    $this->sources[$id_datasource] = $result_datasource_value;
    return $id_datasource;
  }
  /**
   *
   * Create an field from our fixtures.
   *
   * @param string $name The code name of the fixture - see $this->field_fixtures
   * @return int field ID.
   */
  public function createTestDataProcessorField($name) {

    $data_processor_id = array_keys($this->data_processors)[0] ?? 0;
    $this->assertGreaterThan(0, $data_processor_id, "No data processor; call createTestDataProcessorFixture before " . __FUNCTION__);

    $this->assertArrayHasKey($name, $this->field_fixtures, "Test code error: there is no '$name' field fixture defined");

    // Params for setting field parameters
    $params = $this->field_fixtures[$name];
    // Add in the procesor ID
    $params['data_processor_id'] = $data_processor_id;

    // Create
    $id_datafield = civicrm_api3('DataProcessorField', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_datafield, "Failed to create DataProcessorField");

    // Reload
    $result_field = civicrm_api3('DataProcessorField', 'get', ['id' => $id_datafield]);
    $this->assertEquals(1, $result_field['count'], "Failed to reload DataProcessorField");

    // Store it for access if needed.
    $this->dataprocessor_fields[$name] = $result_field['values'][$id_datafield];

    return $id_datafield;
  }
  /**
   * Helper function.
   *
   * @param array $expectations keys are dot-nested keys, values are expected values.
   * @param array $source
   * @param string $text To give context to any assertion errors.
   */
  public function checkNestedArray($expectations, $source, $text) {
    foreach ($expectations as $key => $expected) {

      // Dig down keys.like.this
      $parts = explode('.', $key);
      $_ = $source;
      while ($parts) {
        $subkey = array_shift($parts);
        $this->assertArrayHasKey($subkey, $_, $text . " (key: $key)");
        $_ = $_[$subkey];
      }

      // Finally, check the value
      $this->assertEquals($expected, $_, $text . " (key: $key)");

    }
  }

}


