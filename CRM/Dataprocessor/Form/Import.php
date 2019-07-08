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
    $this->add('file', 'uploadFile', ts('Import Data File'), 'size=30 maxlength=255', TRUE);
    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Import'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))
    ));
  }

  public function postProcess() {
    $values = $this->getSubmitValues(True);
    // Check for JSON extension required
    $filetype = $values['uploadFile']['type'];
    if($filetype != 'application/json'){
    CRM_Core_Session::setStatus(E::ts('Imported file should be a json file'), '', 'error');
    $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/import', array('reset' => 1, 'action' => 'add'));
    
    parent::preProcess();
    CRM_Utils_System::redirect($redirectUrl);
    
    
    }
    else{

      $configuration = file_get_contents($values['uploadFile']['tmp_name']);

      $importCode = json_decode($configuration, true);
      $importResult = CRM_Dataprocessor_Utils_Importer::import($importCode, '', true);

      CRM_Core_Session::setStatus(E::ts('Imported data processor'), '', 'success');

      $redirectUrl = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('reset' => 1, 'action' => 'update', 'id' => $importResult['new_id']));
      CRM_Utils_System::redirect($redirectUrl);
    }
  }


}