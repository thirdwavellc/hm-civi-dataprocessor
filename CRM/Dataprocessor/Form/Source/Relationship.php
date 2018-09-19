<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Source_Relationship extends CRM_Core_Form {

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

    $title = E::ts('Data Processor Source Conifuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'source_id');

    $relationship_types = array(' - select - ') + $this->buildRelationshipTypeSelectList();

    $relationship_type_select = $this->add('select', 'relationship_type_id', ts('Relationship Type'), $relationship_types, true, array('class' => 'crm-select2', 'multiple' => 'multiple'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))
    ));
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['source_id'] = $this->source_id;

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->source_id));
    if (isset($source[$this->source_id]['configuration']['relationship_type_id'])) {
      $defaults['relationship_type_id'] = $source[$this->source_id]['configuration']['relationship_type_id'];
    }

    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();

    $values = $this->exportValues();
    $params['configuration']['relationship_type_id'] = $values['relationship_type_id'];
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

  function buildRelationshipTypeSelectList() {
    $relationship_type_api = civicrm_api3('RelationshipType', 'get', array('options' => array('limit' => 0)));
    $relationship_types = array();
    foreach($relationship_type_api['values'] as $rel_type) {
      $relationship_types[$rel_type['id']] = $rel_type['label_a_b'];
    }
    return $relationship_types;
  }

}