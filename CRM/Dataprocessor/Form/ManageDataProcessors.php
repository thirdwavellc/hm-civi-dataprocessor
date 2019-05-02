<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Form_ManageDataProcessors extends CRM_Core_Form {

  public function preProcess() {
    parent::preProcess();

    $formValues = $this->getSubmitValues();

    $this->setTitle(E::ts('Manage Data Processors'));

    $apiParams = array();
    if (isset($formValues['title']) && !empty($formValues['title'])) {
      $apiParams['title']['LIKE'] = $formValues['title'];
    }
    if (isset($formValues['description']) && !empty($formValues['description'])) {
      $apiParams['description']['LIKE'] = $formValues['description'];
    }
    if (isset($formValues['is_active']) && $formValues['is_active'] == '0') {
      $apiParams['is_active'] = 0;
    } elseif (isset($formValues['is_active']) && $formValues['is_active'] == '1') {
      $apiParams['is_active'] = 1;
    }
    $apiParams['options']['limit'] = 0;
    $dataProcessors = civicrm_api3('DataProcessor', 'get', $apiParams);
    $dataProcessors = $dataProcessors['values'];

    foreach($dataProcessors as $idx => $dataProcessor) {
      $dataProcessors[$idx]['status_label'] = CRM_Dataprocessor_Status::statusToLabel($dataProcessor['status']);
    }
    $this->assign('data_processors', $dataProcessors);

    $session = CRM_Core_Session::singleton();
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $this);
    $urlPath = CRM_Utils_System::getUrlPath();
    $urlParams = 'force=1';
    if ($qfKey) {
      $urlParams .= "&qfKey=$qfKey";
    }
    $session->replaceUserContext(CRM_Utils_System::url($urlPath, $urlParams));
  }

  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->add('text', 'title', E::ts('Title contains'), array('class' => 'huge'));
    $this->add('text', 'description', E::ts('Description contains'), array('class' => 'huge'));

    $this->addYesNo('is_active', E::ts('Is active'), true);

    $this->addButtons(array(
      array(
        'type' => 'refresh',
        'name' => E::ts('Search'),
        'isDefault' => TRUE,
      ),
    ));
  }

  public function postProcess() {

  }

}