<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Filter extends CRM_Core_Form {

  private $dataProcessorId;

  private $id;

  private $filter;

  /**
   * @var Civi\DataProcessor\FilterHandler\AbstractFilterHandler
   */
  private $filterTypeClass;

  private $snippet;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->snippet = CRM_Utils_Request::retrieve('snippet', 'String');
    if ($this->snippet) {
      $this->assign('suppressForm', TRUE);
      $this->controller->_generateQFKey = FALSE;
    }

    $factory = dataprocessor_get_factory();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('id', $this->id);

    $this->assign('has_configuration', false);
    if ($this->id) {
      $this->filter = civicrm_api3('DataProcessorFilter', 'getsingle', array('id' => $this->id));
      $this->assign('filter', $this->filter);
      $this->filterTypeClass = $factory->getFilterByName($this->filter['type']);
      $this->assign('has_configuration', $this->filterTypeClass->hasConfiguration());
    }

    $type = CRM_Utils_Request::retrieve('type', 'String');
    if ($type) {
      $this->filterTypeClass = $factory->getFilterByName($type);
      $this->assign('has_configuration', $this->filterTypeClass->hasConfiguration());
    }

    if (!$this->filter) {
      $this->filter['data_processor_id'] = $this->dataProcessorId;
    }

    $title = E::ts('Data Processor Filter');
    CRM_Utils_System::setTitle($title);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
      $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
      $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);

      $factory = dataprocessor_get_factory();
      $this->add('select', 'type', E::ts('Select Filter'), $factory->getFilters(), true, array('style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),));
      $this->add('checkbox', 'is_required', E::ts('Is required'));

      if ($this->filterTypeClass && $this->filterTypeClass->hasConfiguration()) {
        $this->filterTypeClass->buildConfigurationForm($this, $this->filter);
        $this->assign('configuration_template', $this->filterTypeClass->getConfigurationTemplateFileName());
      }

      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['id'] = $this->id;

    if (isset($this->filter['type'])) {
      $defaults['type'] = $this->filter['type'];
    } else {
      $factory = dataprocessor_get_factory();
      $filter_types = array_keys($factory->getFilters());
      $defaults['type'] = reset($filter_types);
    }
    if (isset($this->filter['is_required'])) {
      $defaults['is_required'] = $this->filter['is_required'];
    }
    if (isset($this->filter['title'])) {
      $defaults['title'] = $this->filter['title'];
    }
    if (isset($this->filter['name'])) {
      $defaults['name'] = $this->filter['name'];
    }
    return $defaults;
  }

  /**
   * Function that can be defined in Form to override or.
   * perform specific action on cancel action
   */
  public function cancelAction() {
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $this->dataProcessorId));
    CRM_Utils_System::redirect($redirectUrl);
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $this->dataProcessorId));
    if ($this->_action == CRM_Core_Action::DELETE) {
      civicrm_api3('DataProcessorFilter', 'delete', array('id' => $this->id));
      $session->setStatus(E::ts('Filter removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    if (!empty($values['name'])) {
      $params['name'] = $values['name'];
    }
    $params['title'] = $values['title'];
    $params['type'] = $values['type'];
    $params['is_required'] = isset($values['is_required']) && $values['is_required'] ? 1 : 0;
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->id) {
      $params['id'] = $this->id;
    }

    if ($this->filterTypeClass && $this->filterTypeClass->hasConfiguration()) {
      $params['configuration'] = $this->filterTypeClass->processConfiguration($values);
    }

    civicrm_api3('DataProcessorFilter', 'create', $params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}