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

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('id', $this->id);

    if ($this->id) {
      $output = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $this->id));
      $this->assign('output', $output[$this->id]);
    }

    $title = E::ts('Data Processor Output');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $factory = dataprocessor_get_factory();
      $types = array(' - select - ')  + $factory->getOutputs();
      $this->add('select', 'type', ts('Select output'), $types, true, array('class' => 'crm-select2'));
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Next'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
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

    $output = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $this->id));
    if (isset($output[$this->id]['type'])) {
      $defaults['type'] = $output[$this->id]['type'];
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Dataprocessor_BAO_Output::deleteWithId($this->id);
      $session->setStatus(E::ts('Data Processor Output removed'), E::ts('Removed'), 'success');
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
    $result = CRM_Dataprocessor_BAO_Output::add($params);
    $factory = dataprocessor_get_factory();
    $outputClass  = $factory->getOutputByName($result['type']);
    if  ($outputClass->getConfigurationUrl()) {
      $redirectUrl = CRM_Utils_System::url($outputClass->getConfigurationUrl(), [
        'reset' => 1,
        'action' =>  'update',
        'id' => $result['id'],
        'data_processor_id' => $this->dataProcessorId
      ]);
    }

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}