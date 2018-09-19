<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * A custom contact search
 */
class CRM_Dataprocessor_Form_Search_DataProcessor extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  public static function findCustomSearchId() {
    try {
      return civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'custom_search',
        'name' => 'CRM_Dataprocessor_Form_Search_DataProcessor',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  function __construct(&$formValues) {
    // Set the user context
    $session = CRM_Core_Session::singleton();
    $url = CRM_Utils_System::getUrlPath();
    $query['csid'] = CRM_Utils_Request::retrieve('csid', 'Integer');
    if (empty($query['csid']) && isset($formValues['csid'])) {
      $query['csid'] = $formValues['csid'];
    }
    $query['reset'] = 1;
    $userContext = CRM_Utils_System::url($url,$query);
    $session->pushUserContext($userContext);
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(E::ts('Data Processor'));

    $onlyActive = array(
      '1' => ts('Only active Data Processors)'),
      '0' => ts('All Data Processors'),
    );
    $form->addRadio('only_active', ts('Find only active Data Processors?'), $onlyActive, NULL, '<br />', TRUE);
    $defaults['only_active'] = 1;
    $form->setDefaults($defaults);

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('only_active'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      'data_processor_id' => 'data_processor_id',
      'is_active_value' => 'is_active_value',
      'status_value' => 'status_value',
      E::ts('Data Processor') => 'title',
      E::ts('Description') => 'description',
      E::ts('Is active') => 'is_active',
      E::ts('Status') => 'status',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * @inheritDoc
   */
  public function count() {
    $sql = $this->sql("COUNT(*) as total", 0, 0, null, FALSE, NULL);

    $dao = CRM_Core_DAO::executeQuery($sql,
      CRM_Core_DAO::$_nullArray
    );
    return $dao->N;
  }

  /**
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $returnSQL Not used; included for consistency with parent; SQL is always returned
   *
   * @return string
   */
  public function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = TRUE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      data_processor.id           as data_processor_id  ,
      data_processor.title as title,
      data_processor.description    as description,
      data_processor.is_active    as is_active,
      data_processor.is_active    as is_active_value,
      data_processor.status as status,
      data_processor.status as status_value
      
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return "
      FROM      civicrm_data_processor data_processor
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();
    $index = 1;
    $clauses = array();

    $onlyActive = CRM_Utils_Array::value('only_active', $this->_formValues);
    if ($onlyActive == 1) {
      $clauses[] = "data_processor.is_active = %{$index}";
      $params[$index] = array(1, 'Integer');
      $index++;
    }

    if (!empty($clauses)) {
      $where = implode(' AND ', $clauses);
      return $this->whereClause($where, $params);
    }

    return "";

  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Dataprocessor/Form/Search/DataProcessor.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    if ($row['is_active'] == 1) {
      $row['is_active'] = E::ts("Yes");
    } else {
      $row['is_active'] = E::ts("No");
    }
    switch ($row['status']) {
      case CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_CODE:
        $row['status'] = E::ts('In code');
        break;
      case CRM_Dataprocessor_DAO_DataProcessor::STATUS_OVERRIDDEN:
        $row['status'] = E::ts('Overridden');
        break;
      case CRM_Dataprocessor_DAO_DataProcessor::STATUS_IN_DATABASE:
        $row['status'] = E::ts('In database');
        break;
    }
  }
}
