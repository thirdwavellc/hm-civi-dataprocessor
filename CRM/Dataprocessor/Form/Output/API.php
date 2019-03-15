<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Output_API extends CRM_Dataprocessor_Form_Output_AbstractOutputForm {


  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('select','permission', E::ts('Permission'), CRM_Core_Permission::basicPermissions(), true);
    $this->add('text', 'api_entity', E::ts('API Entity'), true);
    $this->add('text', 'api_action', E::ts('API Action Name'), true);
    $this->add('text', 'api_count_action', E::ts('API GetCount Action Name'), true);
  }

  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if ($this->output) {
      $defaults['permission'] = $this->output['permission'];
      $defaults['api_entity'] = $this->output['api_entity'];
      $defaults['api_action'] = $this->output['api_action'];
      $defaults['api_count_action'] = $this->output['api_count_action'];
    } else {
      $defaults['permission'] = 'access CiviCRM backend and API';
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();

    $values = $this->exportValues();
    $params['id'] = $this->id;
    $params['permission'] = $values['permission'];
    $params['api_entity'] = $values['api_entity'];
    $params['api_action'] = $values['api_action'];
    $params['api_count_action'] = $values['api_count_action'];
    CRM_Dataprocessor_BAO_Output::add($params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}