<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Source\SourceInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class CaseRoleFilter extends AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $fieldSpecification;

  /**
   * @var SourceInterface
   */
  protected $dataSource;

  public function __construct() {
    parent::__construct();
  }

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param bool $is_required
   * @param array $configuration
   */
  public function initialize($alias, $title, $is_required, $configuration) {
    if ($this->fieldSpecification) {
      return; // Already initialized.
    }
    if (!isset($configuration['datasource']) || !isset($configuration['field'])) {
      return; // Invalid configuration
    }

    $this->is_required = $is_required;

    $this->dataSource = $this->data_processor->getDataSourceByName($configuration['datasource']);
    if ($this->dataSource) {
      $this->fieldSpecification  =  clone $this->dataSource->getAvailableFilterFields()->getFieldSpecificationByName($configuration['field']);
      $this->fieldSpecification->alias = $alias;
      $this->fieldSpecification->title = $title;
    }
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getFieldSpecification() {
    return $this->fieldSpecification;
  }

  /**
   * @param array $filter
   *   The filter settings
   * @return mixed
   */
  public function setFilter($filter) {
    $dataFlow  = $this->dataSource->ensureField($this->fieldSpecification->name);
    $cids = $filter['value'];
    if (!is_array($cids)) {
      $cids = array($cids);
    }
    $relationshipTableAlias = 'civicrm_relationship_'.$this->fieldSpecification->alias;
    if ($dataFlow && $dataFlow instanceof SqlDataFlow) {
      $whereClause = new SqlDataFlow\InTableWhereClause(
        'case_id',
        'civicrm_relationship',
        $relationshipTableAlias,
        array(
          new SqlDataFlow\SimpleWhereClause($relationshipTableAlias, 'is_active', '=', '1'),
          new SqlDataFlow\SimpleWhereClause($relationshipTableAlias, 'case_id', 'IS NOT NULL', 0),
          new SqlDataFlow\SimpleWhereClause($relationshipTableAlias, 'contact_id_b', 'IN', $cids),
        ),
        $dataFlow->getName(),
        $this->fieldSpecification->name,
        $filter['op']
      );

      $dataFlow->addWhereClause($whereClause);
    }
  }

  /**
   * Returns true when this filter has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this filter type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $filter
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $filter=array()) {
    $fieldSelect = \CRM_Dataprocessor_Utils_DataSourceFields::getAvailableFilterFieldsInDataSources($filter['data_processor_id']);

    $form->add('select', 'case_id_field', E::ts('Case ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($filter['configuration'])) {
      $configuration = $filter['configuration'];
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        $defaults['field'] = $configuration['datasource'] . '::' . $configuration['field'];
        $form->setDefaults($defaults);
      }
    }
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Filter/Configuration/CaseRoleFilter.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    list($datasource, $field) = explode('::', $submittedValues['case_id_field'], 2);
    $configuration['field'] = $field;
    $configuration['datasource'] = $datasource;
    return $configuration;
  }

  /**
   * Add the elements to the filter form.
   *
   * @param \CRM_Core_Form $form
   * @return array
   *   Return variables belonging to this filter.
   */
  public function addToFilterForm(\CRM_Core_Form $form) {
    $fieldSpec = $this->getFieldSpecification();
    $operations = $this->getOperatorOptions($fieldSpec);

    $title = $fieldSpec->title;
    if ($this->isRequired()) {
      $title .= ' <span class="crm-marker">*</span>';
    }

    $form->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations);
    $form->addEntityRef( "{$fieldSpec->alias}_value", NULL, array(
      'placeholder' => E::ts('Select a contact'),
      'entity' => 'Contact',
      'create' => false,
      'multiple' => true,
    ));

    $filter['type'] = $fieldSpec->type;
    $filter['title'] = $title;

    return $filter;
  }

  protected function getOperatorOptions(\Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpec) {
    return array(
      'IN' => E::ts('Is one of'),
      'NOT IN' => E::ts('Is not one of'),
    );
  }


}