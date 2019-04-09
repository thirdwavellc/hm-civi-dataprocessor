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

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->assign('id', $this->id);

    $sources = CRM_Dataprocessor_BAO_Source::getValues(array('data_processor_id' => $this->dataProcessorId));
    if ($this->id) {
      $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->id));
      $this->assign('source', $source[$this->id]);
      $i = 0;
      foreach($sources as $s) {
        if ($s['id'] == $this->id) {
          $this->isFirstDataSource = $i > 0 ? false : true;
          break;
        }
        $i++;
      }
    } else {
      $this->isFirstDataSource = count($sources) > 0 ? false : true;
    }
    $this->assign('is_first_data_source', $this->isFirstDataSource);

    $title = E::ts('Data Processor Source');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'id');
    if ($this->_action != CRM_Core_Action::DELETE) {
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
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Next'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::DELETE) {
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
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

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->id));
    if (isset($source[$this->id]['type'])) {
      $defaults['type'] = $source[$this->id]['type'];
    }
    if (isset($source[$this->id]['title'])) {
      $defaults['title'] = $source[$this->id]['title'];
    }
    if (isset($source[$this->id]['name'])) {
      $defaults['name'] = $source[$this->id]['name'];
    }
    if (isset($source[$this->id]['join_type'])) {
      $defaults['join_type'] = $source[$this->id]['join_type'];
    }
    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $backUrl = $redirectUrl = $session->readUserContext();
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Dataprocessor_BAO_Source::deleteWithId($this->id);
      $session->setStatus(E::ts('Data Processor Source removed'), E::ts('Removed'), 'success');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->id));

    $values = $this->exportValues();

    $factory = dataprocessor_get_factory();
    $sourceClass = $factory->getDataSourceByName($values['type']);

    if (!empty($values['name'])) {
      $params['name'] = $values['name'];
    } else {
      $params['name'] = CRM_Dataprocessor_BAO_Source::buildNameFromTitle($values['title']);
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
    } else {
      $params['configuration'] = $sourceClass->getDefaultConfiguration();
    }
    if (isset($source[$this->id])) {
      $params['join_configuration'] = $source[$this->id]['join_configuration'];
    }


    $result = CRM_Dataprocessor_BAO_Source::add($params);


    $configurationUrl = false;
    if ($sourceClass->getConfigurationUrl()) {
      $configurationUrl = CRM_Utils_System::url($sourceClass->getConfigurationUrl(), [
        'reset' => 1,
        'action' => 'add',
        'source_id' => $result['id'],
        'data_processor_id' => $this->dataProcessorId
      ]);
      $redirectUrl = $configurationUrl;
    }

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
        if ($configurationUrl) {
          $session->pushUserContext($configurationUrl);
        }
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
      $fields['name'] = CRM_Dataprocessor_BAO_Source::buildNameFromTitle($fields['title']);
    }
    if (!CRM_Dataprocessor_BAO_Source::isNameValid($fields['name'], $fields['data_processor_id'], $id)) {
      $errors['name'] = E::ts('There is already a data source with this name');
      return $errors;
    }
    return TRUE;
  }

}