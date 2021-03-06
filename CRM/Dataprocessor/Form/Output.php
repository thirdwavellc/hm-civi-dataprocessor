<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Output extends CRM_Core_Form {

  private $dataProcessorId;

  private $id;

  private $output;

  private $usedTypes = array();

  /**
   * @var Civi\DataProcessor\Output\OutputInterface
   */
  private $outputTypeClass;

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

    if ($this->id) {
      $this->output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $this->id));
      $this->assign('output', $this->output);
      $this->outputTypeClass = $factory->getOutputByName($this->output['type']);
      $this->assign('has_configuration', $this->outputTypeClass->hasConfiguration());
    }
    $this->loadUsedTypes();

    $type = CRM_Utils_Request::retrieve('type', 'String');
    if ($type) {
      $this->outputTypeClass = $factory->getOutputByName($type);
      $this->assign('has_configuration', $this->outputTypeClass->hasConfiguration());
    }

    if (!$this->output) {
      $this->output['data_processor_id'] = $this->dataProcessorId;
    }

    $title = E::ts('Data Processor Output');
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
      $factory = dataprocessor_get_factory();
      $types = $factory->getOutputs();
      $types = array_filter($types, [$this, 'filterOutputType'], ARRAY_FILTER_USE_KEY);
      $types = array(' - select - ')  + $types;
      $this->add('select', 'type', ts('Select output'), $types, true, array(
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ));
      if ($this->outputTypeClass && $this->outputTypeClass->hasConfiguration()) {
        $this->outputTypeClass->buildConfigurationForm($this, $this->output);
        $this->assign('configuration_template', $this->outputTypeClass->getConfigurationTemplateFileName());
      }

      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
    parent::buildQuickForm();
  }

  /**
   * Filter the already used types.
   *
   * @param $type
   *
   * @return bool
   */
  private function filterOutputType($type) {
    if (isset($this->output['type']) && $this->output['type'] == $type) {
      return true;
    }
    if (!in_array($type, $this->usedTypes)) {
      return true;
    }
    return false;
  }

  /**
   * Load all used types
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function loadUsedTypes() {
    $params['data_processor_id'] = $this->dataProcessorId;
    $params['options']['limit'] = 0;
    $values = civicrm_api3('DataProcessorOutput', 'get', $params);
    foreach($values['values'] as $value) {
      if ($this->id && $this->id == $value['id']) {
        continue;
      }
      $this->usedTypes[] = $value['type'];
    }
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['id'] = $this->id;

    if (isset($this->output['type'])) {
      $defaults['type'] = $this->output['type'];
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
      $result = civicrm_api3('DataProcessorOutput', 'get', [
        'sequential' => 1,
        'return' => ["configuration"],
        'id' => $this->id,
      ]);

      civicrm_api3('DataProcessorOutput', 'delete', array('id' => $this->id));
      $session->setStatus(E::ts('Data Processor Output removed'), E::ts('Removed'), 'success');
      CRM_Core_BAO_Navigation::resetNavigation();
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    $params['type'] = $values['type'];
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->id) {
      $params['id'] = $this->id;
    }
    $params['configuration'] = $this->outputTypeClass->processConfiguration($values, $params);

    $result = civicrm_api3('DataProcessorOutput', 'create', $params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}
