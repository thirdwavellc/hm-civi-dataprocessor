<?php

require_once 'dataprocessor.civix.php';
use CRM_Dataprocessor_ExtensionUtil as E;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Definition;

/**
 * @return \Civi\DataProcessor\Factory
 */
function dataprocessor_get_factory() {
  return \Civi::service('data_processor_factory');
}

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function dataprocessor_civicrm_container(ContainerBuilder $container) {
  // Register the TypeFactory
  $container->setDefinition('data_processor_factory', new Definition('Civi\DataProcessor\Factory'));

  $apiKernelDefinition = $container->getDefinition('civi_api_kernel');
  $apiProviderDefinition = new Definition('Civi\DataProcessor\Output\Api');
  $apiKernelDefinition->addMethodCall('registerApiProvider', array($apiProviderDefinition));
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function dataprocessor_civicrm_config(&$config) {
  _dataprocessor_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function dataprocessor_civicrm_xmlMenu(&$files) {
  _dataprocessor_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function dataprocessor_civicrm_install() {
  _dataprocessor_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function dataprocessor_civicrm_postInstall() {
  _dataprocessor_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function dataprocessor_civicrm_uninstall() {
  _dataprocessor_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function dataprocessor_civicrm_enable() {
  _dataprocessor_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function dataprocessor_civicrm_disable() {
  _dataprocessor_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function dataprocessor_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _dataprocessor_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function dataprocessor_civicrm_managed(&$entities) {
  _dataprocessor_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function dataprocessor_civicrm_caseTypes(&$caseTypes) {
  _dataprocessor_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function dataprocessor_civicrm_angularModules(&$angularModules) {
  _dataprocessor_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function dataprocessor_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _dataprocessor_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function dataprocessor_civicrm_entityTypes(&$entityTypes) {
  _dataprocessor_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function dataprocessor_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function dataprocessor_civicrm_navigationMenu(&$menu) {
  $customSearchID = CRM_Dataprocessor_Form_Search_DataProcessor::findCustomSearchId();

  _dataprocessor_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('Data Processor'),
    'name' => 'data_processor',
    'url' => '',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0
  ));
  _dataprocessor_civix_insert_navigation_menu($menu, 'Administer/data_processor', array(
    'label' => E::ts('Find Data Processors'),
    'name' => 'find_data_processors',
    'url' => CRM_Utils_System::url('civicrm/contact/search/custom', 'reset=1&csid=' . $customSearchID, true),
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _dataprocessor_civix_insert_navigation_menu($menu, 'Administer/data_processor', array(
    'label' => E::ts('Add Data Processor'),
    'name' => 'add_data_processor',
    'url' => CRM_Utils_System::url('civicrm/dataprocessor/form/edit', 'reset=1&action=add', true),
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _dataprocessor_civix_navigationMenu($menu);
}
