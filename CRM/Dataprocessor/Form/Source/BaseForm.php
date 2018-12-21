<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Form_Source_BaseForm extends CRM_Core_Form {

  protected $dataProcessorId;

  protected $source_id;

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $sourceClass;

  /**
   * @var array
   *   The source object
   */
  protected $source;

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $this->dataProcessorId = CRM_Utils_Request::retrieve('data_processor_id', 'Integer');
    $this->assign('data_processor_id', $this->dataProcessorId);

    $this->source_id = CRM_Utils_Request::retrieve('source_id', 'Integer', CRM_Core_DAO::$_nullObject, TRUE);
    $this->assign('source_id', $this->source_id);

    $source = CRM_Dataprocessor_BAO_Source::getValues(array('id' => $this->source_id));
    $this->source = $source[$this->source_id];
    $this->assign('source', $this->source);

    $factory = dataprocessor_get_factory();
    $this->sourceClass = $factory->getDataSourceByName($this->source['type']);
    $this->sourceClass->setSourceName($this->source['name']);
    $this->sourceClass->setSourceTitle($this->source['title']);

    $title = E::ts('Data Processor Source Configuration');
    CRM_Utils_System::setTitle($title);

    $url = CRM_Utils_System::url('civicrm/dataprocessor/form/edit', array('id' => $this->dataProcessorId, 'action' => 'update', 'reset' => 1));
    $session->pushUserContext($url);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'data_processor_id');
    $this->add('hidden', 'source_id');

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))
    ));
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = [];
    $defaults['data_processor_id'] = $this->dataProcessorId;
    $defaults['source_id'] = $this->source_id;

    return $defaults;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    CRM_Utils_System::redirect($session->readUserContext());
    parent::postProcess();
  }

}