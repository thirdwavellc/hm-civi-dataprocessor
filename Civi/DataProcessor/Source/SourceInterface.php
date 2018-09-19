<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;

interface SourceInterface {

  /**
   * Returns the data flow.
   *
   * @return AbstractDataFlow
   */
  public function getDataFlow();


  /**
   * Initialize this data source.
   *
   * @param array $configuration
   * @param string $source_name
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function initialize($configuration, $source_name);

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl();

}