<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

abstract class CRM_DataprocessorSearch_Form_AbstractSearch extends CRM_Dataprocessor_Form_Output_AbstractUIOutputForm {

  /**
   * @var String
   */
  protected $title;

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
   * @var array
   */
  protected $_appliedFilters;

  /**
   * @var string
   */
  protected $currentUrl;

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
   * Returns the url for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  abstract protected function link($row);

  /**
   * Returns the link text for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  abstract protected function linkText($row);

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

    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $this);
    $urlPath = CRM_Utils_System::getUrlPath();
    $urlParams = 'force=1';
    if ($qfKey) {
      $urlParams .= "&qfKey=$qfKey";
    }
    $this->currentUrl = CRM_Utils_System::url($urlPath, $urlParams);
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($this->currentUrl);

    if (!empty($_POST) && !$this->controller->isModal()) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    else {
      $this->_formValues = $this->getSubmitValues();
    }

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

    if (!$this->hasRequiredFilters() || (!empty($this->_formValues) && count($this->validateFilters()) == 0)) {
      $export_id = CRM_Utils_Request::retrieve('export_id', 'Positive');
      if ($export_id) {
        $this->runExport($export_id);
      }

      $limit = CRM_Utils_Request::retrieve('crmRowCount', 'Positive', $this, FALSE, CRM_Utils_Pager::ROWCOUNT);
      $pageId = CRM_Utils_Request::retrieve('crmPID', 'Positive', $this, FALSE, 1);

      $this->addColumnHeaders();
      $this->buildRows($pageId, $limit);
      $this->addExportOutputs();
    }

  }

  protected function runExport($export_id) {
    $factory = dataprocessor_get_factory();
    self::applyFilters($this->dataProcessor, $this->_formValues);
    $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $export_id));
    $output = $outputs[$export_id];
    $outputClass = $factory->getOutputByName($output['type']);
    if ($outputClass instanceof \Civi\DataProcessor\Output\ExportOutputInterface) {
      $outputClass->downloadExport($this->dataProcessor, $this->dataProcessorBAO, $output, $this->_formValues);
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

    $id_field = $this->getIdFieldName();
    $this->assign('id_field', $id_field);

    $offset = ($pageId - 1) * $limit;
    $this->dataProcessor->getDataFlow()->setLimit($limit);
    $this->dataProcessor->getDataFlow()->setOffset($offset);
    self::applyFilters($this->dataProcessor, $this->_formValues);

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

    $i=0;
    try {
      while($record = $this->dataProcessor->getDataFlow()->nextRecord()) {
        $i ++;
        $row = array();
        $row['id'] = $record[$id_field]->formattedValue;
        $row['checkbox'] = CRM_Core_Form::CB_PREFIX.$row['id'];
        $row['record'] = $record;

        $link = $this->link($row);
        if ($link) {
          $row['url'] = $link;
          $row['link_text'] = $this->linkText($row);
        }

        $this->addElement('checkbox', $row['checkbox'], NULL, NULL, ['class' => 'select-row']);

        $prevnextData[] = array(
          'entity_id1' => $row['id'],
          'entity_table' => $this->getEntityTable(),
          'data' => $record,
        );
        $ids[] = $row['id'];

        $rows[] = $row;
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

  /**
   * Add buttons for other outputs of this data processor
   */
  protected function addExportOutputs() {
    $factory = dataprocessor_get_factory();
    $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('data_processor_id' => $this->dataProcessorId));
    $otherOutputs = array();
    foreach($outputs as $output) {
      if ($output['id'] == $this->dataProcessorOutput['id']) {
        continue;
      }
      $outputClass = $factory->getOutputByName(($output['type']));
      if ($outputClass instanceof \Civi\DataProcessor\Output\ExportOutputInterface) {
        $otherOutput = array();
        $otherOutput['title'] = $outputClass->getTitleForExport($output, $this->dataProcessorBAO);
        $otherOutput['url'] = $this->currentUrl.'&export_id='.$output['id'];
        $otherOutput['icon'] = $outputClass->getExportFileIcon($output, $this->dataProcessorBAO);
        $otherOutputs[] = $otherOutput;
      }
    }
    $this->assign('other_outputs', $otherOutputs);
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
        if (isset($selectedIdsArr[$qfKeyParam]) && is_array($selectedIdsArr[$qfKeyParam])) {
          $selectedIds = array_keys($selectedIdsArr[$qfKeyParam]);
        }
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

  public function validate() {
    $this->_errors = $this->validateFilters();
    return parent::validate();
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
    $this->loadDataProcessor();
    return $this->dataProcessorOutput['configuration']['title'];
  }

}