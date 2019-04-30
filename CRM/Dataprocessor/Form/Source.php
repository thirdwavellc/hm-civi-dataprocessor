<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Source extends CRM_Core_Form {

  private $dataProcessorId;

  private $id;

  private $isFirstDataSource = true;

  private $source;

  /**
   * @var Civi\DataProcessor\Source\SourceInterface
   */
  private $sourceClass;

  private $snippet;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->snippet = CRM_Utils_Request::retrieve('snippet', 'String');
    if ($this->snippet) {
      $this->assign('suppressForm', TRUE);
      $this->controller->_generateQFKey = FALSE;
    }

    $factory = dataprocessor_get_factory();

    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('id', $this->id);

    $this->assign('has_configuration', false);

    $sources = civicrm_api3('DataProcessorSource', 'get', array('data_processor_id' => $this->dataProcessorId, 'options' => array('limit' => 0)));
    if ($this->id) {
      $this->source = civicrm_api3('DataProcessorSource', 'getsingle', array('id' => $this->id));
      $this->assign('source', $this->source);
      $this->sourceClass = $factory->getDataSourceByName($this->source['type']);
      $this->assign('has_configuration', $this->sourceClass->hasConfiguration());

      $i = 0;
      foreach($sources['values'] as $s) {
        if ($s['id'] == $this->id) {
          $this->isFirstDataSource = $i > 0 ? false : true;
          break;
        }
        $i++;
      }
    } else {
      $this->isFirstDataSource = count($sources) > 0 ? false : true;
      $this->source['data_processor_id'] = $this->dataProcessorId;
    }
    $this->assign('is_first_data_source', $this->isFirstDataSource);

    $type = CRM_Utils_Request::retrieve('type', 'String');

    if ($type) {
      $this->sourceClass = $factory->getDataSourceByName($type);
      $this->assign('has_configuration', $this->sourceClass->hasConfiguration());
      if ($this->sourceClass) {
        $this->source['configuration'] = $this->sourceClass->getDefaultConfiguration();
      }
    }

    $title = E::ts('Data Processor Source');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
      $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
      $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);

      $factory = dataprocessor_get_factory();
      $types = array(' - select - ')  + $factory->getDataSources();
      $this->add('select', 'type', ts('Select source'), $types, true, array(
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ));

      if (!$this->isFirstDataSource) {
        $joins = [' - select - '] + $factory->getJoins();
        $this->add('select', 'join_type', ts('Select Join Type'), $joins, TRUE, ['class' => 'crm-select2']);
      }

      if ($this->sourceClass && $this->sourceClass->hasConfiguration()) {
        $this->sourceClass->buildConfigurationForm($this, $this->source);
        $this->assign('configuration_template', $this->sourceClass->getConfigurationTemplateFileName());
      }

      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['id'] = $this->id;

    if (isset($this->source['type'])) {
      $defaults['type'] = $this->source['type'];
    }
    if (isset($this->source['title'])) {
      $defaults['title'] = $this->source['title'];
    }
    if (isset($this->source['name'])) {
      $defaults['name'] = $this->source['name'];
    }
    if (isset($this->source['join_type'])) {
      $defaults['join_type'] = $this->source['join_type'];
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $backUrl = $redirectUrl = $session->readUserContext();
    if ($this->_action == CRM_Core_Action::DELETE) {
      civicrm_api3('DataProcessorSource', 'delete', array('id' => $this->id));
      $session->setStatus(E::ts('Data Processor Source removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $values = $this->exportValues();

    $factory = dataprocessor_get_factory();

    if (!empty($values['name'])) {
      $params['name'] = $values['name'];
    }
    $params['title'] = $values['title'];
    $params['type'] = $values['type'];
    if (!$this->isFirstDataSource) {
      $params['join_type'] = $values['join_type'];
    } else {
      $params['join_type'] = '';
    }
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->id) {
      $params['id'] = $this->id;
    }
    if (isset($this->source)) {
      $params['join_configuration'] = $this->source['join_configuration'];
    }

    if ($this->sourceClass && $this->sourceClass->hasConfiguration()) {
      $params['configuration'] = $this->sourceClass->processConfiguration($values);
    }

    $result = civicrm_api3('DataProcessorSource', 'create', $params);

    if (!$this->isFirstDataSource && $this->_action == CRM_Core_Action::ADD) {
      $joinClass = $factory->getJoinByName($values['join_type']);
      if ($joinClass->getConfigurationUrl()) {
        $joinUrl = CRM_Utils_System::url($joinClass->getConfigurationUrl(), [
          'reset' => 1,
          'action' => 'add',
          'source_id' => $result['id'],
          'data_processor_id' => $this->dataProcessorId
        ]);
        $session->pushUserContext($backUrl);
        $redirectUrl = $joinUrl;
      } else {
        $session->pushUserContext($backUrl);
      }
    } elseif ($this->_action == CRM_Core_Action::ADD) {
      $session->pushUserContext($backUrl);
    }
    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

  /**
   * Function to add validation rules (overrides parent function)
   *
   * @access public
   */
  function addRules() {
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->addFormRule(array(
        'CRM_Dataprocessor_Form_Source',
        'validateName'
      ));
    }
  }

  /**
   * Function to validate if rule label already exists
   *
   * @param array $fields
   * @return array|bool
   * @access static
   */
  static function validateName($fields) {
    /*
     * if id not empty, edit mode. Check if changed before check if exists
     */
    $id = false;
    if (!empty($fields['id'])) {
      $id = $fields['id'];
    }
    if (empty($fields['name'])) {
      $fields['name'] = CRM_Dataprocessor_BAO_DataProcessorSource::checkName($fields['title'], $fields['data_processor_id'], $id);
    }
    if (!CRM_Dataprocessor_BAO_DataProcessorSource::isNameValid($fields['name'], $fields['data_processor_id'], $id)) {
      $errors['name'] = E::ts('There is already a data source with this name');
      return $errors;
    }
    return TRUE;
  }


}