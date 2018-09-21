<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_AggregateField extends CRM_Core_Form {

  private $dataProcessorId;

  private $id;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $title = E::ts('Data Processor Field');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    $this->add('hidden', 'alias');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $fields = CRM_Dataprocessor_BAO_DataProcessor::getAvailableAggregationFields($this->dataProcessorId);
      $fieldSelect = array(E::ts('- Select -'));
      foreach($fields as $field) {
        $fieldSelect[$field->fieldSpecification->alias] = $field->dataSource->getSourceTitle()." :: ".$field->fieldSpecification->title;
      }

      $this->add('select', 'field', E::ts('Select Field'), $fieldSelect, true, array('class' => 'crm-select2 crm-huge40'));
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

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();
    if ($this->_action == CRM_Core_Action::DELETE) {
      $values = $this->exportValues();
      $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
      $aggregation = $dataProcessor[$this->dataProcessorId]['aggregation'];
      $dataProcessor[$this->dataProcessorId]['aggregation'] = array();
      foreach($aggregation as $alias) {
        if ($alias != $values['alias']) {
          $dataProcessor[$this->dataProcessorId]['aggregation'][] = $alias;
        }
      }
      $result = CRM_Dataprocessor_BAO_DataProcessor::add($dataProcessor[$this->dataProcessorId]);

      $session->setStatus(E::ts('Field removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
    $aggregation = $dataProcessor[$this->dataProcessorId]['aggregation'];
    if (!in_array($values['field'], $aggregation)) {
      $aggregation[] = $values['field'];
    }
    $dataProcessor[$this->dataProcessorId]['aggregation'] = $aggregation;
    $result = CRM_Dataprocessor_BAO_DataProcessor::add($dataProcessor[$this->dataProcessorId]);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}