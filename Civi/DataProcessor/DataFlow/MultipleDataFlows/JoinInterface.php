<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

interface JoinInterface{

  /**
   * Validates the right record against the left record and returns true when the right record
   * has a successfull join with the left record. Otherwise false.
   *
   * @param $left_record
   * @param $right_record
   *
   * @return mixed
   */
  public function isJoinable($left_record, $right_record);

  /**
   * Returns true when this join is compatible with this data flow
   *
   * @param \Civi\DataProcessor\DataFlow\AbstractDataFlow $
   * @return bool
   */
  public function worksWithDataFlow(AbstractDataFlow $dataFlow);

  /**
   * Initialize the join
   *
   * @return void
   */
  public function initialize();

  /**
   * @param AbstractProcessorType $dataProcessor
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setDataProcessor(AbstractProcessorType $dataProcessor);

  /**
   * @param array $configuration
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setConfiguration($configuration);

  /**
   * Returns the URL for the configuration form of the join specification
   *
   * @return string
   */
  public function getConfigurationUrl();

}