<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Form_Source_Configuration extends CRM_Dataprocessor_Form_Source_BaseForm {

  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->addFieldsForFiltering();
  }

  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if (isset($this->source['configuration']['filter'])) {
      foreach($this->source['configuration']['filter'] as $alias => $filter) {
        $defaults[$alias.'_op'] = $filter['op'];
        $defaults[$alias.'_value'] = $filter['value'];
      }
    }

    return $defaults;
  }

  public function postProcess() {
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->source_id) {
      $params['id'] = $this->source_id;
    }

    $params['configuration']['filter'] = $this->postProcessFieldsForFiltering();
    CRM_Dataprocessor_BAO_Source::add($params);
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
            $this->addElement('select', "{$alias}_value", $fieldSpec->title, $fieldSpec->getOptions(), array(
              'style' => 'min-width:250px',
              'class' => 'crm-select2 huge',
              'multiple' => 'multiple',
              'placeholder' => E::ts('- select -'),
            ));
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