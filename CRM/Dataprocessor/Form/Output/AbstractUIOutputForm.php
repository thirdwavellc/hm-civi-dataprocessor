<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

abstract class CRM_Dataprocessor_Form_Output_AbstractUIOutputForm extends CRM_Core_Form_Search {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType;
   */
  protected $dataProcessor;

  /**
   * @var int
   */
  protected $dataProcessorId;

  /**
   * @var array
   */
  protected $dataProcessorBAO;

  /**
   * @var \CRM_Dataprocessor_BAO_Output
   */
  protected $dataProcessorOutput;

  /**
   * Return the data processor ID
   *
   * @return String
   */
  abstract protected function getDataProcessorName();

  /**
   * Returns the name of the output for this search
   *
   * @return string
   */
  abstract protected function getOutputName();

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  abstract protected function isConfigurationValid();

  public function preProcess() {
    parent::preProcess();
    $this->loadDataProcessor();
  }

  /**
   * Retrieve the data processor and the output configuration
   *
   * @throws \Exception
   */
  protected function loadDataProcessor() {
    if (!$this->dataProcessorId) {
      $dataProcessorName = $this->getDataProcessorName();
      $sql = "
        SELECT civicrm_data_processor.id as data_processor_id,  civicrm_data_processor_output.id AS output_id
        FROM civicrm_data_processor 
        INNER JOIN civicrm_data_processor_output ON civicrm_data_processor.id = civicrm_data_processor_output.data_processor_id
        WHERE is_active = 1 AND civicrm_data_processor.name = %1 AND civicrm_data_processor_output.type = %2
      ";
      $params[1] = [$dataProcessorName, 'String'];
      $params[2] = [$this->getOutputName(), 'String'];
      $dao = CRM_Dataprocessor_BAO_DataProcessor::executeQuery($sql, $params, TRUE, 'CRM_Dataprocessor_BAO_DataProcessor');
      if (!$dao->fetch()) {
        throw new \Exception('Could not find Data Processor "' . $dataProcessorName.'"');
      }
      $this->dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($dao->data_processor_id);
      $this->dataProcessorId = $dao->data_processor_id;

      $dataProcessorBAO = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
      $this->dataProcessorBAO = $dataProcessorBAO[$this->dataProcessorId];

      $output = CRM_Dataprocessor_BAO_Output::getValues(['id' => $dao->output_id]);
      $this->dataProcessorOutput = $output[$dao->output_id];
      $this->assign('output', $this->dataProcessorOutput);

      if (!$this->isConfigurationValid()) {
        throw new \Exception('Invalid configuration found of the data processor "' . $dataProcessorName . '"');
      }
    }
  }

  /**
   * Returns whether the search has required filters.
   *
   * @return bool
   */
  protected function hasRequiredFilters() {
    if ($this->dataProcessor->getFilterHandlers()) {
      foreach ($this->dataProcessor->getFilterHandlers() as $filter) {
        if ($filter->isRequired()) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Validate the filters
   *
   * @return array
   */
  protected function validateFilters() {
    $errors = array();
    if ($this->dataProcessor->getFilterHandlers()) {
      foreach ($this->dataProcessor->getFilterHandlers() as $filter) {
        if ($filter->isRequired()) {
          $isFilterSet = FALSE;
          $filterSpec = $filter->getFieldSpecification();
          $filterName = $filterSpec->alias;
          if ($filterSpec->type == 'Date') {
            $relative = CRM_Utils_Array::value("{$filterName}_relative", $this->_formValues);
            $from = CRM_Utils_Array::value("{$filterName}_from", $this->_formValues);
            $to = CRM_Utils_Array::value("{$filterName}_to", $this->_formValues);
            $fromTime = CRM_Utils_Array::value("{$filterName}_from_time", $this->_formValues);
            $toTime = CRM_Utils_Array::value("{$filterName}_to_time", $this->_formValues);

            list($from, $to) = CRM_Utils_Date::getFromTo($relative, $from, $to, $fromTime, $toTime);
            if (!$from && !$to) {
              $errors[$filterName . '_relative'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
            }
          }
          elseif (!isset($this->_formValues[$filterName . '_op']) || !(isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value'])) {
            $errors[$filterName . '_value'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
          }
        }
      }
    }
    return $errors;
  }

  /**
   * Apply the filters to the database processor
   *
   * @throws \Exception
   */
  public static function applyFilters(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor, $submittedValues) {
    if ($dataProcessor->getFilterHandlers()) {
      foreach ($dataProcessor->getFilterHandlers() as $filter) {
        $isFilterSet = FALSE;
        $filterSpec = $filter->getFieldSpecification();
        $filterName = $filterSpec->alias;

        foreach($submittedValues as $k => $v) {
          if (strpos($k, $filterName) === 0) {
            $filterParamSet[$k] = $v;
          }
        }

        if ($filterSpec->type == 'Date') {
          $isFilterSet = self::applyDateFilter($filter, $submittedValues);
        }
        elseif (isset($submittedValues[$filterName . '_op'])) {
          switch ($submittedValues[$filterName . '_op']) {
            case 'IN':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'IN',
                  'value' => $submittedValues[$filterName . '_value'],
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'NOT IN':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'NOT IN',
                  'value' => $submittedValues[$filterName . '_value'],
                ];
                $filter->setFilter($filterParams);
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
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'has':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => '%' . $submittedValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'nhas':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'NOT LIKE',
                  'value' => '%' . $submittedValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'sw':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => $submittedValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'ew':
              if (isset($submittedValues[$filterName . '_value']) && $submittedValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => '%' . $submittedValues[$filterName . '_value'],
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
          }
        }
        if ($filter->isRequired() && !$isFilterSet) {
          throw new \Exception('Field ' . $filterSpec->title . ' is required');
        }
      }
    }
  }

  /**
   * @param \Civi\DataProcessor\FilterHandler\AbstractFilterHandler $filter
   * @param array $submittedValues
   * @return string|null
   */
  protected static function applyDateFilter(\Civi\DataProcessor\FilterHandler\AbstractFilterHandler $filter, $submittedValues) {
    $filterName = $filter->getFieldSpecification()->alias;
    $type = $filter->getFieldSpecification()->type;
    $relative = CRM_Utils_Array::value("{$filterName}_relative", $submittedValues);
    $from = CRM_Utils_Array::value("{$filterName}_from", $submittedValues);
    $to = CRM_Utils_Array::value("{$filterName}_to", $submittedValues);
    $fromTime = CRM_Utils_Array::value("{$filterName}_from_time", $submittedValues);
    $toTime = CRM_Utils_Array::value("{$filterName}_to_time", $submittedValues);

    list($from, $to) = CRM_Utils_Date::getFromTo($relative, $from, $to, $fromTime, $toTime);
    if ($from && $to) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $filter->setFilter(array(
        'op' => 'BETWEEN',
        'value' => array($from, $to),
      ));
      return TRUE;
    } elseif ($from) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $filter->setFilter(array(
        'op' => '>=',
        'value' => $from,
      ));
      return TRUE;
    } elseif ($to) {
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $filter->setFilter(array(
        'op' => '<=',
        'value' => $to,
      ));
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Build the criteria form
   */
  protected function buildCriteriaForm() {
    $count = 1;
    $filterElements = array();
    $types = \CRM_Utils_Type::getValidTypes();
    if ($this->dataProcessor->getFilterHandlers()) {
      foreach ($this->dataProcessor->getFilterHandlers() as $filterHandler) {
        $fieldSpec = $filterHandler->getFieldSpecification();
        $type = \CRM_Utils_Type::T_STRING;
        if (isset($types[$fieldSpec->type])) {
          $type = $types[$fieldSpec->type];
        }
        if (!$fieldSpec) {
          continue;
        }
        $filter['title'] = $fieldSpec->title;
        if ($filterHandler->isRequired()) {
          $filter['title'] .= ' <span class="crm-marker">*</span>';
        }
        $filter['type'] = $fieldSpec->type;
        $operations = $this->getOperatorOptions($fieldSpec);
        if ($fieldSpec->getOptions()) {
          $element = $this->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations);
          $this->addElement('select', "{$fieldSpec->alias}_value", NULL, $fieldSpec->getOptions(), [
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
              CRM_Core_Form_Date::buildDateRange($this, $fieldSpec->alias, $count, '_from', '_to', E::ts('From:'), $filterHandler->isRequired(), $operations);
              $count++;
              break;
            case CRM_Report_Form::OP_INT:
            case CRM_Report_Form::OP_FLOAT:
              // and a min value input box
              $this->add('text', "{$fieldSpec->alias}_min", E::ts('Min'));
              // and a max value input box
              $this->add('text', "{$fieldSpec->alias}_max", E::ts('Max'));
            default:
              // default type is string
              $this->addElement('select', "{$fieldSpec->alias}_op", E::ts('Operator:'), $operations,
                ['onchange' => "return showHideMaxMinVal( '$fieldSpec->alias', this.value );"]
              );
              // we need text box for value input
              $this->add('text', "{$fieldSpec->alias}_value", NULL, ['class' => 'huge']);
              break;
          }
        }
        $filterElements[$fieldSpec->alias] = $filter;
      }
      $this->assign('filters', $filterElements);
    }
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


}