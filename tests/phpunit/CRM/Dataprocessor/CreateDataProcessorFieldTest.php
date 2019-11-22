<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests we can create a 'field' on a data processor.
 *
 * @group headless
 */
class CRM_Dataprocessor_CreateDataProcessorFieldTest  extends CRM_Dataprocessor_TestBase {

  public function testCreateDataProcessorField() {

    $data_processor_id = $this->createTestDataProcessorFixture();
    $id_datafield = $this->createTestDataProcessorField('id');
    $result = $this->dataprocessor_fields['id'];

    $expectations = [
      'title'                    => 'id test field',
      'type'                     => 'raw',
      'data_processor_id'        => $data_processor_id,
      'configuration.field'      => 'id',
      'configuration.datasource' => 'testdatasource',
    ];
    $this->checkNestedArray($expectations, $result, __FUNCTION__);
  }
}

