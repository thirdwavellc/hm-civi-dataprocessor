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
use Civi\DataProcessor\Storage\StorageInterface;

abstract class AbstractProcessorType {

  protected $dataSources = array();

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
    }
    return $this->dataflow;
  }

}