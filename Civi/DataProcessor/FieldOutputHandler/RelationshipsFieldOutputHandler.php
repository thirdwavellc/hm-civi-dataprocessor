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
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;

class RelationshipsFieldOutputHandler extends AbstractFieldOutputHandler {

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
   * @var FieldSpecification
   */
  protected $outputFieldSpecification;

  /**
   * @var array
   */
  protected $relationship_type_ids = array();

  protected $show_label = true;

  protected $separator = ', ';

  protected $sort = 'label-name';

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
    $this->outputFieldSpecification = new FieldSpecification($alias, 'String', $title, null, $alias);
    $this->contactIdSource = $this->dataProcessor->getDataSourceByName($configuration['datasource']);
    if (!$this->contactIdSource) {
      throw new DataSourceNotFoundException(E::ts("Field %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(1=>$title, 2=>$configuration['datasource'])));
    }
    $this->contactIdField = $this->contactIdSource->getAvailableFields()->getFieldSpecificationByAlias($configuration['field']);
    if (!$this->contactIdField) {
      $this->contactIdField = $this->contactIdSource->getAvailableFields()->getFieldSpecificationByName($configuration['field']);
    }
    if (!$this->contactIdField) {
      throw new FieldNotFoundException(E::ts("Field %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['field'],
        3 => $configuration['datasource']
      )));
    }
    $this->contactIdSource->ensureFieldInSource($this->contactIdField);

    $this->outputFieldSpecification = new FieldSpecification($this->contactIdField->name, 'String', $title, null, $alias);

    if (isset($configuration['relationship_types']) && is_array($configuration['relationship_types'])) {
      $this->relationship_type_ids = array();
      foreach($configuration['relationship_types'] as $rel_type) {
        $dir = substr($rel_type, 0, 3);
        $rel_type_name = substr($rel_type, 4);
        if ($dir == 'a_b') {
          $rel_type_id = civicrm_api3('RelationshipType', 'getvalue', ['return' => 'id', 'name_a_b' => $rel_type_name]);
        } else {
          $rel_type_id = civicrm_api3('RelationshipType', 'getvalue', ['return' => 'id', 'name_b_a' => $rel_type_name]);
        }
        $this->relationship_type_ids[] = array('dir' => $dir, 'id' => $rel_type_id);
      };
    }
    if (isset($configuration['show_label'])) {
      $this->show_label = $configuration['show_label'];
    }
    if (isset($configuration['separator'])) {
      $this->separator = $configuration['separator'];
    }
    if (isset($configuration['sort'])) {
      $this->sort = $configuration['sort'];
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
    $contactId = $rawRecord[$this->contactIdField->alias];
    $sql['a_b'] = "SELECT c.id, c.display_name, t.label_a_b as label, r.relationship_type_id, c.contact_type, c.contact_sub_type
            FROM civicrm_contact c
            INNER JOIN civicrm_relationship r ON r.contact_id_b = c.id
            INNER JOIN civicrm_relationship_type t on r.relationship_type_id = t.id
            WHERE c.is_deleted = 0 AND r.is_active = 1 AND r.contact_id_a = %1";
    $sql['b_a'] = "SELECT c.id, c.display_name, t.label_b_a as label, r.relationship_type_id, c.contact_type, c.contact_sub_type
            FROM civicrm_contact c
            INNER JOIN civicrm_relationship r ON r.contact_id_a = c.id
            INNER JOIN civicrm_relationship_type t on r.relationship_type_id = t.id
            WHERE c.is_deleted = 0 AND r.is_active = 1 AND r.contact_id_b = %1";
    if (count($this->relationship_type_ids)) {
      $relationShipTypeIdsA_B = array();
      $relationShipTypeIdsB_A = array();
      foreach($this->relationship_type_ids as $rel_type) {
        if ($rel_type['dir'] == 'a_b') {
          $relationShipTypeIdsA_B[] = $rel_type['id'];
        } else {
          $relationShipTypeIdsB_A[] = $rel_type['id'];
        }
      }
      if (count($relationShipTypeIdsA_B)) {
        $sql['a_b'] .= " AND t.id IN (" . implode(", ", $relationShipTypeIdsA_B) . ") ";
      } else {
        unset($sql['a_b']);
      }
      if (count($relationShipTypeIdsB_A)) {
        $sql['b_a'] .= " AND t.id IN (" . implode(", ", $relationShipTypeIdsB_A) . ") ";
      } else {
        unset($sql['b_a']);
      }
    }
    $formattedValues = [];
    $htmlFormattedValues = [];
    if (count($sql)) {
      $sql = implode(" UNION ", $sql);
      switch ($this->sort) {
        case 'birthdate':
          $sql .= " ORDER BY c.birth_date ASC, display_name ASC";
          break;
        case 'name':
          $sql .= " ORDER BY display_name ASC";
          break;
        default:
          $sql .= " ORDER BY label ASC, display_name ASC";
          break;
      }
      $sqlParams[1] = [$contactId, 'Integer'];
      $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
      while ($dao->fetch()) {
        $url = \CRM_Utils_System::url('civicrm/contact/view', [
          'reset' => 1,
          'cid' => $dao->id,
        ]);
        $link = '<a href="' . $url . '">' . $dao->display_name . '</a>';
        $image = \CRM_Contact_BAO_Contact_Utils::getImage($dao->contact_sub_type ? $dao->contact_sub_type : $dao->contact_type,  FALSE, $dao->id);
        if ($this->show_label) {
          $htmlFormattedValues[] = $image .'&nbsp;' . $dao->label . ':&nbsp;' . $link;
          $formattedValues[] = $dao->label.': '.$dao->display_name;
        }
        else {
          $htmlFormattedValues[] = $image .'&nbsp;' . $link;
          $formattedValues[] = $dao->display_name;
        }
      }
    }
    $output = new HTMLFieldOutput($contactId);
    $output->formattedValue = implode($this->separator, $formattedValues);
    $output->setHtmlOutput(implode("<br>", $htmlFormattedValues));
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
    $fieldSelect = \CRM_Dataprocessor_Utils_DataSourceFields::getAvailableFieldsInDataSources($field['data_processor_id']);
    $relationshipTypeApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $relationshipTypes = array();
    foreach($relationshipTypeApi['values'] as $relationship_type) {
      $relationshipTypes['a_b_'.$relationship_type['name_a_b']] = $relationship_type['label_a_b'];
      $relationshipTypes['b_a_'.$relationship_type['name_b_a']] = $relationship_type['label_b_a'];
    }
    $sort['label-name'] = E::ts('Relationship Type, Display Name');
    $sort['birthdate'] = E::ts('Birth date, Display Name');
    $sort['name'] = E::ts('Display Name');

    $form->add('select', 'contact_id_field', E::ts('Contact ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge data-processor-field-for-name',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'relationship_types', E::ts('Restrict to relationship'), $relationshipTypes, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- Show all roles -'),
      'multiple' => true,
    ));
    $form->add('checkbox', 'show_label', E::ts('Show relationship type'), false, false);
    $form->add('text', 'separator', E::ts('Separator'), true);
    $form->add('select', 'sort', E::ts('Sort'), $sort, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
    ));
    $defaults = array();
    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        $defaults['contact_id_field'] = $configuration['datasource'] . '::' . $configuration['field'];
      }
      if (isset($configuration['relationship_types'])) {
        $defaults['relationship_types'] = $configuration['relationship_types'];
      }
      if (isset($configuration['show_label'])) {
        $defaults['show_label'] = $configuration['show_label'];
      } elseif (!isset($configuration['show_label'])) {
        $defaults['show_label'] = 1;
      }
      if (isset($configuration['separator'])) {
        $defaults['separator'] = $configuration['separator'];
      }
      if (isset($configuration['sort'])) {
        $defaults['sort'] = $configuration['sort'];
      }
    }
    if (!isset($defaults['separator'])) {
      $defaults['separator'] = ',';
    }
    if (!isset($defaults['sort'])) {
      $defaults['sort'] = 'label-name';
    }
    $form->setDefaults($defaults);
  }

  /**
   * When this handler has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Field/Configuration/RelationshipsFieldOutputHandler.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    list($datasource, $field) = explode('::', $submittedValues['contact_id_field'], 2);
    $configuration['field'] = $field;
    $configuration['datasource'] = $datasource;
    $configuration['relationship_types'] = isset($submittedValues['relationship_types']) ? $submittedValues['relationship_types'] : array();
    $configuration['show_label'] = isset($submittedValues['show_label']) ? $submittedValues['show_label'] : 0;
    $configuration['separator'] = $submittedValues['separator'];
    $configuration['sort'] = $submittedValues['sort'];
    return $configuration;
  }




}
