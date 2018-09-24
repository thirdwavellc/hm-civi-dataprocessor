<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

interface SourceInterface {

  /**
   * Returns the data flow.
   *
   * @return AbstractDataFlow
   */
  public function getDataFlow();


  /**
   * Initialize the join
   *
   * @return void
   */
  public function initialize();

  /**
   * Sets the join specification to connect this source to other data sources.
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface $join
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setJoin(JoinInterface $join);

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
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFields();

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFilterFields();

  /**
   * @return \Civi\DataProcessor\DataSpecification\AggregationField[]
   */
  public function getAvailableAggregationFields();

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl();

  /**
   * Ensure that filter field is accesible in the query
   *
   * @param String $fieldName
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription
   * @throws \Exception
   */
  public function ensureField($fieldName);

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification);

  /**
   * Ensures an aggregation field in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function ensureAggregationFieldInSource(FieldSpecification $fieldSpecification);

  /**
   * @return String
   */
  public function getSourceName();

  /**
   * @param String $name
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceName($name);

  /**
   * @return String
   */
  public function getSourceTitle();

  /**
   * @param String $title
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceTitle($title);

}