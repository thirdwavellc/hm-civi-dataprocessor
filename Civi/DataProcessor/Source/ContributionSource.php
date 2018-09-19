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

class ContributionSource implements SourceInterface {

  /**
   * @var \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected $dataFlow;

  public function __construct() {

  }

  /**
   * Initialize this data source.
   *
   * @param array $configuration
   * @param string $source_name
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function initialize($configuration, $source_name) {
    $this->dataFlow = new SqlTableDataFlow('civicrm_contribution', $source_name, new DataSpecification(array(
      new FieldSpecification('id', 'Integer', $source_name.'_id'),
      new FieldSpecification('contact_id', 'Integer', $source_name.'_contact_id'),
      new FieldSpecification('total_amount', 'Float', $source_name.'_total_amount'),
    )));
    return $this;
  }

  public function getDataFlow() {
    return $this->dataFlow;
  }

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return false;
  }

}