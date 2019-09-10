<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * This class could be used as a base for other form classes.
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
abstract class CRM_Dataprocessor_Form_Output_AbstractOutputForm extends CRM_Core_Form {

  protected $dataProcessorId;

  protected $id;

  protected $output;

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
      $this->output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $this->id));
      $this->assign('output', $this->output);
    }

    $title = E::ts('Data Processor  Output  Configuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $defaults['data_processor_id'] = $this->dataProcessorId;
    if ($this->id) {
      $defaults['id'] = $this->id;
    }
    return $defaults;
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel')))
    );
    parent::buildQuickForm();
  }

}
