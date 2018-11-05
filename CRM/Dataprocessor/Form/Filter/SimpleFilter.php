<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Filter_SimpleFilter extends CRM_Dataprocessor_Form_Filter_AbstractFilterForm {


  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->addFieldSelect(E::ts('Select Field'));
  }

  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $defaults = $this->getDefaultValuesForField($defaults);
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();

    $configuration = array();
    $configuration = $this->getFieldConfigurationFromSubmittedValues($configuration);
    $this->saveConfiguration($configuration);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}