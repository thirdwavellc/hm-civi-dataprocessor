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
      $filter = CRM_Dataprocessor_BAO_Filter::getValues(array('id' => $this->id));
      $this->assign('filter', $filter[$this->id]);
    }

    $title = E::ts('Data Processor Filter');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
      $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);

      $filterHandlers = CRM_Dataprocessor_BAO_DataProcessor::getAvailableFilterHandlers($this->dataProcessorId);
      $filterHandlersSelect = array(E::ts('- Select -'));
      foreach($filterHandlers as $filterHandler) {
        $filterHandlersSelect[$filterHandler->getName()] = $filterHandler->getTitle();
      }

      $this->add('select', 'type', E::ts('Select Filter'), $filterHandlersSelect, true, array('class' => 'crm-select2 crm-huge40'));

      $this->add('checkbox', 'is_required', E::ts('Is required'));
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

    $filter = CRM_Dataprocessor_BAO_Filter::getValues(array('id' => $this->id));
    if (isset($filter[$this->id]['type'])) {
      $defaults['type'] = $filter[$this->id]['type'];
    }
    if (isset($filter[$this->id]['is_required'])) {
      $defaults['is_required'] = $filter[$this->id]['is_required'];
    }
    if (isset($filter[$this->id]['title'])) {
      $defaults['title'] = $filter[$this->id]['title'];
    }
    if (isset($filter[$this->id]['name'])) {
      $defaults['name'] = $filter[$this->id]['name'];
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Dataprocessor_BAO_Filter::deleteWithId($this->id);
      $session->setStatus(E::ts('Filter removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    if (!empty($values['name'])) {
      $params['name'] = $values['name'];
    } else {
      $params['name'] = CRM_Dataprocessor_BAO_Filter::buildNameFromTitle($values['title']);
    }
    $params['title'] = $values['title'];
    $params['type'] = $values['type'];
    $params['is_required'] = $values['is_required'];
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->id) {
      $params['id'] = $this->id;
    }

    $result = CRM_Dataprocessor_BAO_Filter::add($params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

  /**
   * Function to add validation rules (overrides parent function)
   *
   * @access public
   */
  function addRules() {
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->addFormRule(array(
        'CRM_Dataprocessor_Form_Filter',
        'validateName'
      ));
    }
  }

  /**
   * Function to validate if rule label already exists
   *
   * @param array $fields
   * @return array|bool
   * @access static
   */
  static function validateName($fields) {
    /*
     * if id not empty, edit mode. Check if changed before check if exists
     */
    $id = false;
    if (!empty($fields['id'])) {
      $id = $fields['id'];
    }
    if (empty($fields['name'])) {
      $fields['name'] = CRM_Dataprocessor_BAO_Filter::buildNameFromTitle($fields['title']);
    }
    if (!CRM_Dataprocessor_BAO_Filter::isNameValid($fields['name'], $fields['data_processor_id'], $id)) {
      $errors['name'] = E::ts('There is already a filter with this name');
      return $errors;
    }
    return TRUE;
  }

}