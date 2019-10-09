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
    while ($dao->fetch()) {
      $configuration = json_decode($dao->configuration, TRUE);
      if (isset($configuration['navigation_id']) && $configuration['navigation_id']) {
        $navId = $configuration['navigation_id'];
        \CRM_Core_BAO_Navigation::processDelete($navId);
      }
    }
    \CRM_Core_BAO_Navigation::resetNavigation();
  }

  /**
   * Upgrade after refactor of Aggregation functionality.
   *
   * @return bool
   */
  public function upgrade_1001() {
    CRM_Dataprocessor_Upgrader_Version_1_1_0::upgradeAggregationFields();
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_data_processor` DROP `aggregation`;");
    return TRUE;
  }

}
