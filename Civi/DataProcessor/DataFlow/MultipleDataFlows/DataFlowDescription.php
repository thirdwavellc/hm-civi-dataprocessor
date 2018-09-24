<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\MultipleDataFlows;

class DataFlowDescription {

  /**
   * @var \Civi\DataProcessor\DataFlow\AbstractDataFlow;
   */
  protected $dataFlow;

  /**
   * @var \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification
   */
  protected $joinSpecification = array();

  public function __construct($datFlow, $joinSpecification = null) {
    $this->dataFlow = $datFlow;
    $this->joinSpecification = $joinSpecification;
    $this->dataFlow->setOffset($this);
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function getDataFlow() {
    return $this->dataFlow;
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification
   */
  public function getJoinSpecification() {
    return $this->joinSpecification;
  }

}