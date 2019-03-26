<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;


use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

abstract class AbstractSource implements SourceInterface {

  /**
   * @var String
   */
  protected $sourceName;

  /**
   * @var String
   */
  protected $sourceTitle;

  /**
   * @var AbstractProcessorType
   */
  protected $dataProcessor;

  /**
   * @var array
   */
  protected $configuration;

  /**
   * @var \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  protected $dataFlow;

  public function __construct() {

  }

  /**
   * @return String
   */
  public function getSourceName() {
    return $this->sourceName;
  }

  /**
   * @param String $name
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceName($name) {
    $this->sourceName = $name;
    return $this;
  }

  /**
   * @return String
   */
  public function getSourceTitle() {
    return $this->sourceTitle;
  }

  /**
   * @param String $title
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceTitle($title) {
    $this->sourceTitle = $title;
    return $this;
  }

  /**
   * @param AbstractProcessorType $dataProcessor
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setDataProcessor(AbstractProcessorType $dataProcessor) {
    $this->dataProcessor = $dataProcessor;
    return $this;
  }

  /**
   * @param array $configuration
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setConfiguration($configuration) {
    $this->configuration = $configuration;
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|\Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Exception
   */
  public function getDataFlow() {
    $this->initialize();
    return $this->dataFlow;
  }

  /**
   * Sets the join specification to connect this source to other data sources.
   *
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface $join
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setJoin(JoinInterface $join) {
    return $this;
  }

  /**
   * Ensure that filter field is accesible in the query
   *
   * @param String $fieldName
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|null
   * @throws \Exception
   */
  public function ensureField($fieldName) {
    $field = $this->getAvailableFields()->getFieldSpecificationByName($fieldName);
    if ($field) {
      $this->dataFlow->getDataSpecification()
        ->addFieldSpecification($fieldName, $field);
    }
    return $this->dataFlow;
  }

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification) {
    if (!$this->dataFlow->getDataSpecification()->doesFieldExist($fieldSpecification->name)) {
      $this->dataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->name, $fieldSpecification);
    }
    return $this;
  }

  /**
   * Ensures an aggregation field in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function ensureAggregationFieldInSource(FieldSpecification $fieldSpecification) {
    $this->dataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->name, $fieldSpecification);
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFilterFields() {
    return $this->getAvailableFields();
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\AggregationField[]
   */
  public function getAvailableAggregationFields() {
    return array();
  }

}