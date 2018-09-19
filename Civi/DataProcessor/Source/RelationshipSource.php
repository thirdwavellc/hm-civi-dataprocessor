<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\SqlDataFlow\SimpleWhereClause;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

use CRM_Dataprocessor_ExtensionUtil as E;

class RelationshipSource implements SourceInterface {

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
    $this->dataFlow = new SqlTableDataFlow('civicrm_relationship', $source_name, new DataSpecification(array(
      new FieldSpecification('id', 'Integer', $source_name.'_id'),
      new FieldSpecification('relationship_type_id', 'Integer', $source_name.'_relationship_type_id'),
      new FieldSpecification('is_active', 'Boolean', $source_name.'_is_active'),
      new FieldSpecification('contact_id_a', 'Integer', $source_name.'_contact_id_a'),
      new FieldSpecification('contact_id_b', 'Integer', $source_name.'_contact_id_b'),
    )));
    if (isset($configuration['relationship_type_id'])) {
      $this->dataFlow->addWhereClause(new SimpleWhereClause($source_name, 'relationship_type_id', 'IN', $configuration['relationship_type_id'], 'Integer'));
    }
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
    return 'civicrm/dataprocessor/form/source/relationship';
  }

}