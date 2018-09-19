<?php
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Dataprocessor_Upgrader extends CRM_Dataprocessor_Upgrader_Base {

  public function install() {
    $this->executeSqlFile('sql/create_civicrm_data_processor.sql');
  }

  public function uninstall() {
   $this->executeSqlFile('sql/uninstall.sql');
  }

}
