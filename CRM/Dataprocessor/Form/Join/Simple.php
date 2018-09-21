<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Join_Simple extends CRM_Core_Form {

  private $dataProcessorId;

  private $source_id;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->source_id = CRM_Utils_Request::retrieve('source_id', 'Integer', CRM_Core_DAO::$_nullObject, TRUE);
    $this->assign('source_id', $this->source_id);

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->source_id));
    $this->assign('source', $source[$this->source_id]);

    $title = E::ts('Data Processor Source Join Conifuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'source_id');

    $fields = array(' - select - ') + $this->buildFieldList();

    $this->add('select', 'left_field', ts('Select field'), $fields, true, array('class' => 'crm-select2'));
    $this->add('select', 'right_field', ts('Select field'), $fields, true, array('class' => 'crm-select2'));

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
    $defaults['source_id'] = $this->source_id;

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->source_id));
    if (isset($source[$this->source_id]['join_configuration']['left_prefix'])) {
      $defaults['left_field'] = $source[$this->source_id]['join_configuration']['left_prefix'].".".$source[$this->source_id]['join_configuration']['left_field'];
    }
    if (isset($source[$this->source_id]['join_configuration']['right_prefix'])) {
      $defaults['right_field'] = $source[$this->source_id]['join_configuration']['right_prefix'].".".$source[$this->source_id]['join_configuration']['right_field'];
    }

    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();

    $values = $this->exportValues();
    list($left_prefix, $left_field) = explode(".",$values['left_field'], 2);
    list($right_prefix, $right_field) = explode(".",$values['right_field'], 2);

    $params['join_configuration'] = array(
      'left_prefix' => $left_prefix,
      'left_field' => $left_field,
      'right_prefix' => $right_prefix,
      'right_field' => $right_field
    );
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->source_id) {
      $params['id'] = $this->source_id;
    }
    CRM_Dataprocessor_BAO_Source::add($params);
    CRM_Utils_System::redirect($session->readUserContext());
    parent::postProcess();
  }

  function buildFieldList() {
    $factory = dataprocessor_get_factory();
    $fields = array();
    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $this->dataProcessorId));
    foreach($sources as $source) {
      $sourceClass = $factory->getDataSourceByName($source['type']);
      $sourceClass->initialize($source['configuration'], $source['name']);
      $sourceFields = $sourceClass->getAvailableFields()->getFields();
      foreach($sourceFields as $sourceField) {
        $fields[$source['name'].'.'.$sourceField->name] = $source['title'] . '::'.$sourceField->name;
      }

      if ($source['id'] == $this->source_id) {
        break;
      }
    }
    asort($fields);
    return $fields;
  }

}