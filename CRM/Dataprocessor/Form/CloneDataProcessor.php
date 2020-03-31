<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_CloneDataProcessor extends CRM_Core_Form {

  private $dataProcessorId;

  private $dataProcessor;

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  private $dataProcessorClass;

  private $currentUrl;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->dataProcessorId = CRM_Utils_Request::retrieve('id', 'Integer', $this, true);
    $this->dataProcessor = civicrm_api3('DataProcessor', 'getsingle', ['id' => $this->dataProcessorId]);
    $this->dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($this->dataProcessor, true);
    $this->currentUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $this->dataProcessorId));
    $this->assign('data_processor_id', $this->dataProcessorId);
    $this->assign('dataProcessor', $this->dataProcessor);
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('Clone data processor: %1', [1=>$this->dataProcessor['title']]));
    $this->add('hidden', 'id');

    $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
    $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);
    $this->add('text', 'description', E::ts('Description'), array('size' => 100, 'maxlength' => 256));
    $this->add('checkbox', 'is_active', E::ts('Enabled'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))
    ));

    parent::buildQuickForm();
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['id'] = $this->dataProcessorId;
    if (!empty($this->dataProcessor) && !empty($this->dataProcessorId)) {
      $defaults['title'] = E::ts('Clone of %1', [1=>$this->dataProcessor['title']]);
      if (isset($this->dataProcessor['description'])) {
        $defaults['description'] = $this->dataProcessor['description'];
      } else {
        $defaults['description'] = '';
      }
      $defaults['is_active'] = $this->dataProcessor['is_active'];
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $values = $this->exportValues();
    $params = clone $this->dataProcessor;
    unset($params['id']);
    $params['name'] = $values['name'];
    $params['title'] = $values['title'];
    $params['description'] = $values['description'];
    $params['is_active'] = !empty($values['is_active']) ? 1 : 0;

    $result = civicrm_api3('DataProcessor', 'create', $params);
    $newId = $result['id'];

    $sources = civicrm_api3('DataProcessorSource', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $dataProcessor['data_sources'] = array();
    foreach($sources['values'] as $i => $datasource) {
      unset($datasource['id']);
      unset($datasource['data_processor_id']);
      $datasource['data_processor_id'] = $newId;
      civicrm_api3('DataProcessorSource', 'create', $datasource);
    }
    $filters = civicrm_api3('DataProcessorFilter', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $dataProcessor['filters']  = array();
    foreach($filters['values'] as $i => $filter) {
      unset($filter['id']);
      unset($filter['data_processor_id']);
      $filter['data_processor_id'] = $newId;
      civicrm_api3('DataProcessorFilter', 'create', $filter);
    }
    $fields = civicrm_api3('DataProcessorField', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $dataProcessor['fields'] = array();
    foreach($fields['values'] as $i => $field) {
      unset($field['id']);
      unset($field['data_processor_id']);
      $field['data_processor_id'] = $newId;
      civicrm_api3('DataProcessorField', 'create', $field);
    }
    $outputs = $outputs = civicrm_api3('DataProcessorOutput', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $dataProcessor['outputs'] = array();
    foreach($outputs['values'] as $i => $output) {
      unset($output['id']);
      unset($output['data_processor_id']);
      $output['data_processor_id'] = $newId;
      civicrm_api3('DataProcessorOutput', 'create', $output);
    }

    $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $result['id']));
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Function to add validation rules (overrides parent function)
   *
   * @access public
   */
  function addRules() {
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->addFormRule(array(
        'CRM_Dataprocessor_Form_DataProcessor',
        'validateName'
      ));
    }
  }

  /**
   * Function to validate if rule label already exists
   *
   * @param array $fields
   * @return array|bool
   * @access static
   */
  static function validateName($fields) {
    /*
     * if id not empty, edit mode. Check if changed before check if exists
     */
    $id = false;
    if (empty($fields['name'])) {
      $fields['name'] = CRM_Dataprocessor_BAO_DataProcessor::checkName($fields['title'], $id);
    }
    if (!CRM_Dataprocessor_BAO_DataProcessor::isNameValid($fields['name'], $id)) {
      $errors['name'] = E::ts('There is already a data processor with this name');
      return $errors;
    }
    return TRUE;
  }

}
