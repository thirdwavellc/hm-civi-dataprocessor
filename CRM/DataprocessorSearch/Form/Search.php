<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_DataprocessorSearch_Form_Search extends CRM_Core_Form_Search {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType;
   */
  protected $dataProcessor;

  protected $dataProcessorId;

  /**
   * @var \CRM_Dataprocessor_BAO_Output
   */
  protected $dataProcessorOutput;

  protected $limit;

  protected $pageId;

  /**
   * @var \CRM_Utils_Sort
   */
  protected $sort;

  public function preProcess() {
    parent::preProcess();

    $this->findDataProcessor();

    $this->_searchButtonName = $this->getButtonName('refresh');
    $this->_actionButtonName = $this->getButtonName('next', 'action');
    $this->_done = FALSE;
    $this->defaults = [];
    // we allow the controller to set force/reset externally, useful when we are being
    // driven by the wizard framework
    $this->_reset = CRM_Utils_Request::retrieve('reset', 'Boolean', CRM_Core_DAO::$_nullObject);
    $this->_force = CRM_Utils_Request::retrieve('force', 'Boolean', $this, FALSE);
    $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this, FALSE, 'search');
    $this->set('context', $this->_context);
    $this->assign("context", $this->_context);
    if (!empty($_POST) && !$this->controller->isModal()) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    else {
      $this->_formValues = $this->get('formValues');
    }
    if (!empty($this->_formValues)) {
      $limit = CRM_Utils_Request::retrieve('crmRowCount', 'Positive', $this, FALSE, CRM_Utils_Pager::ROWCOUNT);
      $pageId = CRM_Utils_Request::retrieve('crmPID', 'Positive', $this, FALSE, 1);

      $this->addColumnHeaders();
      $this->findRows($pageId, $limit);
    }

  }

  /**
   * Retrieve the data processor and the output configuration
   *
   * @throws \Exception
   */
  protected function findDataProcessor() {
    $dataProcessorId = str_replace('civicrm/dataprocessor_search/', '', CRM_Utils_System::getUrlPath());
    $sql = "
      SELECT civicrm_data_processor.id as data_processor_id,  civicrm_data_processor_output.id AS output_id
      FROM civicrm_data_processor 
      INNER JOIN civicrm_data_processor_output ON civicrm_data_processor.id = civicrm_data_processor_output.data_processor_id
      WHERE is_active = 1 AND civicrm_data_processor.id = %1 AND civicrm_data_processor_output.type = 'search'
    ";
    $params[1] = array($dataProcessorId, 'Integer');
    $dao = CRM_Dataprocessor_BAO_DataProcessor::executeQuery($sql, $params, TRUE, 'CRM_Dataprocessor_BAO_DataProcessor');
    if (!$dao->fetch()) {
      throw new \Exception('Could not find Data Processor with id '.$dataProcessorId);
    }
    $this->dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($dao->data_processor_id);

    $output = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $dao->output_id));
    $this->dataProcessorOutput = $output[$dao->output_id];

    if (!isset($this->dataProcessorOutput['configuration']['contact_id_field'])) {
      throw new \Exception('Invalid configuration found for the Search output of the data processor (id = '.$dataProcessorId.')');
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
  protected function findRows($pageId, $limit) {
    $rows = [];
    $contact_ids = array();
    $prevnextData = array();

    $offset = ($pageId - 1) * $limit;
    $this->setFilters();
    $this->dataProcessor->getDataFlow()->setLimit($limit);
    $this->dataProcessor->getDataFlow()->setOffset($offset);

    // Set the sort
    $sortField = '';
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

    $contact_id_field = $this->dataProcessorOutput['configuration']['contact_id_field'];
    $this->assign('contact_id_field', $contact_id_field);

    try {
      while($record = $this->dataProcessor->getDataFlow()->nextRecord()) {
        $row = array();
        $row['contact_id'] = $record[$contact_id_field]->formattedValue;
        $row['checkbox'] = CRM_Core_Form::CB_PREFIX.$row['contact_id'];
        $row['record'] = $record;
        $this->addElement('checkbox', $row['checkbox'], NULL, NULL, ['class' => 'select-row']);

        $prevnextData[] = array(
          'entity_id1' => $row['contact_id'],
          'data' => $record,
        );
        $contact_ids[] = $row['contact_id'];

        $rows[$row['contact_id']] = $row;
      }
    } catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }

    // Add the contact type image
    if (count($contact_ids)) {
      $contactDao = CRM_Core_DAO::executeQuery("SELECT id, contact_type, contact_sub_type FROM civicrm_contact WHERE `id` IN (".implode(",", $contact_ids).")");
      while($contactDao->fetch()) {
        $rows[$contactDao->id]['contact_type'] = CRM_Contact_BAO_Contact_Utils::getImage($contactDao->contact_sub_type ? $contactDao->contact_sub_type : $contactDao->contact_type,
          FALSE,
          $contactDao->id
        );
      }
    }

    $this->addElement('checkbox', 'toggleSelect', NULL, NULL, ['class' => 'select-rows']);

    $cacheKey = "civicrm search {$this->controller->_key}";
    Civi::service('prevnext')->fillWithArray($cacheKey, $prevnextData);
    $this->assign('rows', $rows);
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
    foreach($this->dataProcessor->getFilterHandlers() as $filter) {
      $isFilterSet = false;
      $filterSpec = $filter->getFieldSpecification();
      $filterName = $filterSpec->alias;
      if ($filterSpec->type == 'Date') {
        $isFilterSet = $this->setDateFilter($filter);
      } elseif (isset($this->_formValues[$filterName.'_op'])) {
        switch ($this->_formValues[$filterName . '_op']) {
          case 'IN':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'IN',
                'value' => $this->_formValues[$filterSpec->alias . '_value'],
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
          case 'NOT IN':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'NOT IN',
                'value' => $this->_formValues[$filterSpec->alias . '_value'],
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
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => $this->_formValues[$filterSpec->alias . '_op'],
                'value' => $this->_formValues[$filterSpec->alias . '_value'],
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
          case 'has':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'LIKE',
                'value' => '%'.$this->_formValues[$filterSpec->alias . '_value'].'%',
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
          case 'nhas':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'NOT LIKE',
                'value' => '%'.$this->_formValues[$filterSpec->alias . '_value'].'%',
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
          case 'sw':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'LIKE',
                'value' => $this->_formValues[$filterSpec->alias . '_value'].'%',
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
          case 'ew':
            if (isset($this->_formValues[$filterSpec->alias . '_value']) && $this->_formValues[$filterSpec->alias . '_value']) {
              $filterParams = [
                'op' => 'LIKE',
                'value' => '%'.$this->_formValues[$filterSpec->alias . '_value'],
              ];
              $filter->setFilter($filterParams);
              $isFilterSet = TRUE;
            }
            break;
        }
      }

      if ($filter->isRequired() && !$isFilterSet) {
        throw new \Exception('Field '.$filterSpec->title.' is required');
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
    $contact_id_field = $this->dataProcessorOutput['configuration']['contact_id_field'];
    $columnHeaders = array();
    $sortColumnNr = 1;
    foreach($this->dataProcessor->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      if ($field->alias != $contact_id_field) {
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

  protected function buildCriteriaForm() {
    $count = 1;
    $filterElements = array();
    $types = \CRM_Utils_Type::getValidTypes();
    foreach($this->dataProcessor->getFilterHandlers() as $filterHandler) {
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
        $element = $this->addElement('select', "{$fieldSpec->alias}_op", ts('Operator:'), $operations);
        $this->addElement('select', "{$fieldSpec->alias}_value", NULL, $fieldSpec->getOptions(), array(
          'style' => 'min-width:250px',
          'class' => 'crm-select2 huge',
          'multiple' => TRUE,
          'placeholder' => ts('- select -'),
        ));
      } else {
        switch ($type) {
          case \CRM_Utils_Type::T_DATE:
            CRM_Core_Form_Date::buildDateRange($this, $fieldSpec->alias, $count, '_from', '_to', ts('From:'), $filterHandler->isRequired(), $operations);
            $count ++;
            break;
          case CRM_Report_Form::OP_INT:
          case CRM_Report_Form::OP_FLOAT:
            // and a min value input box
            $this->add('text', "{$fieldSpec->alias}_min", ts('Min'));
            // and a max value input box
            $this->add('text', "{$fieldSpec->alias}_max", ts('Max'));
          default:
            // default type is string
            $this->addElement('select', "{$fieldSpec->alias}_op", ts('Operator:'), $operations,
              array('onchange' => "return showHideMaxMinVal( '$fieldSpec->alias', this.value );")
            );
            // we need text box for value input
            $this->add('text', "{$fieldSpec->alias}_value", NULL, array('class' => 'huge'));
            break;
        }
      }
      $filterElements[$fieldSpec->alias] = $filter;
    }
    $this->assign('filters', $filterElements);
  }

  protected function getOperatorOptions(\Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpec) {
    if ($fieldSpec->getOptions()) {
      return array(
        'IN' => ts('Is one of'),
        'NOT IN' => ts('Is not one of'),
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
          '=' => ts('Is equal to'),
          '<=' => ts('Is less than or equal to'),
          '>=' => ts('Is greater than or equal to'),
          '<' => ts('Is less than'),
          '>' => ts('Is greater than'),
          '!=' => ts('Is not equal to'),
        );
        break;
    }
    return array(
      '=' => ts('Is equal to'),
      '!=' => ts('Is not equal to'),
      'has' => ts('Contains'),
      'sw' => ts('Starts with'),
      'ew' => ts('Ends with'),
      'nhas' => ts('Does not contain'),
    );
  }

  /**
   * @return array
   */
  protected function getPagerParams() {
    $params = [];
    $params['total'] = 0;
    $params['status'] = ts('%%StatusMessage%%');
    $params['csvString'] = NULL;
    $params['rowCount'] =  CRM_Utils_Pager::ROWCOUNT;
    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
    return $params;
  }

  public function buildQuickform() {
    parent::buildQuickform();

    $this->buildCriteriaForm();

    $selectedContactIds = array();
    $qfKeyParam = CRM_Utils_Array::value('qfKey', $this->_formValues);
    // We use ajax to handle selections only if the search results component_mode is set to "contacts"
    if ($qfKeyParam) {
      $this->addClass('crm-ajax-selection-form');
      $qfKeyParam = "civicrm search {$qfKeyParam}";
      $selectedContactIdsArr = Civi::service('prevnext')->getSelection($qfKeyParam);
      $selectedContactIds = array_keys($selectedContactIdsArr[$qfKeyParam]);
    }

    $this->assign_by_ref('selectedContactIds', $selectedContactIds);
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

  /**
   * Builds the list of tasks or actions that a searcher can perform on a result set.
   *
   * @return array
   */
  public function buildTaskList() {
    $taskParams['deletedContacts'] = FALSE;
    $this->_taskList += CRM_Contact_Task::permissionedTaskTitles(CRM_Core_Permission::getPermission(), $taskParams);
    return $this->_taskList;
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
      Civi::service('prevnext')->deleteItem(NULL, $cacheKey);
    }

    if (!empty($_POST)) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    $this->set('formValues', $this->_formValues);
    $buttonName = $this->controller->getButtonName();
    if ($buttonName == $this->_actionButtonName) {
      // check actionName and if next, then do not repeat a search, since we are going to the next page
      // hack, make sure we reset the task values
      $stateMachine = $this->controller->getStateMachine();
      $formName = $stateMachine->getTaskFormName();
      $this->controller->resetPage($formName);
      return;
    }
  }
}