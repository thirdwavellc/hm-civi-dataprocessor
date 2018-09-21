<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\ProcessorType;

use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\CombinedSqlDataFlow;
use Civi\DataProcessor\DataFlow\CombinedDataFlow\SqlCombineSpecification;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\DataFlowDescription;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface;
use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification;
use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler;
use Civi\DataProcessor\FilterHandler\AbstractFilterHandler;
use Civi\DataProcessor\Storage\StorageInterface;

abstract class AbstractProcessorType {

  /**
   * @var array
   */
  protected $dataSources = array();

  /**
   * @var bool
   */
  protected $allSqlDataFlows = true;

  /**
   * @var \Civi\DataProcessor\Storage\StorageInterface|null
   */
  protected $storage = null;

  /**
   * @var \Civi\DataProcessor\DataFlow\AbstractDataFlow|null
   */
  protected $dataflow = null;

  /**
   * @var \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler[]
   */
  protected $outputFieldHandlers;

  /**
   * @var \Civi\DataProcessor\FilterHandler\AbstractFilterHandler[]
   */
  protected $filterHandlers;

  /**
   * Add a data source to the processor
   * @param \Civi\DataProcessor\Source\SourceInterface $datasource
   * @param \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface|NULL $combineSpecification
   */
  public function addDataSource(\Civi\DataProcessor\Source\SourceInterface $datasource, JoinInterface $combineSpecification=null) {
    $d['datasource'] = $datasource;
    $d['combine_specification'] = $combineSpecification;
    $this->dataSources[] = $d;
    if (!$datasource->getDataFlow() instanceof SqlDataFlow) {
      $this->allSqlDataFlows = false;
    }
  }

  /**
   * Returns the data source
   *
   * @param $source_name
   * @return null|\Civi\DataProcessor\Source\SourceInterface
   */
  public function getDataSourceByName($source_name) {
    foreach($this->dataSources as $dataSource) {
      if ($dataSource['datasource']->getSourceName() == $source_name) {
        return $dataSource['datasource'];
      }
    }
    return null;
  }

  /**
   * @return \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler[]
   */
  public function getAvailableOutputHandlers() {
    $factory = dataprocessor_get_factory();
    $handlers = array();
    foreach($this->dataSources as $dataSource) {
      foreach($dataSource['datasource']->getAvailableFields()->getFields() as $field) {
        $fieldHandlers = $factory->getOutputHandlers($field, $dataSource['datasource']);
        $handlers = array_merge($handlers, $fieldHandlers);
      }
    }
    return $handlers;
  }

  /**
   * @return \Civi\DataProcessor\FieldOutputHandler\AbstractFilterOutputHandler[]
   */
  public function getAvailableFilterHandlers() {
    $factory = dataprocessor_get_factory();
    $handlers = array();
    foreach($this->dataSources as $dataSource) {
      foreach($dataSource['datasource']->getAvailableFilterFields()->getFields() as $field) {
        $fieldHandlers = $factory->getFilterHandlers($field, $dataSource['datasource']);
        $handlers = array_merge($handlers, $fieldHandlers);
      }
    }
    return $handlers;
  }

  /**
   * @param \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler $outputFieldHandler
   */
  public function addOutputFieldHandlers(AbstractFieldOutputHandler $outputFieldHandler) {
    $this->outputFieldHandlers[] = $outputFieldHandler;
  }

  /**
   * @param \Civi\DataProcessor\FilterHandler\AbstractFilterHandler $filterHandler
   */
  public function addFilterHandler(AbstractFilterHandler $filterHandler) {
    $this->filterHandlers[] = $filterHandler;
  }

  /**
   * @return \Civi\DataProcessor\FilterHandler\AbstractFilterHandler[]
   */
  public function getFilterHandlers() {
    return $this->filterHandlers;
  }

  public function ensureFieldInDataSource(FieldSpecification $fieldSpecification) {
    foreach($this->dataSources as $dataSource) {
      $dataSource['datasource']->ensureFieldInSource($fieldSpecification);
    }
  }

  /**
   * Sets the storage of the data processor.
   *
   * @param \Civi\DataProcessor\Storage\StorageInterface $storage
   */
  public function setStorage(StorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   * @throws \Civi\DataProcessor\DataFlow\InvalidFlowException
   */
  public function getDataFlow() {
    if (!$this->dataflow) {
      if (count($this->dataSources) === 1) {
        $dataflow = $this->dataSources[0]['datasource']->getDataFlow();
      }
      else {
        if ($this->allSqlDataFlows) {
          $dataflow = new CombinedSqlDataFlow();
        }
        else {
          $dataflow = new CombinedDataFlow();
        }
        foreach ($this->dataSources as $datasource) {
          $dataFlowDescription = new DataFlowDescription($datasource['datasource']->getDataFlow(), $datasource['combine_specification']);
          $dataflow->addSourceDataFlow($dataFlowDescription);
        }
      }

      if ($this->storage) {
        $this->storage->setSourceDataFlow($dataflow);
        $this->dataflow = $this->storage->getDataFlow();
      } else {
        $this->dataflow = $dataflow;
      }

      $this->dataflow->setOutputFieldHandlers($this->outputFieldHandlers);
    }
    return $this->dataflow;
  }

}