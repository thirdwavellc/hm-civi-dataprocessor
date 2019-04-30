<?php
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_BAO_DataProcessorSource extends CRM_Dataprocessor_DAO_DataProcessorSource {

  public static function checkName($title, $data_processor_id, $id=null,$name=null) {
    if (!$name) {
      $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($title));
    }

    $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($name));
    $name_part = $name;

    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_source` WHERE `name` = %1 AND `data_processor_id` = %2";
    $sqlParams[1] = array($name, 'String');
    $sqlParams[2] = array($data_processor_id, 'String');
    if (isset($id)) {
      $sql .= " AND `id` != %3";
      $sqlParams[3] = array($id, 'Integer');
    }

    $i = 1;
    while(CRM_Core_DAO::singleValueQuery($sql, $sqlParams) > 0) {
      $i++;
      $name = $name_part .'_'.$i;
      $sqlParams[1] = array($name, 'String');
    }
    return $name;
  }

  /**
   * Returns whether the name is valid or not
   *
   * @param string $name
   * @param int $data_procssor_id,
   * @param int $id optional
   * @return bool
   * @static
   */
  public static function isNameValid($name, $data_procssor_id, $id=null) {
    $sql = "SELECT COUNT(*) FROM `civicrm_data_processor_source` WHERE `name` = %1 AND `data_processor_id` = %2";
    $params[1] = array($name, 'String');
    $params[2] = array($data_procssor_id, 'Integer');
    if ($id) {
      $sql .= " AND `id` != %3";
      $params[3] = array($id, 'Integer');
    }
    $count = CRM_Core_DAO::singleValueQuery($sql, $params);
    return ($count > 0) ? false : true;
  }

  /**
   * Function to delete a Data Processor Source with id
   *
   * @param int $id
   * @throws Exception when $id is empty
   * @access public
   * @static
   */
  public static function deleteWithDataProcessorId($id) {
    if (empty($id)) {
      throw new Exception('id can not be empty when attempting to delete a data processor filter');
    }

    $field = new CRM_Dataprocessor_DAO_DataProcessorSource();
    $field->data_processor_id = $id;
    $field->find(FALSE);
    while ($field->fetch()) {
      civicrm_api3('DataProcessorSource', 'delete', array('id' => $field->id));
    }
  }

  /**
   * @param $source
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public static function getSourceClass($source, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor) {
    $factory = dataprocessor_get_factory();
    $sourceClass = $factory->getDataSourceByName($source['type']);
    $sourceClass->setSourceName($source['name']);
    $sourceClass->setSourceTitle($source['title']);
    $sourceClass->setConfiguration($source['configuration']);
    $sourceClass->setDataProcessor($dataProcessor);
    $join = null;
    if ($source['join_type']) {
      $join = $factory->getJoinByName($source['join_type']);
      $join->setConfiguration($source['join_configuration']);
      $join->setDataProcessor($dataProcessor);
    }
    $dataProcessor->addDataSource($sourceClass, $join);
    if ($join) {
      $join->initialize();
      $sourceClass->setJoin($join);
    }
    return $sourceClass;
  }
}
