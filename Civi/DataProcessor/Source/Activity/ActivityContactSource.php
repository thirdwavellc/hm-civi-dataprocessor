<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Activity;

use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use CRM_Dataprocessor_ExtensionUtil as E;

class ActivityContactSource extends AbstractCivicrmEntitySource {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'ActivityContact';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_activity_contact';
  }
}