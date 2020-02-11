<?php

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test exporting a data processor to json.
 *
 * @group headless
 */
class CRM_Dataprocessor_ExportDataProcessorTest extends CRM_Dataprocessor_TestBase {

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

    $id_dataprocessor = $this->createTestDataProcessorFixture();
    $this->createTestDataProcessorSourceFixture();
    $this->createTestDataProcessorField('id');
    $this->createTestDataProcessorField('first_name');

    $params = [];      // Params for setting data source parameters
    $params['data_processor_id'] = $id_dataprocessor;
    $params['type'] = 'contact_search';
    $params['configuration'] = array("title" => "contact", "contact_id_field" => "id_test");
    $id_dataprocessor_output = civicrm_api3('DataProcessorOutput', 'create', $params)['id'] ?? 0;
    $this->assertGreaterThan(0, $id_dataprocessor_output, "Failed calling DataProcessorOutput.create With " . json_encode($params));

    $buffer = json_encode(CRM_Dataprocessor_Utils_Importer::export($id_dataprocessor), JSON_PRETTY_PRINT);
    $this->checkContent(json_decode($buffer));
  }


}
