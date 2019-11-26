<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Dummy TEMPORARY test which requires the reinstallation of the test db
 * for debugging problems with installation.
 *
 * @group headless
 */
class CRM_Dataprocessor_Dummy extends CRM_Dataprocessor_TestBase {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply(TRUE);
  }

  public function testDummy() {
    $this->assertTrue(TRUE);
  }

}

