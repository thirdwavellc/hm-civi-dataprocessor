<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;
use Civi\DataProcessor\Source\SourceInterface;

use CRM_Dataprocessor_ExtensionUtil as E;

class IsActiveFieldOutputHandler extends AbstractFieldOutputHandler {

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $dataSource;

  /**
   * @var SourceInterface
   */
  protected $isActiveSource;

  /**
   * @var FieldSpecification
   */
  protected $isActiveField;

  /**
   * @var SourceInterface
   */
  protected $startDateSource;

  /**
   * @var FieldSpecification
   */
  protected $startDateField;

  /**
   * @var SourceInterface
   */
  protected $endDateSource;

  /**
   * @var FieldSpecification
   */
  protected $endDateField;

  /**
   * @var FieldSpecification
   */
  protected $outputFieldSpecification;

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getOutputFieldSpecification() {
    return $this->outputFieldSpecification;
  }

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return 'Boolean';
  }

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param array $configuration
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $processorType
   */
  public function initialize($alias, $title, $configuration) {
    $this->isActiveSource = $this->dataProcessor->getDataSourceByName($configuration['is_active_datasource']);
    if (!$this->isActiveSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(
          1=>$title,
          2=>$configuration['is_active_datasource'])
      ));
    }
    $this->isActiveField = $this->isActiveSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['is_active_field']);
    if (!$this->isActiveField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['is_active_field'],
        3 => $configuration['is_active_datasource']
      )));
    }
    $this->isActiveSource->ensureFieldInSource($this->isActiveField);

    $this->startDateSource = $this->dataProcessor->getDataSourceByName($configuration['start_date_datasource']);
    if (!$this->startDateSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(
          1=>$title,
          2=>$configuration['start_date_datasource'])
      ));
    }
    $this->startDateField = $this->isActiveSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['start_date_field']);
    if (!$this->startDateField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['start_date_field'],
        3 => $configuration['start_date_datasource']
      )));
    }
    $this->startDateSource->ensureFieldInSource($this->startDateField);

    $this->endDateSource = $this->dataProcessor->getDataSourceByName($configuration['end_date_datasource']);
    if (!$this->endDateSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(
          1=>$title,
          2=>$configuration['end_date_datasource'])
      ));
    }
    $this->endDateField = $this->isActiveSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['end_date_field']);
    if (!$this->endDateField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['end_date_field'],
        3 => $configuration['end_date_datasource']
      )));
    }
    $this->endDateSource->ensureFieldInSource($this->endDateField);

    $this->outputFieldSpecification = new FieldSpecification($this->isActiveField->name, 'Boolean', $title, null, $alias);
  }

  /**
   * Returns the formatted value
   *
   * @param $rawRecord
   * @param $formattedRecord
   *
   * @return \Civi\DataProcessor\FieldOutputHandler\FieldOutput
   */
  public function formatField($rawRecord, $formattedRecord) {
    $isActive = $rawRecord[$this->isActiveField->alias];
    $value = $isActive ? true : false;

    $startDate = $rawRecord[$this->startDateField->alias];
    $endDate = $rawRecord[$this->endDateField->alias];
    if ($startDate) {
      $startDate = new \DateTime($startDate);
    }
    if ($endDate) {
      $endDate = new \DateTime($endDate);
    }
    $today = new \DateTime();
    if (
      ($value) &&
      (!$startDate || $startDate->format(('Ymd') <= $today->format('Ymd'))) &&
      (!$endDate || $endDate->format(('Ymd') >= $today->format('Ymd')))
    ) {
      $value = true;
    }

    $formattedValue = new FieldOutput($value);
    return $formattedValue;
  }

  /**
   * Returns true when this handler has additional configuration.
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this handler has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $field
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    $fieldSelect = \CRM_Dataprocessor_Utils_DataSourceFields::getAvailableFieldsInDataSources($field['data_processor_id']);

    $form->add('select', 'is_active_field', E::ts('Is Active Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge data-processor-field-for-name',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'start_date_field', E::ts('Start Date Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'end_date_field', E::ts('End Date Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = array();
      if (isset($configuration['is_active_field']) && isset($configuration['is_active_datasource'])) {
        $defaults['is_active_field'] = $configuration['is_active_datasource'] . '::' . $configuration['is_active_field'];
      }
      if (isset($configuration['start_date_field']) && isset($configuration['start_date_datasource'])) {
        $defaults['start_date_field'] = $configuration['start_date_datasource'] . '::' . $configuration['start_date_field'];
      }
      if (isset($configuration['end_date_field']) && isset($configuration['end_date_datasource'])) {
        $defaults['end_date_field'] = $configuration['end_date_datasource'] . '::' . $configuration['end_date_field'];
      }
      $form->setDefaults($defaults);
    }
  }

  /**
   * When this handler has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Field/Configuration/IsActiveFieldOutputHandler.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    list($is_active_datasource, $is_active_field) = explode('::', $submittedValues['is_active_field'], 2);
    $configuration['is_active_field'] = $is_active_field;
    $configuration['is_active_datasource'] = $is_active_datasource;
    list($start_date_datasource, $start_date_field) = explode('::', $submittedValues['start_date_field'], 2);
    $configuration['start_date_field'] = $start_date_field;
    $configuration['start_date_datasource'] = $start_date_datasource;
    list($end_date_datasource, $end_date_field) = explode('::', $submittedValues['end_date_field'], 2);
    $configuration['end_date_field'] = $end_date_field;
    $configuration['end_date_datasource'] = $end_date_datasource;
    return $configuration;
  }

}
