<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

abstract class AbstractFieldOutputHandler {

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $outputFieldSpecification;

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $dataSource;

  /**
   * Returns the name of the handler type.
   *
   * @return String
   */
  abstract public function getName();

  /**
   * Returns the title of the handler type.
   *
   * @return String
   */
  abstract public function getTitle();

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  abstract protected function getType();

  /**
   * Returns the formatted value
   *
   * @param $rawRecord
   * @param $formattedRecord
   *
   * @return \Civi\DataProcessor\FieldOutputHandler\FieldOutput
   */
  abstract public function formatField($rawRecord, $formattedRecord);

  /**
   * AbstractFieldOutputHandler constructor.
   *
   * @param \Civi\DataProcessor\Source\SourceInterface $dataSource
   */
  public function __construct(\Civi\DataProcessor\Source\SourceInterface $dataSource) {
    $this->dataSource = $dataSource;
  }

  /**
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function getDataSource() {
    return $this->dataSource;
  }

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param array $configuration
   */
  public function initialize($alias, $title, $configuration) {
    // Override this in child classes.
    $this->outputFieldSpecification->title = $title;
    $this->outputFieldSpecification->alias = $alias;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getOutputFieldSpecification() {
    return $this->outputFieldSpecification;
  }

  /**
   * Returns true when this handler has additional configuration.
   *
   * @return bool
   */
  public function hasConfiguration() {
    return false;
  }

  /**
   * When this handler has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $field
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    // Example add a checkbox to the form.
    // $form->add('checkbox', 'show_label', E::ts('Show label'));
  }

  /**
   * When this handler has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    // Example return "CRM/FormFieldLibrary/Form/FieldConfiguration/TextField.tpl";
    return false;
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    // Add the show_label to the configuration array.
    // $configuration['show_label'] = $submittedValues['show_label'];
    // return $configuration;
    return array();
  }


}