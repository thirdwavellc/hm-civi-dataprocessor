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
use Civi\DataProcessor\Exception\InvalidConfigurationException;
use Civi\DataProcessor\Source\SourceInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class InApiFilter extends AbstractFilterHandler {

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

    $apiEntity = $configuration['api_entity'];
    $apiAction = $configuration['api_action'];
    $apiParams = isset($configuration['api_params']) && is_array($configuration['api_params'])? $configuration['api_params'] : array();
    $id_field = $configuration['value_field'];
    $label_field = $configuration['label_field'];
    $apiParams['return'] = array($id_field, $label_field);
    $apiParams['options']['limit'] = 0;
    unset($apiParams['options']['offset']);
    $this->fieldSpecification->options = array();
    $apiResult = civicrm_api3($apiEntity, $apiAction, $apiParams);
    foreach ($apiResult['values'] as $option) {
      if (isset($option[$id_field]) && isset($option[$label_field])) {
        $this->fieldSpecification->options[$option[$id_field]] = $option[$label_field];
      }
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
    if ($dataFlow && $dataFlow instanceof SqlDataFlow) {
      $value = $filter['value'];
      if (!is_array($value)) {
        $value = explode(",", $value);
      }
      $whereClause = new SqlDataFlow\SimpleWhereClause($dataFlow->getName(), $this->fieldSpecification->name, $filter['op'], $value, $this->fieldSpecification->type);
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

    $form->add('select', 'field', E::ts('Contact ID Field'), $fieldSelect, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge data-processor-field-for-name',
      'placeholder' => E::ts('- select -'),
    ));

    $form->add('text', 'api_entity', E::ts('API Entity'), array('style' => 'min-width:250px', 'class' => 'huge'), true);

    $form->add('text', 'api_action', E::ts('API Action'), array('style' => 'min-width:250px', 'class' => 'huge'), true);

    $form->add('text', 'value_field', E::ts('Value Field'), array('style' => 'min-width:250px', 'class' => 'huge'), true);

    $form->add('text', 'label_field', E::ts('Label Field'), array('style' => 'min-width:250px', 'class' => 'huge'), true);

    $form->add('textarea', 'api_params', E::ts('API Params'), array('style' => 'min-width:250px', 'class' => 'huge'), false);


    if (isset($filter['configuration'])) {
      $configuration = $filter['configuration'];
      $defaults = array();
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        $defaults['field'] = $configuration['datasource'] . '::' . $configuration['field'];
      }
      $defaults['api_entity'] = $configuration['api_entity'];
      $defaults['api_action'] = $configuration['api_action'];
      $defaults['value_field'] = $configuration['value_field'];
      $defaults['label_field'] = $configuration['label_field'];
      $defaults['api_params'] = is_array($configuration['api_params']) ? json_encode($configuration['api_params'], JSON_PRETTY_PRINT) : '';
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
    return "CRM/Dataprocessor/Form/Filter/Configuration/InApiFilter.tpl";
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
    $configuration['api_entity'] = $submittedValues['api_entity'];
    $configuration['api_action'] = $submittedValues['api_action'];
    $configuration['value_field'] = $submittedValues['value_field'];
    $configuration['label_field'] = $submittedValues['label_field'];
    $configuration['api_params'] = !empty($submittedValues['api_params']) ? json_decode($submittedValues['api_params'], true) : null;
    return $configuration;
  }


}