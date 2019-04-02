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

    $whereClauses = array("1");
    if (isset($formValues['title']) && !empty($formValues['title'])) {
      $whereClauses[] = "`title` LIKE '%". CRM_Utils_Type::escape($formValues['title'], 'String')."%'";
    }
    if (isset($formValues['description']) && !empty($formValues['description'])) {
      $whereClauses[]  = "`description` LIKE '%". CRM_Utils_Type::escape($formValues['description'], 'String')."%'";
    }
    if (isset($formValues['is_active']) && $formValues['is_active'] == '0') {
      $whereClauses[] = "`is_active` = 0";
    } elseif (isset($formValues['is_active']) && $formValues['is_active'] == '1') {
      $whereClauses[] = "`is_active` = 1";
    }

    $whereStatement = implode(" AND ", $whereClauses);
    $sql = "SELECT * FROM civicrm_data_processor WHERE {$whereStatement} ORDER BY is_active, title";
    $dataProcessors = array();
    $dao = CRM_Core_DAO::executeQuery($sql, array(), false, 'CRM_Dataprocessor_DAO_DataProcessor');
    while($dao->fetch()) {
      $row = array();
      CRM_Dataprocessor_DAO_DataProcessor::storeValues($dao, $row);
      switch ($row['status']) {
        case CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_CODE:
          $row['status_label'] = E::ts('In code');
          break;
        case CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN:
          $row['status_label'] = E::ts('Overridden');
          break;
        case CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_DATABASE:
          $row['status_label'] = E::ts('In database');
          break;
      }
      $dataProcessors[] = $row;
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