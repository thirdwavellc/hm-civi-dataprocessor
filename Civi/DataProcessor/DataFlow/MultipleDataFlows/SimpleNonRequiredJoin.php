<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

class SimpleNonRequiredJoin  extends  SimpleJoin {

  public function __construct($left_prefix = null, $left_field = null, $right_prefix = null, $right_field = null, $type = "INNER") {
    $type = 'LEFT';
    parent::__construct($left_prefix, $left_field, $right_prefix, $right_field, $type);
  }

  /**
   * @return string
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/joins/simple_join';
  }

  /**
   * @param array $configuration
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function setConfiguration($configuration) {
    $configuration[' type'] = 'LEFT';
    return parent::setConfiguration($configuration);
  }


}