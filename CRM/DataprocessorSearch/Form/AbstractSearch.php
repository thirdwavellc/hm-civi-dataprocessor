<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

abstract class CRM_DataprocessorSearch_Form_AbstractSearch extends CRM_Core_Form_Search {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType;
   */
  protected $dataProcessor;

  /**
   * @var int
   */
  protected $dataProcessorId;

  /**
   * @var String
   */
  protected $title;

  /**
   * @var \CRM_Dataprocessor_BAO_Output
   */
  protected $dataProcessorOutput;

  /**
   * @var int
   */
  protected $limit;

  /**
   * @var int
   */
  protected $pageId;

  /**
   * @var bool
   */
  protected $_debug = FALSE;

  /**
   * @var \CRM_Utils_Sort
   */
  protected $sort;

  /**
   * @var bool
   */
  protected $formHasRequiredFilters = FALSE;

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  abstract protected function isConfigurationValid();

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
   * Returns the name of the ID field in the dataset.
   *
   * @return string
   */
  abstract protected function getIdFieldName();

  /**
   * @return string
   */
  abstract protected function getEntityTable();

  /**
   * Builds the list of tasks or actions that a searcher can perform on a result set.
   *
   * @return array
   */
  public function buildTaskList() {
    return $this->_taskList;
  }

  /**
   * Returns whether we want to use the prevnext cache.
   * @return bool
   */
  protected function usePrevNextCache() {
    return false;
  }

  /**
   * Returns whether the ID field is Visible
   *
   * @return bool
   */
  protected function isIdFieldVisible() {
    if (isset($this->dataProcessorOutput['configuration']['hide_id_field']) && $this->dataProcessorOutput['configuration']['hide_id_field']) {
      return false;
    }
    return true;
  }

  /**
   * Return altered rows
   *
   * @param array $rows
   * @param array $ids
   *
   */
  protected function alterRows(&$rows, $ids) {

  }

  public function preProcess() {
    parent::preProcess();

    $this->findDataProcessor();

    $this->_searchButtonName = $this->getButtonName('refresh');
    $this->_actionButtonName = $this->getButtonName('next', 'action');
    $this->_done = FALSE;
    $this->defaults = [];
    // we allow the controller to set force/reset externally, useful when we are being
    // driven by the wizard framework
    $this->_debug = CRM_Utils_Request::retrieve('debug', 'Boolean', $this, FALSE);
    $this->_reset = CRM_Utils_Request::retrieve('reset', 'Boolean', CRM_Core_DAO::$_nullObject);
    $this->_force = CRM_Utils_Request::retrieve('force', 'Boolean', $this, FALSE);
    $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this, FALSE, 'search');
    $this->set('context', $this->_context);
    $this->assign("context", $this->_context);
    $this->assign('debug', $this->_debug);
    if (!empty($_POST) && !$this->controller->isModal()) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    else {
      $this->_formValues = $this->get('formValues');
    }
    if (!$this->formHasRequiredFilters || !empty($this->_formValues)) {
      $limit = CRM_Utils_Request::retrieve('crmRowCount', 'Positive', $this, FALSE, CRM_Utils_Pager::ROWCOUNT);
      $pageId = CRM_Utils_Request::retrieve('crmPID', 'Positive', $this, FALSE, 1);

      $this->addColumnHeaders();
      $this->buildRows($pageId, $limit);
    }

  }

  /**
   * Retrieve the data processor and the output configuration
   *
   * @throws \Exception
   */
  protected function findDataProcessor() {
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

      $output = CRM_Dataprocessor_BAO_Output::getValues(['id' => $dao->output_id]);
      $this->dataProcessorOutput = $output[$dao->output_id];

      if (!$this->isConfigurationValid()) {
        throw new \Exception('Invalid configuration found for the Search output of the data processor "' . $dataProcessorName . '"');
      }
    }
  }

  /**
   * Retrieve the records from the data processor
   *
   * @param $pageId
   * @param $limit
   *
   * @throws \Civi\DataProcessor\DataFlow\InvalidFlowException
   */
  protected function buildRows($pageId, $limit) {
    $rows = [];
    $ids = array();
    $prevnextData = array();

    $offset = ($pageId - 1) * $limit;
    $this->dataProcessor->getDataFlow()->setLimit($limit);
    $this->dataProcessor->getDataFlow()->setOffset($offset);
    $this->setFilters();

    // Set the sort
    $sortDirection = 'ASC';
    if (!empty($this->sort->_vars[$this->sort->getCurrentSortID()])) {
      $sortField = $this->sort->_vars[$this->sort->getCurrentSortID()];
      if ($this->sort->getCurrentSortDirection() == CRM_Utils_Sort::DESCENDING) {
        $sortDirection = 'DESC';
      }
      $this->dataProcessor->getDataFlow()->addSort($sortField['name'], $sortDirection);
    }


    $pagerParams = $this->getPagerParams();
    $pagerParams['total'] = $this->dataProcessor->getDataFlow()->recordCount();
    $pagerParams['pageID'] = $pageId;
    $this->pager = new CRM_Utils_Pager($pagerParams);
    $this->assign('pager', $this->pager);

    $id_field = $this->getIdFieldName();
    $this->assign('id_field', $id_field);

    try {
      while($record = $this->dataProcessor->getDataFlow()->nextRecord()) {
        $row = array();
        $row['id'] = $record[$id_field]->formattedValue;
        $row['checkbox'] = CRM_Core_Form::CB_PREFIX.$row['id'];
        $row['record'] = $record;
        $this->addElement('checkbox', $row['checkbox'], NULL, NULL, ['class' => 'select-row']);

        $prevnextData[] = array(
          'entity_id1' => $row['id'],
          'entity_table' => $this->getEntityTable(),
          'data' => $record,
        );
        $ids[] = $row['id'];

        $rows[$row['id']] = $row;
      }
    } catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }

    $this->alterRows($rows, $ids);

    $this->addElement('checkbox', 'toggleSelect', NULL, NULL, ['class' => 'select-rows']);
    $this->assign('rows', $rows);
    $this->assign('debug_info', $this->dataProcessor->getDataFlow()->getDebugInformation());
    if ($this->usePrevNextCache()) {
      $cacheKey = "civicrm search {$this->controller->_key}";
      CRM_DataprocessorSearch_Utils_PrevNextCache::fillWithArray($cacheKey, $prevnextData);
    }
  }

  /**
   * @param \Civi\DataProcessor\FilterHandler\AbstractFilterHandler $filter
   *
   * @return string|null
   */
  public function setDateFilter(\Civi\DataProcessor\FilterHandler\AbstractFilterHandler $filter) {
    $filterName = $filter->getFieldSpecification()->alias;
    $type = $filter->getFieldSpecification()->type;
    $relative = CRM_Utils_Array::value("{$filterName}_relative", $this->_formValues);
    $from = CRM_Utils_Array::value("{$filterName}_from", $this->_formValues);
    $to = CRM_Utils_Array::value("{$filterName}_to", $this->_formValues);
    $fromTime = CRM_Utils_Array::value("{$filterName}_from_time", $this->_formValues);
    $toTime = CRM_Utils_Array::value("{$filterName}_to_time", $this->_formValues);

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

  protected function setFilters() {
    if ($this->dataProcessor->getFilterHandlers()) {
      foreach ($this->dataProcessor->getFilterHandlers() as $filter) {
        $isFilterSet = FALSE;
        $filterSpec = $filter->getFieldSpecification();
        $filterName = $filterSpec->alias;
        if ($filterSpec->type == 'Date') {
          $isFilterSet = $this->setDateFilter($filter);
        }
        elseif (isset($this->_formValues[$filterName . '_op'])) {
          switch ($this->_formValues[$filterName . '_op']) {
            case 'IN':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'IN',
                  'value' => $this->_formValues[$filterName . '_value'],
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'NOT IN':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'NOT IN',
                  'value' => $this->_formValues[$filterName . '_value'],
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
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => $this->_formValues[$filterName . '_op'],
                  'value' => $this->_formValues[$filterName . '_value'],
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'has':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => '%' . $this->_formValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'nhas':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'NOT LIKE',
                  'value' => '%' . $this->_formValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'sw':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => $this->_formValues[$filterName . '_value'] . '%',
                ];
                $filter->setFilter($filterParams);
                $isFilterSet = TRUE;
              }
              break;
            case 'ew':
              if (isset($this->_formValues[$filterName . '_value']) && $this->_formValues[$filterName . '_value']) {
                $filterParams = [
                  'op' => 'LIKE',
                  'value' => '%' . $this->_formValues[$filterName . '_value'],
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
   * Add the headers for the columns
   *
   * @throws \Civi\DataProcessor\DataFlow\InvalidFlowException
   */
  protected function addColumnHeaders() {
    $sortFields = array();
    $id_field = $this->getIdFieldName();
    $idFieldVisible = $this->isIdFieldVisible();
    $columnHeaders = array();
    $sortColumnNr = 1;
    foreach($this->dataProcessor->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $hiddenField = true;
      if ($field->alias != $id_field) {
        $hiddenField = false;
      } elseif ($field->alias == $id_field && $idFieldVisible) {
        $hiddenField = false;
      }
      if (!$hiddenField) {
        $columnHeaders[$field->alias] = $field->title;
        $sortFields[$sortColumnNr] = array(
          'name' => $field->title,
          'sort' => $field->alias,
          'direction' => CRM_Utils_Sort::DONTCARE,
        );
        $sortColumnNr++;
      }
    }
    $this->assign('columnHeaders', $columnHeaders);

    $this->sort = new CRM_Utils_Sort($sortFields);
    $this->assign_by_ref('sort', $this->sort);
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
        if ($filterHandler->isRequired()) {
          $this->formHasRequiredFilters = TRUE;
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

  /**
   * @return array
   */
  protected function getPagerParams() {
    $params = [];
    $params['total'] = 0;
    $params['status'] =E::ts('%%StatusMessage%%');
    $params['csvString'] = NULL;
    $params['rowCount'] =  CRM_Utils_Pager::ROWCOUNT;
    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
    return $params;
  }

  public function buildQuickform() {
    parent::buildQuickform();

    $this->buildCriteriaForm();

    $selectedIds = array();
    $qfKeyParam = CRM_Utils_Array::value('qfKey', $this->_formValues);
    // We use ajax to handle selections only if the search results component_mode is set to "contacts"
    if ($qfKeyParam && $this->usePrevNextCache()) {
      $this->addClass('crm-ajax-selection-form');
        $qfKeyParam = "civicrm search {$qfKeyParam}";
        $selectedIdsArr = CRM_DataprocessorSearch_Utils_PrevNextCache::getSelection($qfKeyParam);
        $selectedIds = array_keys($selectedIdsArr[$qfKeyParam]);
    }

    $this->assign_by_ref('selectedIds', $selectedIds);
    $this->add('hidden', 'context');
    $this->add('hidden', CRM_Utils_Sort::SORT_ID);
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $defaults['context'] = 'search';
    if ($this->sort && $this->sort->getCurrentSortID()) {
      $defaults[CRM_Utils_Sort::SORT_ID] = $this->sort->getCurrentSortID();
    }
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if ($this->_done) {
      return;
    }
    $this->_done = TRUE;

    //for prev/next pagination
    $crmPID = CRM_Utils_Request::retrieve('crmPID', 'Integer');

    if (array_key_exists($this->_searchButtonName, $_POST) ||
      ($this->_force && !$crmPID)
    ) {
      //reset the cache table for new search
      $cacheKey = "civicrm search {$this->controller->_key}";
      CRM_DataprocessorSearch_Utils_PrevNextCache::deleteItem(NULL, $cacheKey);
    }

    if (!empty($_POST)) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    $this->set('formValues', $this->_formValues);
    $buttonName = $this->controller->getButtonName();
    if ($buttonName == $this->_actionButtonName) {
      // check actionName and if next, then do not repeat a search, since we are going to the next page
      // hack, make sure we reset the task values
      $formName = $this->controller->getStateMachine()->getTaskFormName();
      $this->controller->resetPage($formName);
      return;
    }
  }

  /**
   * Return a descriptive name for the page, used in wizard header
   *
   * @return string
   */
  public function getTitle() {
    $this->findDataProcessor();
    return $this->dataProcessorOutput['configuration']['title'];
  }

}