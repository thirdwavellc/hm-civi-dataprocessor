<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_DataProcessor extends CRM_Core_Form {

  private $dataProcessorId;

  private $currentUrl;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->dataProcessorId = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->currentUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $this->dataProcessorId));
    $this->assign('data_processor_id', $this->dataProcessorId);

    $session = CRM_Core_Session::singleton();
    switch($this->_action) {
      case CRM_Core_Action::DISABLE:
        CRM_Dataprocessor_BAO_DataProcessor::disable($this->dataProcessorId);
        $session->setStatus('Data Processor disabled', 'Disable', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::ENABLE:
        CRM_Dataprocessor_BAO_DataProcessor::enable($this->dataProcessorId);
        $session->setStatus('Data Processor enabled', 'Enable', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::REVERT:
        CRM_Dataprocessor_BAO_DataProcessor::revert($this->dataProcessorId);
        $session->setStatus('Data Processor reverted', 'Revert', 'success');
        CRM_Utils_System::redirect($session->readUserContext());
        break;
      case CRM_Core_Action::EXPORT:
        $this->assign('export', json_encode(CRM_Dataprocessor_BAO_DataProcessor::export($this->dataProcessorId), JSON_PRETTY_PRINT));
        break;
    }

    if ($this->dataProcessorId) {
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
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $this->dataProcessorId));
    foreach($sources as $idx => $source) {
      $sources[$idx]['join_link'] = '';
      if (isset($source['join_type']) && $source['join_type']) {
        $joinClass = $factory->getJoinByName($source['join_type']);
        $sources[$idx]['join_link'] = CRM_Utils_System::url($joinClass->getConfigurationUrl(), array('reset' => 1, 'source_id' => $source['id'], 'data_processor_id' => $this->dataProcessorId));
      }
      $sources[$idx]['configuration_link'] = '';
      $sourceClass = $factory->getDataSourceByName($source['type']);
      if ($sourceClass->getConfigurationUrl()) {
        $sources[$idx]['configuration_link'] = CRM_Utils_System::url($sourceClass->getConfigurationUrl(), array('reset' => 1, 'source_id' => $source['id'], 'data_processor_id' => $this->dataProcessorId));
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
    $fields = array();
    $aggregationFields = CRM_Dataprocessor_BAO_DataProcessor::getAvailableAggregationFields($this->dataProcessorId);
    $aggregationFieldsFormatted = array();
    foreach($aggregationFields as $field) {
      $aggregationFieldsFormatted[$field->fieldSpecification->alias] = $field->dataSource->getSourceTitle()." :: ".$field->fieldSpecification->title;
    }
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
    $aggregation = $dataProcessor[$this->dataProcessorId]['aggregation'];
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
      if (isset($types[$output['type']])) {
        $outputs[$idx]['type_name'] = $types[$output['type']];
      } else {
        $outputs[$idx]['type_name'] = '';
      }
      $outputs[$idx]['configuration_link'] = '';
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
      CRM_Dataprocessor_BAO_DataProcessor::deleteWithId($this->dataProcessorId);
      $session->setStatus(E::ts('Data Processor removed'), E::ts('Removed'), 'success');
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

    $result = CRM_Dataprocessor_BAO_DataProcessor::add($params);
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
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
    if (!empty($dataProcessor) && !empty($this->dataProcessorId)) {
      $defaults['title'] = $dataProcessor[$this->dataProcessorId]['title'];
      if (isset($dataProcessor[$this->dataProcessorId]['name'])) {
        $defaults['name'] = $dataProcessor[$this->dataProcessorId]['name'];
      }
      if (isset($dataProcessor[$this->dataProcessorId]['description'])) {
        $defaults['description'] = $dataProcessor[$this->dataProcessorId]['description'];
      } else {
        $defaults['description'] = '';
      }
      $defaults['is_active'] = $dataProcessor[$this->dataProcessorId]['is_active'];
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
      $fields['name'] = CRM_Dataprocessor_BAO_DataProcessor::buildNameFromTitle($fields['title']);
    }
    if (!CRM_Dataprocessor_BAO_DataProcessor::isNameValid($fields['name'], $id)) {
      $errors['name'] = E::ts('There is already a data processor with this name');
      return $errors;
    }
    return TRUE;
  }

}
