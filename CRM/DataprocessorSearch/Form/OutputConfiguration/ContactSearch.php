<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_DataprocessorSearch_Form_OutputConfiguration_ContactSearch extends CRM_Dataprocessor_Form_Output_AbstractOutputForm {

  /**
   * @var CRM_Dataprocessor_Utils_Navigation
   */
  protected $navigation;

  public function preProcess() {
    parent::preProcess();
    $this->navigation = CRM_Dataprocessor_Utils_Navigation::singleton();
  }

  public function buildQuickForm() {
    parent::buildQuickForm();

    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($this->dataProcessorId);
    $fields = array();
    foreach($dataProcessor->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $fields[$field->alias] = $field->title;
    }

    $this->add('text', 'title', E::ts('Title'), true);

    $this->add('select','permission', E::ts('Permission'), CRM_Core_Permission::basicPermissions(), true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $this->add('select', 'contact_id_field', E::ts('Contact ID field'), $fields, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $this->add('select', 'hide_id_field', E::ts('Show Contact ID field'), array(0=>'Contact ID is Visible', 1=> 'Contact ID is hidden'));

    $this->add('wysiwyg', 'help_text', E::ts('Help text for this search'), array('rows' => 6, 'cols' => 80));

    // navigation field
    $navigationOptions = $this->navigation->getNavigationOptions();
    if (isset($this->output['configuration']['navigation_id'])) {
      $navigationPath = $this->navigation->getNavigationPathById($this->output['configuration']['navigation_id']);
      unset($navigationOptions[$navigationPath]);
    }
    $this->add('select', 'navigation_parent_path', ts('Parent Menu'), array('' => ts('- select -')) + $navigationOptions, true);
  }

  function setDefaultValues() {
    $dataProcessors = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
    $dataProcessor = $dataProcessors[$this->dataProcessorId];

    $defaults = parent::setDefaultValues();
    if ($this->output) {
      if (isset($this->output['permission'])) {
        $defaults['permission'] = $this->output['permission'];
      }
      if (isset($this->output['configuration']) && is_array($this->output['configuration'])) {
        if (isset($this->output['configuration']['contact_id_field'])) {
          $defaults['contact_id_field'] = $this->output['configuration']['contact_id_field'];
        }
        if (isset($this->output['configuration']['navigation_id'])) {
          $defaults['navigation_parent_path'] = $this->navigation->getNavigationParentPathById($this->output['configuration']['navigation_id']);
        }
        if (isset($this->output['configuration']['title'])) {
          $defaults['title'] = $this->output['configuration']['title'];
        }
        if (isset($this->output['configuration']['hide_id_field'])) {
          $defaults['hide_id_field'] = $this->output['configuration']['hide_id_field'];
        }
        if (isset($this->output['configuration']['help_text'])) {
          $defaults['help_text'] = $this->output['configuration']['help_text'];
        }
      }
    }
    if (!isset($defaults['permission'])) {
      $defaults['permission'] = 'access CiviCRM';
    }
    if (empty($defaults['title'])) {
      $defaults['title'] = $dataProcessor['title'];
    }

    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();

    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();

    $params['id'] = $this->id;
    $params['permission'] = $values['permission'];
    $params['configuration']['title'] = $values['title'];
    $params['configuration']['contact_id_field'] = $values['contact_id_field'];
    $params['configuration']['navigation_parent_path'] = $values['navigation_parent_path'];
    $params['configuration']['hide_id_field'] = $values['hide_id_field'];
    $params['configuration']['help_text'] = $values['help_text'];

    CRM_Dataprocessor_BAO_Output::add($params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

}