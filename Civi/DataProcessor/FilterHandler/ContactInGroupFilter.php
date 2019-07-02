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
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;
use Civi\DataProcessor\Source\SourceInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class ContactInGroupFilter extends AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $fieldSpecification;

  /**
   * @var SourceInterface
   */
  protected $dataSource;

  /**
   * @var array
   */
  protected $parent_group_id = false;

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
      throw new InvalidConfigurationException(E::ts("Filter %1 requires a field to filter on. None given.", array(1=>$title)));
    }

    $this->is_required = $is_required;

    $this->dataSource = $this->data_processor->getDataSourceByName($configuration['datasource']);
    if (!$this->dataSource) {
      throw new DataSourceNotFoundException(E::ts("Filter %1 requires data source '%2' which could not be found. Did you rename or deleted the data source?", array(1=>$title, 2=>$configuration['datasource'])));
    }
    $this->fieldSpecification  =  clone $this->dataSource->getAvailableFilterFields()->getFieldSpecificationByName($configuration['field']);
    if (!$this->fieldSpecification) {
      throw new FieldNotFoundException(E::ts("Filter %1 requires a field with the name '%2' in the data source '%3'. Did you change the data source type?", array(
        1 => $title,
        2 => $configuration['field'],
        3 => $configuration['datasource']
      )));
    }
    $this->fieldSpecification->alias = $alias;
    $this->fieldSpecification->title = $title;

    if (isset($configuration['parent_group']) && $configuration['parent_group']) {
      $this->parent_group_id = civicrm_api3('Group', 'getvalue', array('return' => 'id', 'name' => $configuration['parent_group']));
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
    $group_ids = $filter['value'];
    if (!is_array($group_ids)) {
      $group_ids = array($group_ids);
    }
    $groupTableAlias = 'civicrm_group_contact_'.$this->fieldSpecification->alias;
    $groupFilters = array(
      new SqlDataFlow\SimpleWhereClause($groupTableAlias, 'status', '=', 'Added'),
      new SqlDataFlow\SimpleWhereClause($groupTableAlias, 'group_id', 'IN', $group_ids),
    );

    if ($dataFlow && $dataFlow instanceof SqlDataFlow) {
      $whereClause = new SqlDataFlow\InTableWhereClause(
        'contact_id',
        'civicrm_group_contact',
        $groupTableAlias,
        $groupFilters,
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
    $groupsApi = civicrm_api3('Group', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $groups = array();
    foreach($groupsApi['values'] as $group) {
      $groups[$group['name']] = $group['title'];
    }

    $form->add('select', 'contact_id_field', E::ts('Contact ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));

    $form->add('select', 'parent_group', E::ts('Show only subgroup(s) of'), $groups, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- Show all groups -'),
      'multiple' => false,
    ));

    if (isset($filter['configuration'])) {
      $configuration = $filter['configuration'];
      $defaults = array();
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        $defaults['contact_id_field'] = $configuration['datasource'] . '::' . $configuration['field'];
      }
      if (isset($configuration['parent_group'])) {
        $defaults['parent_group'] = $configuration['parent_group'];
      }
      $form->setDefaults($defaults);
    }
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Filter/Configuration/ContactInGroupFilter.tpl";
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
    $configuration['parent_group'] = isset($submittedValues['parent_group']) ? $submittedValues['parent_group'] : false;
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

    $api_params['is_active'] = 1;
    if ($this->parent_group_id) {
      $childGroupIds = \CRM_Contact_BAO_GroupNesting::getDescendentGroupIds([$this->parent_group_id], FALSE);
      $api_params['id']['IN'] = $childGroupIds;
    }

    $form->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations);
    $form->addEntityRef( "{$fieldSpec->alias}_value", NULL, array(
      'placeholder' => E::ts('Select a group'),
      'entity' => 'Group',
      'api' => array('params' => $api_params),
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