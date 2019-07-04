<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Dataprocessor_Upgrader extends CRM_Dataprocessor_Upgrader_Base {

  public function install() {

  }

  public function uninstall() {
    // Remove output from menu
    $dao = CRM_Core_DAO::executeQuery("SELECT configuration FROM civicrm_data_processor_output");
    while($dao->fetch()) {
      $configuration = json_decode($dao->configuration, true);
      if (isset($configuration['navigation_id']) && $configuration['navigation_id']) {
        $navId = $configuration['navigation_id'];
        \CRM_Core_BAO_Navigation::processDelete($navId);
      }
    }
    \CRM_Core_BAO_Navigation::resetNavigation();
  }

}
