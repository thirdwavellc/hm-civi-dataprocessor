<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_AggregateField extends CRM_Core_Form {

  private $dataProcessorId;

  private $dataProcessor;

  private $id;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->dataProcessorId = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $this->dataProcessorId));
    $this->dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($this->dataProcessor, true);
    $this->assign('data_processor_id', $this->dataProcessorId);

    $title = E::ts('Data Processor Field');
    CRM_Utils_System::setTitle($title);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    $this->add('hidden', 'alias');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $aggregationFieldsFormatted = array();
      foreach($this->dataProcessorClass->getDataSources() as $dataSource) {
        foreach($dataSource->getAvailableAggregationFields() as $field) {
          $aggregationFieldsFormatted[$field->fieldSpecification->alias] = $field->dataSource->getSourceTitle()." :: ".$field->fieldSpecification->title;
        }
      }

      $this->add('select', 'field', E::ts('Select Field'), $aggregationFieldsFormatted, true, array(
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ));
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Add'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['id'] = $this->dataProcessorId;
    $defaults['alias'] =CRM_Utils_Request::retrieve('alias', 'String');
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
      $values = $this->exportValues();
      $aggregation = $this->dataProcessor['aggregation'];
      $this->dataProcessor['aggregation'] = array();
      foreach($aggregation as $alias) {
        if ($alias != $values['alias']) {
          $this->dataProcessor['aggregation'][] = $alias;
        }
      }
      $result = civicrm_api3('DataProcessor', 'create', $this->dataProcessor);

      $session->setStatus(E::ts('Field removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    $aggregation = $this->dataProcessor['aggregation'];
    if (!in_array($values['field'], $aggregation)) {
      $aggregation[] = $values['field'];
    }
    $this->dataProcessor['aggregation'] = $aggregation;
    $result = civicrm_api3('DataProcessor', 'create', $this->dataProcessor);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}