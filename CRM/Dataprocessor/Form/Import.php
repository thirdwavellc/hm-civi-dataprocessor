<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Dataprocessor_Form_Import extends CRM_Core_Form {

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->add('textarea', 'code', E::ts('Import code'), 'rows=30 style="width:100%"', true);
    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Import'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))
    ));
  }

  public function postProcess() {
    $values = $this->exportValues();
    $importCode = json_decode($values['code'], true);
    $importResult = CRM_Dataprocessor_Utils_Importer::import($importCode, '', true);

    CRM_Core_Session::setStatus(E::ts('Imported data processor'), '', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $importResult['new_id']));
    CRM_Utils_System::redirect($redirectUrl);
  }


}