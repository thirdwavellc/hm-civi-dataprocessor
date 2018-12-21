<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Source_Csv extends CRM_Dataprocessor_Form_Source_BaseForm {

  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('text', 'uri', E::ts('URI'), true);
    $this->add('text', 'delimiter', E::ts('Field delimiter'), true);
    $this->add('text', 'enclosure', E::ts('Field enclosure character'), true);
    $this->add('text', 'escape', E::ts('Escape character'), true);
    $this->add('checkbox', 'first_row_as_header', E::ts('First row contains column names'));
  }

  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    foreach($this->source['configuration'] as $field => $value) {
      $defaults[$field] = $value;
    }
    if (!isset($defaults['delimiter'])) {
      $defaults['delimiter'] = ',';
    }
    if (!isset($defaults['enclosure'])) {
      $defaults['enclosure'] = '"';
    }
    if (!isset($defaults['escape'])) {
      $defaults['escape'] = '\\';
    }
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->source_id) {
      $params['id'] = $this->source_id;
    }

    $params['configuration']['uri'] = $values['uri'];
    $params['configuration']['delimiter'] = $values['delimiter'];
    $params['configuration']['enclosure'] = $values['enclosure'];
    $params['configuration']['escape'] = $values['escape'];
    $params['configuration']['first_row_as_header'] = $values['first_row_as_header'];
    CRM_Dataprocessor_BAO_Source::add($params);
    parent::postProcess();
  }

}