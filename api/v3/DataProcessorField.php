<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * DataProcessorField.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_data_processor_field_create_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorField::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
    if ($fieldname != 'id' && isset($field['required']) && $field['required']) {
      $spec[$fieldname]['api.required'] = true;
    }
  }
}

/**
 * DataProcessorField.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_field_create($params) {
  if (!isset($params['weight']) && !isset($params['id'])) {
    $params['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Dataprocessor_DAO_DataProcessorField', array('data_processor_id' => $params['data_processor_id']));
  }
  $id = null;
  if (isset($params['id'])) {
    $id = $params['id'];
  }
  $name = null;
  if (isset($params['name'])) {
    $name = $params['name'];
  }
  $params['name'] = CRM_Dataprocessor_BAO_DataProcessorField::checkName($params['title'], $params['data_processor_id'], $id, $name);
  $return = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  $dataProcessorId = civicrm_api3('DataProcessorField', 'getvalue', array('id' => $return['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  return $return;
}

/**
 * DataProcessorField.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_field_delete($params) {
  $dataProcessorId = civicrm_api3('DataProcessorField', 'getvalue', array('id' => $params['id'], 'return' => 'data_processor_id'));
  CRM_Dataprocessor_BAO_DataProcessor::updateAndChekStatus($dataProcessorId);
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * DataProcessorField.get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_data_processor_field_get_spec(&$spec) {
  $fields = CRM_Dataprocessor_DAO_DataProcessorField::fields();
  foreach($fields as $fieldname => $field) {
    $spec[$fieldname] = $field;
  }
}

/**
 * DataProcessorField.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_data_processor_field_get($params) {
  if (!isset($params['options']) || !isset($params['options']['sort'])) {
    $params['options']['sort'] = 'weight ASC';
  }
  $return = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  foreach($return['values'] as $id => $value) {
    if (isset($value['configuration'])) {
      $return['values'][$id]['configuration'] = json_decode($value['configuration'], TRUE);
    } else {
      $return['values'][$id]['configuration'] = array();
    }
  }
  return $return;
}

/**
 * DataProcessorField.check_name API specification
 *
 * @param $params
 */
function _civicrm_api3_data_processor_field_check_name_spec($params) {
  $params['id'] = array(
    'name' => 'id',
    'title' => E::ts('ID'),
  );
  $params['title'] = array(
    'name' => 'title',
    'title' => E::ts('Title'),
    'api.required' => true,
  );
  $params['data_processor_id'] = array(
    'name' => 'data_processor_id',
    'title' => E::ts('Data Processor Id'),
    'api.required' => true,
  );
  $params['name'] = array(
    'name' => 'name',
    'title' => E::ts('Name'),
  );
}

/**
 * DataProcessorField.check_name API
 *
 * @param $params
 */
function civicrm_api3_data_processor_field_check_name($params) {
  $name = CRM_Dataprocessor_BAO_DataProcessorField::checkName($params['title'], $params['data_processor_id'], $params['id'], $params['name']);
  return array(
    'name' => $name,
  );
}

function civicrm_api3_data_processor_field_correct_field_configuration($params) {
  $return = array();
  $fields = CRM_COre_DAO::executeQuery("SELECT * FROM civicrm_data_processor_field");
  $dataProcessors = array();
  while($fields->fetch()) {
    if (!isset($dataProcessors[$fields->data_processor_id])) {
      $dataProcessors[$fields->data_processor_id] = civicrm_api3('DataProcessor', 'getsingle', array('id' => $fields->data_processor_id));
    }
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessors[$fields->data_processor_id]);
    foreach($dataProcessor->getDataSources() as $dataSource) {
      foreach($dataSource->getAvailableFields()->getFields() as $dataSourceField) {
        // Check fields for type raw_... or option_label_.... or file_...
        // and change their type to raw, option_label or file
        // and set the right configuration.
        $newType = false;
        $configuration = false;
        if ($fields->type == 'raw_'.$dataSourceField->alias) {
          $configuration = array('field' => $dataSourceField->name, 'datasource' => $dataSource->getSourceName());
          $newType = 'raw';
        } elseif ($fields->type == 'option_label_'.$dataSourceField->alias) {
          $configuration = array('field' => $dataSourceField->name, 'datasource' => $dataSource->getSourceName());
          $newType = 'option_label';
        } elseif ($fields->type == 'file_field_'.$dataSourceField->alias) {
          $configuration = array('field' => $dataSourceField->name, 'datasource' => $dataSource->getSourceName());
          $newType = 'file_field';
        }

        if ($newType) {
          CRM_Core_DAO::executeQuery("UPDATE civicrm_data_processor_field SET `type` = 'raw', `configuration` = %1 WHERE id = %2", array(
            1 => array(json_encode($configuration), 'String'),
            2 => array($fields->id, 'Integer')
          ));
          $return[$fields->id] = array(
            'original_type' => $fields->type,
            'type' => $newType,
            'configuration' => $configuration,
          );
        }
      }
    }
  }
  return civicrm_api3_create_success($return);
}
