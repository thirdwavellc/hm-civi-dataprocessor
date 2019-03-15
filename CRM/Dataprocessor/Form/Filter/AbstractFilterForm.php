<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * This class could be used as a base for other form classes.
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
abstract class CRM_Dataprocessor_Form_Filter_AbstractFilterForm extends CRM_Core_Form {

  protected $dataProcessorId;

  protected $id;

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
      $fiilter = CRM_Dataprocessor_BAO_Filter::getValues(array('id' => $this->id));
      $this->assign('field', $fiilter[$this->id]);
    }

    $title = E::ts('Data Processor  Filter  Configuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel')))
    );
    parent::buildQuickForm();
  }

  /**
   * Add a select for the field selection
   *
   * @throws \Exception
   */
  protected function addFieldSelect($label) {
    $fieldSelect = $this->getFieldOptions();

    $this->add('select', 'field', $label, $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
  }

  /**
   * Returns an array with the name of the field as the key and the label of the field as the value.
   *
   * @return array
   * @throws \Exception
   */
  protected function getFieldOptions() {
    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($this->dataProcessorId);
    foreach($dataProcessor->getDataSources() as $dataSource) {
      foreach($dataSource->getAvailableFilterFields()->getFields() as $field) {
        $fieldSelect[$dataSource->getSourceName().'::'.$field->name] = $dataSource->getSourceTitle().' :: '.$field->title;
      }
    }
    return $fieldSelect;
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['id'] = $this->id;

    return $defaults;
  }

  /**
   * Set default values for field
   *
   * @param $defaults
   *
   * @return mixed
   */
  protected function getDefaultValuesForField($defaults) {
    $filter = CRM_Dataprocessor_BAO_Filter::getValues(array('id' => $this->id));
    if (isset($filter[$this->id]['configuration'])) {
      $configuration = $filter[$this->id]['configuration'];
      if (isset($configuration['datasource']) && isset($configuration['field'])) {
        $defaults['field'] = $configuration['datasource'].'::'.$configuration['field'];
      }
    }
    return $defaults;
  }

  /**
   * Get the field configuration from the submitted Values
   * @param $configuration
   *
   * @return mixed
   */
  protected function getFieldConfigurationFromSubmittedValues($configuration) {
    $values = $this->exportValues();
    list($datasource, $field) = explode('::', $values['field'], 2);
    $configuration['field'] = $field;
    $configuration['datasource'] = $datasource;
    return $configuration;
  }

  /**
   * Save the configuration
   *
   * @param $configuration
   *
   * @throws \Exception
   */
  protected function saveConfiguration($configuration) {
    $filters = CRM_Dataprocessor_BAO_Filter::getValues(array('id' => $this->id));
    $params = $filters[$this->id];
    $params['configuration'] = $configuration;
    $result = CRM_Dataprocessor_BAO_Filter::add($params);
  }

}