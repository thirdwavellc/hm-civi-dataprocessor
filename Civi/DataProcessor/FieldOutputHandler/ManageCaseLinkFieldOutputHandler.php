<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\DataProcessor\Source\SourceInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;

class ManageCaseLinkFieldOutputHandler extends AbstractFieldOutputHandler {

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $dataSource;

  /**
   * @var SourceInterface
   */
  protected $contactIdSource;

  /**
   * @var FieldSpecification
   */
  protected $contactIdField;

  /**
   * @var SourceInterface
   */
  protected $caseIdSource;

  /**
   * @var FieldSpecification
   */
  protected $caseIdField;

  /**
   * @var FieldSpecification
   */
  protected $outputFieldSpecification;

  /**
   * @var String
   */
  protected $linkTitle;

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
    return 'String';
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
    $this->contactIdSource = $this->dataProcessor->getDataSourceByName($configuration['contact_id_datasource']);
    if (!$this->contactIdSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(
        1=>$title,
        2=>$configuration['contact_id_datasource'])
      ));
    }
    $this->contactIdField = $this->contactIdSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['contact_id_field']);
    if (!$this->contactIdField) {
      $this->contactIdField = $this->contactIdSource->getAvailableFields()->getFieldSpecificationByName($configuration['contact_id_field']);
    }
    if (!$this->contactIdField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['contact_id_field'],
        3 => $configuration['contact_id_datasource']
      )));
    }
    $this->contactIdSource->ensureFieldInSource($this->contactIdField);

    $this->caseIdSource = $this->dataProcessor->getDataSourceByName($configuration['case_id_datasource']);
    if (!$this->caseIdSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(
        1=>$title,
        2=>$configuration['case_id_datasource'])
      ));
    }
    $this->caseIdField = $this->caseIdSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['case_id_field']);
    if (!$this->caseIdField) {
      $this->caseIdField = $this->caseIdSource->getAvailableFields()->getFieldSpecificationByName($configuration['case_id_field']);
    }
    if (!$this->caseIdField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['case_id_field'],
        3 => $configuration['case_id_datasource']
      )));
    }
    $this->caseIdSource->ensureFieldInSource($this->caseIdField);

    $this->linkTitle = E::ts('Manage case');
    if (!empty($configuration['link_title'])) {
      $this->linkTitle = $configuration['link_title'];
    }

    $this->outputFieldSpecification = new FieldSpecification($alias, 'String', $title, null, $alias);
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
    $contactId = $rawRecord[$this->contactIdField->alias];
    $caseId = $rawRecord[$this->caseIdField->alias];
    $url = \CRM_Utils_System::url('civicrm/contact/view/case', array(
      'reset' => 1,
      'action' => 'view',
      'cid' => $contactId,
      'id' => $caseId
    ));
    $link = '<a href="'.$url.'">'.$this->linkTitle.'</a>';
    $formattedValue = new HTMLFieldOutput($contactId);
    $formattedValue->formattedValue = $url;
    $formattedValue->setHtmlOutput($link);
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

    $form->add('select', 'contact_id_field', E::ts('Contact ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'case_id_field', E::ts('Case ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));

    $form->add('text', 'link_title', E::ts('Link text'), ['class' => 'huge'], false);

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = array();
      if (isset($configuration['contact_id_field']) && isset($configuration['contact_id_datasource'])) {
        $defaults['contact_id_field'] = $configuration['contact_id_datasource'] . '::' . $configuration['contact_id_field'];
      }
      if (isset($configuration['case_id_field']) && isset($configuration['case_id_datasource'])) {
        $defaults['case_id_field'] = $configuration['case_id_datasource'] . '::' . $configuration['case_id_field'];
      }
      if (isset($configuration['link_title'])) {
        $defaults['link_title'] = $configuration['link_title'];
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
    return "CRM/Dataprocessor/Form/Field/Configuration/ManageCaseLinkFieldOutputHandler.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    list($contact_id_datasource, $contact_id_field) = explode('::', $submittedValues['contact_id_field'], 2);
    $configuration['contact_id_field'] = $contact_id_field;
    $configuration['contact_id_datasource'] = $contact_id_datasource;
    list($case_id_datasource, $case_id_field) = explode('::', $submittedValues['case_id_field'], 2);
    $configuration['case_id_field'] = $case_id_field;
    $configuration['case_id_datasource'] = $case_id_datasource;
    if (isset($submittedValues['link_title'])) {
      $configuration['link_title'] = $submittedValues['link_title'];
    }
    return $configuration;
  }




}
