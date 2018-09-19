<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

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
   * @param array $configuration
   * @param int $data_processor_id
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function initialize($configuration, $data_processor_id);

  /**
   * Returns the URL for the configuration form of the join specification
   *
   * @return string
   */
  public function getConfigurationUrl();

}