<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Upgrader_Version_1_1_0 {

  /**
   * Move the aggregation fields from the data processor table and
   * add them as a new field with the flag is_aggregate is set.
   */
  public static function upgradeAggregationFields() {
    $updatedDataProcessors = array();
    $dao = CRM_Core_DAO::executeQuery("select * from civicrm_data_processor where aggregation is not null;");
    while($dao->fetch()) {
      $aggreagtion = json_decode($dao->aggregation, true);
      if (count($aggreagtion)) {
        foreach($aggreagtion as $aggr_field) {
          list($datasource, $field) = self::splitDataSourceAndFieldName($aggr_field, $dao->id);
          try {
            civicrm_api3('DataProcessorField', 'create', array(
              'data_processor_id' => $dao->id,
              'type' => 'raw',
              'name' => $aggr_field,
              'title' => $aggr_field,
              'configuration' => array(
                'is_aggregate' => 1,
                'datasource' => $datasource,
                'field' => $field,
              )
            ));
            $updatedDataProcessors[] = $dao->title;
          } catch (\Exception $e) {
            CRM_Core_Session::setStatus(E::ts('Error during upgrading data processor: %1', [1=>$dao->title]), '', 'error');
          }
        }
      }
    }
    if (count($updatedDataProcessors)) {
      $message = E::ts('The following data processors have been updated. Please check them if they still work. The aggregate fields have been added to the field section');
      $message .= '<ul>';
      foreach ($updatedDataProcessors as $updatedDataProcessor) {
        $message .= '<li>' . $updatedDataProcessor . '</li>';
      }
      $message .= '</ul>';
      CRM_Core_Session::setStatus($message, E::ts('Update data processor configuration'), 'info', ['expires' => 0]);
    }
  }

  /**
   * @param $fieldName
   * @param $data_processor_id
   * @return array
   */
  private static function splitDataSourceAndFieldName($fieldName, $data_processor_id) {
    $nameParts = explode("_", $fieldName);
    $name = '';
    while ($namePart = array_shift($nameParts)) {
      $name .= $namePart;
      $sql = "SELECT `name` FROM `civicrm_data_processor_source` WHERE `data_processor_id` = %1 AND `name` = %2";
      $sqlParams[1] = [$data_processor_id, 'Integer'];
      $sqlParams[2] = [$name, 'String'];
      $sourceName = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
      if ($sourceName) {
        return array(
          $sourceName,
          implode("_", $nameParts),
        );
      }
      $name .= '_';
    }
    return array();
  }

}
