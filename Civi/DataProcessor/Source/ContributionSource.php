<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

use CRM_Dataprocessor_ExtensionUtil as E;

class ContributionSource extends AbstractCivicrmEntitySource {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Contribution';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_contribution';
  }
}