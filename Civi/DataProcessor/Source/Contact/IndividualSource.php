<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Contact;

use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use CRM_Dataprocessor_ExtensionUtil as E;

class IndividualSource extends AbstractCivicrmEntitySource {

  protected $skipFields = array(
    'contact_type',
    'household_name',
    'legal_name',
    'sic_code',
    'organization_name',
  );

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Contact';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_contact';
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFilterFields() {
    if (!$this->availableFilterFields) {
      $this->availableFilterFields = new DataSpecification();
      $this->loadFields($this->availableFilterFields, $this->skipFields);
      $this->loadCustomGroupsAndFields($this->availableFilterFields, true, 'Individual');
    }
    return $this->availableFilterFields;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFields() {
    if (!$this->availableFields) {
      $this->availableFields = new DataSpecification();
      $this->loadFields($this->availableFields, $this->skipFields);
      $this->loadCustomGroupsAndFields($this->availableFields, false, 'Individual');
    }
    return $this->availableFields;
  }

  /**
   * Add the filters to the where clause of the data flow
   *
   * @param $configuration
   * @throws \Exception
   */
  protected function addFilters($configuration) {
    parent::addFilters($configuration);
    $this->addFilter('contact_type', '=', 'Individual');
  }


}