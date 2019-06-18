<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_DataProcessor extends CRM_Core_Form {

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
    $this->dataProcessorId = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->dataProcessorId) {
      $this->dataProcessor = civicrm_api3('DataProcessor', 'getsingle', ['id' => $this->dataProcessorId]);
      $this->dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($this->dataProcessor);
    }
    $this->currentUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $this->dataProcessorId));
    $this->assign('data_processor_id', $this->dataProcessorId);

    $session = CRM_Core_Session::singleton();
    switch($this->_action) {
      case CRM_Core_Action::DISABLE:
        CRM_Dataprocessor_BAO_DataProcessor::setDataProcessorToImportingState($this->dataProcessor['name']);
        civicrm_api3('DataProcessor', 'create', array('id' => $this->dataProcessorId, 'is_active' => 0));
        $session->setStatus('Data Processor disabled', 'Disable', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::ENABLE:
        CRM_Dataprocessor_BAO_DataProcessor::setDataProcessorToImportingState($this->dataProcessor['name']);
        civicrm_api3('DataProcessor', 'create', array('id' => $this->dataProcessorId, 'is_active' => 1));
        $session->setStatus('Data Processor enabled', 'Enable', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::REVERT:
        CRM_Dataprocessor_BAO_DataProcessor::setDataProcessorToImportingState($this->dataProcessor['name']);
        CRM_Dataprocessor_BAO_DataProcessor::revert($this->dataProcessorId);
        $session->setStatus('Data Processor reverted', 'Revert', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::EXPORT:
        $this->assign('export', json_encode(CRM_Dataprocessor_Utils_Importer::export($this->dataProcessorId), JSON_PRETTY_PRINT));
        break;
    }

    if ($this->dataProcessorId) {
      $this->assign('dataProcessor', $this->dataProcessor);
      $this->addSources();
      $this->addFields();
      $this->addFilters();
      $this->addAggregateFields();
      $this->addOutputs();
      $dataSourceAddUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/source', 'reset=1&action=add&data_processor_id='.$this->dataProcessorId, TRUE);
      $addAggregateFieldUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/aggregate_field', 'reset=1&action=add&id='.$this->dataProcessorId, TRUE);
      $addFieldUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/field', 'reset=1&action=add&data_processor_id='.$this->dataProcessorId, TRUE);
      $addFilterUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/filter', 'reset=1&action=add&data_processor_id='.$this->dataProcessorId, TRUE);
      $outputAddUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/output', 'reset=1&action=add&data_processor_id='.$this->dataProcessorId, TRUE);
      $this->assign('addDataSourceUrl', $dataSourceAddUrl);
      $this->assign('addAggregateFieldUrl', $addAggregateFieldUrl);
      $this->assign('addFieldUrl', $addFieldUrl);
      $this->assign('addFilterUrl', $addFilterUrl);
      $this->assign('addOutputUrl', $outputAddUrl);
    }
  }

  protected function addSources() {
    $factory = dataprocessor_get_factory();
    $types = $factory->getDataSources();
    $sources = civicrm_api3('DataProcessorSource', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $sources = $sources['values'];
    CRM_Utils_Weight::addOrder($sources, 'CRM_Dataprocessor_DAO_DataProcessorSource', 'id', $this->currentUrl, 'data_processor_id='.$this->dataProcessorId);
    foreach($sources as $idx => $source) {
      if (isset($types[$source['type']])) {
        $sources[$idx]['type_name'] = $types[$source['type']];
      } else {
        $sources[$idx]['type_name'] = '';
      }
    }
    $this->assign('sources', $sources);
  }

  protected function addFields() {
    $fields = civicrm_api3('DataProcessorField', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $fields = $fields['values'];
    CRM_Utils_Weight::addOrder($fields, 'CRM_Dataprocessor_DAO_DataProcessorField', 'id', $this->currentUrl, 'data_processor_id='.$this->dataProcessorId);
    $this->assign('fields', $fields);
  }

  protected function addFilters() {
    $filters = civicrm_api3('DataProcessorFilter', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $filters = $filters['values'];
    CRM_Utils_Weight::addOrder($filters, 'CRM_Dataprocessor_DAO_DataProcessorFilter', 'id', $this->currentUrl, 'data_processor_id='.$this->dataProcessorId);
    $this->assign('filters', $filters);
  }

  protected function addAggregateFields() {
    $aggregationFieldsFormatted = array();
    foreach($this->dataProcessorClass->getDataSources() as $dataSource) {
      foreach($dataSource->getAvailableAggregationFields() as $field) {
        $aggregationFieldsFormatted[$field->fieldSpecification->alias] = $field->dataSource->getSourceTitle()." :: ".$field->fieldSpecification->title;
      }
    }
    $aggregation = $this->dataProcessor['aggregation'];
    $fields = array();
    foreach($aggregation as $alias) {
      $fields[$alias] = $aggregationFieldsFormatted[$alias];
    }
    $this->assign('aggregateFields', $fields);
  }

  protected function addOutputs() {
    $factory = dataprocessor_get_factory();
    $types = $factory->getOutputs();
    $outputs = civicrm_api3('DataProcessorOutput', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    $outputs = $outputs['values'];
    foreach($outputs as $idx => $output) {
      
      $navigation_result = civicrm_api3('Navigation', 'get', [
        'sequential' => 1,
        'return' => ["url"],
        'id' => $output['configuration']['navigation_id'],
      ]);
      $navigation_url = $navigation_result['values'][0]['url'];
      if (isset($types[$output['type']])) {
        $outputs[$idx]['type_name'] = $types[$output['type']];
      } else {
        $outputs[$idx]['type_name'] = '';
      }
      $outputs[$idx]['configuration_link'] = '';
      $outputs[$idx]['navigation_url'] = $navigation_url;
    }
    $this->assign('outputs', $outputs);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
      $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);
      $this->add('text', 'description', E::ts('Description'), array('size' => 100, 'maxlength' => 256));
      $this->add('checkbox', 'is_active', E::ts('Enabled'));
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Next'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::EXPORT) {
      $this->addButtons(array(
        array('type' => 'cancel', 'name' => E::ts('Go back'), 'isDefault' => TRUE),
      ));
    } else {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
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
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $this->setAddDefaults($defaults);
        break;
      case CRM_Core_Action::UPDATE:
        $this->setUpdateDefaults($defaults);
        break;
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    if ($this->_action == CRM_Core_Action::DELETE) {
      $result = civicrm_api3('DataProcessorOutput', 'get', [
        'sequential' => 1,
        'return' => ["configuration"],
        'data_processor_id' => $this->dataProcessorId,
      ]);	
      foreach($result['values'] as $output_navigation){
      	// $output_navigation['configuration']['navigation_id'] outputs the navigation id for each of the output
      	civicrm_api3('Navigation', 'delete', ['id' => $output_navigation['configuration']['navigation_id']]);
	  }

      civicrm_api3('DataProcessor', 'delete', array('id' => $this->dataProcessorId));
      $session->setStatus(E::ts('Data Processor removed'), E::ts('Removed'), 'success');
      CRM_Core_BAO_Navigation::resetNavigation();
      $redirectUrl = $session->popUserContext();
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    $params['name'] = $values['name'];
    $params['title'] = $values['title'];
    $params['description'] = $values['description'];
    $params['is_active'] = !empty($values['is_active']) ? 1 : 0;
    if ($this->dataProcessorId) {
      $params['id'] = $this->dataProcessorId;
    }

    $result = civicrm_api3('DataProcessor', 'create', $params);

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
   * Function to set default values if action is add
   *
   * @param array $defaults
   * @access protected
   */
  protected function setAddDefaults(&$defaults) {
    $defaults['is_active'] = 1;
  }

  /**
   * Function to set default values if action is update
   *
   * @param array $defaults
   * @access protected
   */
  protected function setUpdateDefaults(&$defaults) {
    if (!empty($this->dataProcessor) && !empty($this->dataProcessorId)) {
      $defaults['title'] = $this->dataProcessor['title'];
      if (isset($this->dataProcessor['name'])) {
        $defaults['name'] = $this->dataProcessor['name'];
      }
      if (isset($this->dataProcessor['description'])) {
        $defaults['description'] = $this->dataProcessor['description'];
      } else {
        $defaults['description'] = '';
      }
      $defaults['is_active'] = $this->dataProcessor['is_active'];
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
    if (!empty($fields['id'])) {
      $id = $fields['id'];
    }
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
