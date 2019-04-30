<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;


use Civi\DataProcessor\DataFlow\CsvDataFlow;
use Civi\DataProcessor\DataFlow\InMemoryDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

use CRM_Dataprocessor_ExtensionUtil as E;

class CSV extends AbstractSource {

  protected $headerRow;

  protected $rows;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFields;

  /**
   * Initialize the join
   *
   * @return void
   */
  public function initialize() {
    if ($this->dataFlow) {
      return;
    }

    $this->availableFields = new DataSpecification();
    $uri = $this->configuration['uri'];
    $skipRows = 0;
    $headerRowNumber = 0;
    if (isset($this->configuration['first_row_as_header']) && $this->configuration['first_row_as_header']) {
      $skipRows = 1;
      $headerRowNumber = 1;
    }
    $delimiter = $this->configuration['delimiter'];
    $enclosure = $this->configuration['enclosure'];
    $escape = $this->configuration['escape'];
    $this->dataFlow = new CsvDataFlow($uri, $skipRows, $delimiter, $enclosure, $escape);
    $this->headerRow = $this->dataFlow->getHeaderRow($headerRowNumber);

    foreach($this->headerRow as  $idx => $colName) {
      $name = 'col_'.$idx;
      $alias = $this->getSourceName().$name;
      $field = new FieldSpecification($name, 'String', $colName, null, $alias);
      $this->availableFields->addFieldSpecification($name, $field);
    }
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFields() {
    $this->initialize();
    return $this->availableFields;
  }

  /**
   * Returns true when this source has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this source has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $source
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $source=array()) {
    $form->add('text', 'uri', E::ts('URI'), true);
    $form->add('text', 'delimiter', E::ts('Field delimiter'), true);
    $form->add('text', 'enclosure', E::ts('Field enclosure character'), true);
    $form->add('text', 'escape', E::ts('Escape character'), true);
    $form->add('checkbox', 'first_row_as_header', E::ts('First row contains column names'));

    $defaults = array();
    foreach($source['configuration'] as $field => $value) {
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
    $form->setDefaults($defaults);
  }

  /**
   * When this source has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Source/Csv.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    $configuration = array();
    $configuration['uri'] = $submittedValues['uri'];
    $configuration['delimiter'] = $submittedValues['delimiter'];
    $configuration['enclosure'] = $submittedValues['enclosure'];
    $configuration['escape'] = $submittedValues['escape'];
    $configuration['first_row_as_header'] = $submittedValues['first_row_as_header'];

    return $configuration;
  }

}