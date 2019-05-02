<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Contact;

use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use CRM_Dataprocessor_ExtensionUtil as E;

class PhoneSource extends AbstractCivicrmEntitySource {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Phone';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_phone';
  }

  /**
   * Returns an array with the names of required configuration filters.
   * Those filters are displayed as required to the user
   *
   * @return array
   */
  protected function requiredConfigurationFilters() {
    return array(
      'is_primary',
    );
  }

  /**
   * Returns the default configuration for this data source
   *
   * @return array
   */
  public function getDefaultConfiguration() {
    return array(
      'filter' => array(
        'is_primary' => array (
          'op' => '=',
          'value' => '1',
        ),
      )
    );
  }

}