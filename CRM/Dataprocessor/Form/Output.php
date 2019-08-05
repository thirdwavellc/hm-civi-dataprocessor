<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Output extends CRM_Core_Form {

  private $dataProcessorId;

  private $id;

  private $dashlet;

  private $dashlet_id;

  private $output;

  /**
   * @var Civi\DataProcessor\Output\OutputInterface
   */
  private $outputTypeClass;

  private $snippet;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {

    $this->snippet = CRM_Utils_Request::retrieve('snippet', 'String');

    if ($this->snippet) {
      $this->assign('suppressForm', TRUE);
      $this->controller->_generateQFKey = FALSE;
    }

    $factory = dataprocessor_get_factory();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('id', $this->id);

    $dashlet = CRM_Utils_Request::retrieve('dashlet', 'Integer');
    // dashlet 1->Yes 2->No

    if ($this->id) {
      $this->output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $this->id));
      $this->assign('output', $this->output);
      $this->outputTypeClass = $factory->getOutputByName($this->output['type']);
      $this->assign('has_configuration', $this->outputTypeClass->hasConfiguration());


      // Check for Dashlet
      $dashlet_url = $this->createDashletUrl($this->id,$this->dataProcessorId);
      try{
        $result_dashlet = civicrm_api3('Dashboard', 'getsingle', [
          'url' => $dashlet_url,
        ]);
        $this->dashlet = 1;
        $this->dashlet_id = $result_dashlet['id'];
        $this->output['dashlet'] = $this->dashlet;
        $this->output['dashlet_name'] = $result_dashlet['name'];
        $this->output['dashlet_title'] = $result_dashlet['label'];
        $this->output['dashlet_active'] = $result_dashlet['is_active'];
      }
      catch(Exception $e){
        $this->dashlet = 2;
        $this->output['dashlet'] = $this->dashlet;
      }

    }

    $type = CRM_Utils_Request::retrieve('type', 'String');
    if ($type) {
      $this->outputTypeClass = $factory->getOutputByName($type);
      $this->assign('has_configuration', $this->outputTypeClass->hasConfiguration());
    }

    if (!$this->output) {
      $this->output['data_processor_id'] = $this->dataProcessorId;
    }
    if($dashlet){
      $this->dashlet = $dashlet;
      $this->output['dashlet'] = $this->dashlet;
    }

    $title = E::ts('Data Processor Output');
    CRM_Utils_System::setTitle($title);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
      $factory = dataprocessor_get_factory();
      $types = array(' - select - ')  + $factory->getOutputs();
      $this->add('select', 'type', ts('Select output'), $types, true, array('class' => 'crm-select2'));
      $this->add('select', 'dashlet', E::ts('Add Output as Dashlet'), array(''=>' - select - ', 1=>'Yes', 2=> 'No'), true,array('id' => 'dashlet'));
      if ($this->outputTypeClass && $this->outputTypeClass->hasConfiguration()) {
        $this->outputTypeClass->buildConfigurationForm($this, $this->output);
        $this->assign('configuration_template', $this->outputTypeClass->getConfigurationTemplateFileName());
      }

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

    if (isset($this->output['type'])) {
      $defaults['type'] = $this->output['type'];
    }
    if (isset($this->output['dashlet'])) {
      $defaults['dashlet'] = $this->output['dashlet'];
    }
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
      $result = civicrm_api3('DataProcessorOutput', 'get', [
        'sequential' => 1,
        'return' => ["configuration"],
        'id' => $this->id,
      ]);

      civicrm_api3('DataProcessorOutput', 'delete', array('id' => $this->id));
      $session->setStatus(E::ts('Data Processor Output removed'), E::ts('Removed'), 'success');
      CRM_Core_BAO_Navigation::resetNavigation();
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();
    $params['type'] = $values['type'];
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->id) {
      $params['id'] = $this->id;
    }
    $params['configuration'] = $this->outputTypeClass->processConfiguration($values, $params);
    
    $result = civicrm_api3('DataProcessorOutput', 'create', $params);
    
    if($this->dashlet == 1){

      $dashlet_params = $this->outputTypeClass->processDashletConfiguration($values);
      $dashlet_params['url'] = $this->createDashletUrl($result['id'],$this->dataProcessorId);
      if ($this->dashlet_id) {
        $dashlet_params['id'] = $this->dashlet_id;
      }
      $dashlet_result = civicrm_api3('Dashboard', 'create', $dashlet_params);
    }
    elseif($this->dashlet == 2){
      if ($this->dashlet_id) {
        $dashlet_params['id'] = $this->dashlet_id;
        $dashlet_result = civicrm_api3('Dashboard', 'delete', $dashlet_params); 
      }
    }

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

  /**
   * Returns the url for the dashlet url
   *
   * @param array $outputId
   * @param array $dataProcessorId
   * @return string
   */

  public function createDashletUrl($outputId,$dataProcessorId){
    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/dashlet', array('outputId' => $outputId, 'dataProcessorId' => $dataProcessorId));
    //substr is used to remove starting slash
    return substr($url, 1);
  }

}
