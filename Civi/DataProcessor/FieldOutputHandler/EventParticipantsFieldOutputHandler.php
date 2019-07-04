<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\DataProcessor\Source\SourceInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;

class EventParticipantsFieldOutputHandler extends AbstractFieldOutputHandler implements OutputHandlerSortable{

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $inputFieldSpec;

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $outputFieldSpec;

  /**
   * @var SourceInterface
   */
  protected $dataSource;

  /**
   * @var array
   */
  protected $status_ids = array();

  /**
   * @var array
   */
  protected $role_ids = array();

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getOutputFieldSpecification() {
    return $this->outputFieldSpec;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getSortableInputFieldSpec() {
    return $this->inputFieldSpec;
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
    $this->dataSource = $this->dataProcessor->getDataSourceByName($configuration['datasource']);
    if (!$this->dataSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(1=>$title, 2=>$configuration['datasource'])));
    }
    $this->inputFieldSpec = $this->dataSource->getAvailableFields()->getFieldSpecificationByName($configuration['field']);
    if (!$this->inputFieldSpec) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['field'],
        3 => $configuration['datasource']
      )));
    }
    $this->dataSource->ensureFieldInSource($this->inputFieldSpec);

    $this->outputFieldSpec = new FieldSpecification($alias, 'String', $title, $alias);
    if (isset($configuration['role_ids'])) {
      $this->role_ids = $configuration['role_ids'];
    }
    if (isset($configuration['status_ids'])) {
      $this->status_ids = $configuration['status_ids'];
    }
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
    $event_id = $rawRecord[$this->inputFieldSpec->alias];
    $output = new JsonFieldOutput();
    if ($event_id) {
      $participantsSql = "
        SELECT `c`.`display_name`, `c`.`id` as `contact_id`, `p`.`id` as `participant_id` 
        FROM `civicrm_contact` `c`
        INNER JOIN `civicrm_participant` `p` ON `p`.`contact_id` = `c`.`id`
        WHERE `p`.`event_id` = %1";
      $participantsSqlParams[1] = array($event_id, 'Integer');
      if (count($this->role_ids)) {
        $participantsSql .= " AND `p`.`role_id` IN (".implode(', ', $this->role_ids).")";
      }
      if (count($this->status_ids)) {
        $participantsSql .= " AND `p`.`status_id` IN (".implode(', ', $this->status_ids).")";
      }
      $participantsSql .= " ORDER BY `display_name`";
      $participants = array();
      $jsonData = array();
      $dao = \CRM_Core_DAO::executeQuery($participantsSql, $participantsSqlParams);
      while($dao->fetch()) {
        $participants[] = $dao->display_name;
        $jsonData[] = array(
          'contact_id' => $dao->contact_id,
          'participant_id' => $dao->participant_id,
          'display_name' => $dao->display_name
        );
      }
      $output->rawValue = implode(', ', $participants);
      $output->formattedValue = implode(', ', $participants);
      $output->setData($jsonData);
    }
    return $output;
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
    $fieldSelect = $this->getFieldOptions($field['data_processor_id']);
    $roles = array();
    $roleApi = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => 'participant_role',
      'options' => array('limit' => 0)
    ));
    foreach($roleApi['values'] as $role) {
      $roles[$role['value']] = $role['label'];
    }
    $statuses = array();
    $statusApi = civicrm_api3('ParticipantStatusType', 'get', array(
      'options' => array('limit' => 0)
    ));
    foreach($statusApi['values'] as $status) {
      $statuses[$status['id']] = $status['label'];
    }

    $form->add('select', 'field', E::ts('Event ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));

    $form->add('select', 'role_ids', E::ts('Roles'), $roles, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- all roles -'),
      'multiple' => true,
    ));
    $form->add('select', 'status_ids', E::ts('Participant Status'), $statuses, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- all statuses -'),
      'multiple' => true,
    ));

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = array();
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        $defaults['field'] = $configuration['datasource'] . '::' . $configuration['field'];
      }
      if (isset($configuration['role_ids'])) {
        $defaults['role_ids'] = $configuration['role_ids'];
      }
      if (isset($configuration['status_ids'])) {
        $defaults['status_ids'] = $configuration['status_ids'];
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
    return "CRM/Dataprocessor/Form/Field/Configuration/EventParticipantsFieldOutputHandler.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    list($datasource, $field) = explode('::', $submittedValues['field'], 2);
    $configuration['field'] = $field;
    $configuration['datasource'] = $datasource;
    $configuration['role_ids'] = $submittedValues['role_ids'];
    $configuration['status_ids'] = $submittedValues['status_ids'];
    return $configuration;
  }

  /**
   * Returns all possible fields
   *
   * @param $data_processor_id
   *
   * @return array
   * @throws \Exception
   */
  protected function getFieldOptions($data_processor_id) {
    $fieldSelect = \CRM_Dataprocessor_Utils_DataSourceFields::getAvailableFieldsInDataSources($data_processor_id, array($this, 'isFieldValid'));
    return $fieldSelect;
  }

  /**
   * Callback function for determining whether this field could be handled by this output handler.
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $field
   * @return bool
   */
  public function isFieldValid(FieldSpecification $field) {
    return true;
  }


}