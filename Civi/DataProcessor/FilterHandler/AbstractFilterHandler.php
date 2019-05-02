<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

abstract class AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  protected $data_processor;

  /**
   * @var bool
   */
  protected $is_required;

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  abstract public function getFieldSpecification();

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param bool $is_required
   * @param array $configuration
   */
  abstract public function initialize($alias, $title, $is_required, $configuration);

  /**
   * @param array $filterParams
   *   The filter settings
   * @return mixed
   */
  abstract public function setFilter($filterParams);

  public function __construct() {

  }

  public function setDataProcessor(AbstractProcessorType $dataProcessor) {
    $this->data_processor = $dataProcessor;
  }

  public function isRequired() {
    return $this->is_required;
  }

  /**
   * Returns true when this filter has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return false;
  }

  /**
   * When this filter type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $filter
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $filter=array()) {
    // Example add a checkbox to the form.
    // $form->add('checkbox', 'show_label', E::ts('Show label'));
  }

  /**
   * When this filter type has configuration specify the template file name
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

  /**
   * File name of the template to add this filter to the criteria form.
   *
   * @return string
   */
  public function getTemplateFileName() {
    return "CRM/Dataprocessor/Form/Filter/GenericFilter.tpl";
  }

  /**
   * Validate the submitted filter parameters.
   *
   * @param $submittedValues
   * @return array
   */
  public function validateSubmittedFilterParams($submittedValues) {
    $errors = array();
    if ($this->isRequired()) {
      $filterSpec = $this->getFieldSpecification();
      $filterName = $filterSpec->alias;
      if ($filterSpec->type == 'Date' || $filterSpec->type == 'Timestamp') {
        $relative = \CRM_Utils_Array::value("{$filterName}_relative", $submittedValues);
        $from = \CRM_Utils_Array::value("{$filterName}_from", $submittedValues);
        $to = \CRM_Utils_Array::value("{$filterName}_to", $submittedValues);
        $fromTime = \CRM_Utils_Array::value("{$filterName}_from_time", $submittedValues);
        $toTime = \CRM_Utils_Array::value("{$filterName}_to_time", $submittedValues);

        list($from, $to) = \CRM_Utils_Date::getFromTo($relative, $from, $to, $fromTime, $toTime);
        if (!$from && !$to) {
          $errors[$filterName . '_relative'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
        }
      }
      elseif (!isset($submittedValues[$filterName . '_op']) || !(isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value'])) {
        $errors[$filterName . '_value'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
      }
    }
    return $errors;
  }

  /**
   * Apply the submitted filter
   *
   * @param $submittedValues
   * @throws \Exception
   */
  public function applyFilterFromSubmittedFilterParams($submittedValues) {
    $isFilterSet = FALSE;
    $filterSpec = $this->getFieldSpecification();
    $filterName = $filterSpec->alias;
    if ($filterSpec->type == 'Date' || $filterSpec->type == 'Timestamp') {
      $isFilterSet = $this->applyDateFilter($submittedValues);
    }
    elseif (isset($submittedValues[$filterName . '_op'])) {
      switch ($submittedValues[$filterName . '_op']) {
        case 'IN':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'IN',
              'value' => $submittedValues[$filterName . '_value'],
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case 'NOT IN':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'NOT IN',
              'value' => $submittedValues[$filterName . '_value'],
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case '=':
        case '!=':
        case '>':
        case '<':
        case '>=':
        case '<=':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => $submittedValues[$filterName . '_op'],
              'value' => $submittedValues[$filterName . '_value'],
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case 'has':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'LIKE',
              'value' => '%' . $submittedValues[$filterName . '_value'] . '%',
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case 'nhas':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'NOT LIKE',
              'value' => '%' . $submittedValues[$filterName . '_value'] . '%',
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case 'sw':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'LIKE',
              'value' => $submittedValues[$filterName . '_value'] . '%',
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
        case 'ew':
          if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
            $filterParams = [
              'op' => 'LIKE',
              'value' => '%' . $submittedValues[$filterName . '_value'],
            ];
            $this->setFilter($filterParams);
            $isFilterSet = TRUE;
          }
          break;
      }
    }
    if ($this->isRequired() && !$isFilterSet) {
      throw new \Exception('Field ' . $filterSpec->title . ' is required');
    }
  }

  /**
   * Add the elements to the filter form.
   *
   * @param \CRM_Core_Form $form
   * @return array
   *   Return variables belonging to this filter.
   */
  public function addToFilterForm(\CRM_Core_Form $form) {
    static $count = 1;
    $types = \CRM_Utils_Type::getValidTypes();
    $fieldSpec = $this->getFieldSpecification();
    $operations = $this->getOperatorOptions($fieldSpec);
    $type = \CRM_Utils_Type::T_STRING;

    $title = $fieldSpec->title;
    if ($this->isRequired()) {
      $title .= ' <span class="crm-marker">*</span>';
    }

    if (isset($types[$fieldSpec->type])) {
      $type = $types[$fieldSpec->type];
    }
    if ($fieldSpec->getOptions()) {
      $form->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations);
      $form->addElement('select', "{$fieldSpec->alias}_value", NULL, $fieldSpec->getOptions(), [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'multiple' => TRUE,
        'placeholder' => E::ts('- select -'),
      ]);
    }
    else {
      switch ($type) {
        case \CRM_Utils_Type::T_DATE:
        case \CRM_Utils_Type::T_TIMESTAMP:
          \CRM_Core_Form_Date::buildDateRange($form, $fieldSpec->alias, $count, '_from', '_to', E::ts('From:'), $this->isRequired(), $operations);
          $count ++;
          break;
        case \CRM_Utils_Type::T_INT:
        case \CRM_Utils_Type::T_FLOAT:
          // and a min value input box
          $form->add('text', "{$fieldSpec->alias}_min", E::ts('Min'));
          // and a max value input box
          $form->add('text', "{$fieldSpec->alias}_max", E::ts('Max'));
        default:
          // default type is string
          $form->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations,
            ['onchange' => "return showHideMaxMinVal( '$fieldSpec->alias', this.value );"]
          );
          // we need text box for value input
          $form->add('text', "{$fieldSpec->alias}_value", NULL, ['class' => 'huge']);
          break;
      }
    }

    $filter['type'] = $fieldSpec->type;
    $filter['title'] = $title;

    return $filter;
  }

  protected function getOperatorOptions(\Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpec) {
    if ($fieldSpec->getOptions()) {
      return array(
        'IN' => E::ts('Is one of'),
        'NOT IN' => E::ts('Is not one of'),
      );
    }
    $types = \CRM_Utils_Type::getValidTypes();
    $type = \CRM_Utils_Type::T_STRING;
    if (isset($types[$fieldSpec->type])) {
      $type = $types[$fieldSpec->type];
    }
    switch ($type) {
      case \CRM_Utils_Type::T_DATE:
        return array();
        break;
      case \CRM_Utils_Type::T_INT:
      case \CRM_Utils_Type::T_FLOAT:
        return array(
          '=' => E::ts('Is equal to'),
          '<=' => E::ts('Is less than or equal to'),
          '>=' => E::ts('Is greater than or equal to'),
          '<' => E::ts('Is less than'),
          '>' => E::ts('Is greater than'),
          '!=' => E::ts('Is not equal to'),
        );
        break;
    }
    return array(
      '=' => E::ts('Is equal to'),
      '!=' => E::ts('Is not equal to'),
      'has' => E::ts('Contains'),
      'sw' => E::ts('Starts with'),
      'ew' => E::ts('Ends with'),
      'nhas' => E::ts('Does not contain'),
    );
  }

  /**
   * @param array $submittedValues
   * @return string|null
   */
  protected function applyDateFilter($submittedValues) {
    $filterName = $this->getFieldSpecification()->alias;
    $type = $this->getFieldSpecification()->type;
    $relative = \CRM_Utils_Array::value("{$filterName}_relative", $submittedValues);
    $from = \CRM_Utils_Array::value("{$filterName}_from", $submittedValues);
    $to = \CRM_Utils_Array::value("{$filterName}_to", $submittedValues);
    $fromTime = \CRM_Utils_Array::value("{$filterName}_from_time", $submittedValues);
    $toTime = \CRM_Utils_Array::value("{$filterName}_to_time", $submittedValues);

    list($from, $to) = \CRM_Utils_Date::getFromTo($relative, $from, $to, $fromTime, $toTime);
    if ($from && $to) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $this->setFilter(array(
        'op' => 'BETWEEN',
        'value' => array($from, $to),
      ));
      return TRUE;
    } elseif ($from) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $this->setFilter(array(
        'op' => '>=',
        'value' => $from,
      ));
      return TRUE;
    } elseif ($to) {
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $this->setFilter(array(
        'op' => '<=',
        'value' => $to,
      ));
      return TRUE;
    }
    return FALSE;
  }

}