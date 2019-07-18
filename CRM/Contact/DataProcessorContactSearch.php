<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\Output\UIOutputInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Contact_DataProcessorContactSearch implements UIOutputInterface {

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
  public function buildConfigurationForm(\CRM_Core_Form $form, $output=array()) {
    $navigation = CRM_Dataprocessor_Utils_Navigation::singleton();
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $output['data_processor_id']));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    $fields = array();
    foreach($dataProcessorClass->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $fields[$field->alias] = $field->title;
    }

    $form->add('text', 'title', E::ts('Title'),NULL, true);
    
    // form elements for adding Dashlet
    // $output['dashlet'] 1-> Yes 2->No

    if(isset($output['dashlet']) && $output['dashlet']==1){
      $form->add('text', 'dashlet_title', E::ts('Dashlet Title'), NULL, true);
      $form->add('text', 'dashlet_name', E::ts('Dashlet Name (system name)'), NULL, true);
      $form->add('select', 'dashlet_active', E::ts('Is Dashlet Active ?'), array(1=>'Yes', 0=> 'No'), true);
    }

    $form->add('select','permission', E::ts('Permission'), \CRM_Core_Permission::basicPermissions(), true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'contact_id_field', E::ts('Contact ID field'), $fields, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'hide_id_field', E::ts('Show Contact ID field'), array(0=>'Contact ID is Visible', 1=> 'Contact ID is hidden'));

    $form->add('wysiwyg', 'help_text', E::ts('Help text for this search'), array('rows' => 6, 'cols' => 80));

    // navigation field
    $navigationOptions = $navigation->getNavigationOptions();
    if (isset($output['configuration']['navigation_id'])) {
      $navigationPath = $navigation->getNavigationPathById($output['configuration']['navigation_id']);
      unset($navigationOptions[$navigationPath]);
    }
    $form->add('select', 'navigation_parent_path', ts('Parent Menu'), array('' => ts('- select -')) + $navigationOptions, true);

    $defaults = array();
    if ($output) {
      
      if (isset($output['permission'])) {
        $defaults['permission'] = $output['permission'];
      }
      if (isset($output['dashlet_name'])) {
        $defaults['dashlet_name'] = $output['dashlet_name'];
        $defaults['dashlet_title'] = $output['dashlet_title'];
        $defaults['dashlet_active'] = $output['dashlet_active'];
      }
      if (isset($output['configuration']) && is_array($output['configuration'])) {
        if (isset($output['configuration']['contact_id_field'])) {
          $defaults['contact_id_field'] = $output['configuration']['contact_id_field'];
        }
        if (isset($output['configuration']['navigation_id'])) {
          $defaults['navigation_parent_path'] = $navigation->getNavigationParentPathById($output['configuration']['navigation_id']);
        }
        if (isset($output['configuration']['title'])) {
          $defaults['title'] = $output['configuration']['title'];
        }
        if (isset($output['configuration']['hide_id_field'])) {
          $defaults['hide_id_field'] = $output['configuration']['hide_id_field'];
        }
        if (isset($output['configuration']['help_text'])) {
          $defaults['help_text'] = $output['configuration']['help_text'];
        }
      }
    }
    if (!isset($defaults['permission'])) {
      $defaults['permission'] = 'access CiviCRM';
    }
    if (empty($defaults['title'])) {
      $defaults['title'] = civicrm_api3('DataProcessor', 'getvalue', array('id' => $output['data_processor_id'], 'return' => 'title'));
    }
    
    $form->setDefaults($defaults);
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Contact/Form/OutputConfiguration/DataProcessorContactSearch.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $output
   * @return array
   */
  public function processConfiguration($submittedValues, &$output) {
    $output['permission'] = $submittedValues['permission'];
    $configuration['title'] = $submittedValues['title'];
    $configuration['contact_id_field'] = $submittedValues['contact_id_field'];
    $configuration['navigation_parent_path'] = $submittedValues['navigation_parent_path'];
    $configuration['hide_id_field'] = $submittedValues['hide_id_field'];
    $configuration['help_text'] = $submittedValues['help_text'];
    return $configuration;
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $output
   * @return array
   */
  public function processDashletConfiguration($submittedValues) {

    $configuration['domain_id'] = 1;
    $configuration['name'] = $submittedValues['dashlet_name'];
    $configuration['label'] = $submittedValues['dashlet_title'];
    $configuration['permission'] = $submittedValues['permission'];
    $configuration['is_active'] = $submittedValues['dashlet_active'];
    $configuration['cache_minutes'] = 60;

    return $configuration;
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getUrlToUi($output, $dataProcessor) {
    return "civicrm/dataprocessor_contact_search/{$dataProcessor['name']}";
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForUiLink($output, $dataProcessor) {
    return isset($output['configuration']['title']) ? $output['configuration']['title'] : $dataProcessor['title'];
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string|false
   */
  public function getIconForUiLink($output, $dataProcessor) {
    return false;
  }

  /**
   * Returns the callback for the UI.
   *
   * @return string
   */
  public function getCallbackForUi() {
    return 'CRM_Contact_Controller_DataProcessorContactSearch';
  }

  /**
   * Checks whether the current user has access to this output
   *
   * @param array $output
   * @param array $dataProcessor
   * @return bool
   */
  public function checkUIPermission($output, $dataProcessor) {
    return CRM_Core_Permission::check(array(
      $output['permission']
    ));
  }
}