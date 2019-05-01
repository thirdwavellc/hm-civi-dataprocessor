<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use Civi\DataProcessor\Source\SourceInterface;

interface JoinInterface{

  /**
   * Joins the records sets and return the new created set.
   *
   * @param $left_record_set
   * @param $right_record_set
   *
   * @return array
   */
  public function join($left_record_set, $right_record_set);

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
   * Prepares the right data flow based on the data in the left record set.
   *
   * @param $left_record_set
   * @param \Civi\DataProcessor\DataFlow\AbstractDataFlow $rightDataFlow
   *
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function prepareRightDataFlow($left_record_set, AbstractDataFlow $rightDataFlow);

  /**
   * Returns true when this join has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration();

  /**
   * When this join has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param SourceInterface $joinFromSource
   * @param SourceInterface[] $joinableToSources
   * @param array $joinConfiguration
   *   The current join configuration
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, SourceInterface $joinFromSource, $joinableToSources, $joinConfiguration=array());

  /**
   * When this join has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName();


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param SourceInterface $joinFromSource
   * @return array
   */
  public function processConfiguration($submittedValues, SourceInterface $joinFromSource);

}