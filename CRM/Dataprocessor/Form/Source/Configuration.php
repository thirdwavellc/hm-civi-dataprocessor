<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Form_Source_Configuration extends CRM_Core_Form {

  protected $dataProcessorId;

  protected $source_id;

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $sourceClass;

  /**
   * @var array
   *   The source object
   */
  protected $source;

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
    $this->source = $source[$this->source_id];
    $this->assign('source', $this->source);

    $factory = dataprocessor_get_factory();
    $this->sourceClass = $factory->getDataSourceByName($this->source['type']);
    $this->sourceClass->setSourceName($this->source['name']);
    $this->sourceClass->setSourceTitle($this->source['title']);

    $title = E::ts('Data Processor Source Configuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'source_id');

    $this->addFieldsForFiltering();

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

    if (isset($this->source['configuration']['filter'])) {
      foreach($this->source['configuration']['filter'] as $alias => $filter) {
        $defaults[$alias.'_op'] = $filter['op'];
        $defaults[$alias.'_value'] = $filter['value'];
      }
    }

    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();

    $values = $this->exportValues();
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->source_id) {
      $params['id'] = $this->source_id;
    }

    $params['configuration']['filter'] = $this->postProcessFieldsForFiltering();
    CRM_Dataprocessor_BAO_Source::add($params);
    CRM_Utils_System::redirect($session->readUserContext());
    parent::postProcess();
  }


  /**
   * Add a data specification to the form for filtering.
   */
  protected function addFieldsForFiltering() {
    $fields = array();
    foreach($this->sourceClass->getAvailableFilterFields()->getFields() as $fieldSpec) {
      $alias = $fieldSpec->name;
      switch ($fieldSpec->type) {
        case 'Boolean':
          $fields[$alias] = $fieldSpec->title;
          $this->addElement('select', "{$alias}_op", ts('Operator:'), [
            '=' => E::ts('Is equal to'),
            '!=' => E::ts('Is not equal to'),
          ]);
          if (!empty($fieldSpec->getOptions())) {
            $this->addElement('select', "{$alias}_value", $fieldSpec->title, array('' => E::ts(' - Select - ')) + $fieldSpec->getOptions());
          }
          break;
        default:
          if ($fieldSpec->getOptions()) {
            $fields[$alias] = $fieldSpec->title;
            $this->addElement('select', "{$alias}_op", ts('Operator:'), [
              'IN' => E::ts('Is one of'),
              'NOT IN' => E::ts('Is not one of'),
            ]);
            $this->addElement('select', "{$alias}_value", $fieldSpec->title, $fieldSpec->getOptions(), array('class' => 'crm-select2', 'multiple' => 'multiple'));
          }
      }
    }
    $this->assign('filter_fields', $fields);
  }
  /**
   * Create a configuration filtering array
   *
   * @return array
   */
  protected function postProcessFieldsForFiltering() {
    $values = $this->exportValues();
    $filter_config = array();
    foreach($this->sourceClass->getAvailableFilterFields()->getFields() as $fieldSpec) {
      $alias = $fieldSpec->name;
      if ($this->valueSubmitted($alias.'_value', $values)) {
        $filter_config[$alias] = array(
          'op' => $values[$alias.'_op'],
          'value' => $values[$alias.'_value']
        );
      }
    }
    return $filter_config;
  }

  protected function valueSubmitted($field, $values) {
    if (!isset($values[$field])) {
      return false;
    }
    if (is_array($values[$field]) && count($values[$field]) === 0) {
      return false;
    }
    if (is_string($values[$field]) && strlen($values[$field]) === 0) {
      return false;
    }
    return true;
  }

}