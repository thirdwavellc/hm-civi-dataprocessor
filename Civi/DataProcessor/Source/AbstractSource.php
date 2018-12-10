<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;


use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
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

}