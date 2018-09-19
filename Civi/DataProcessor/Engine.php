<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor;

use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

class Engine {

  protected $processorType;

  public function __construct(AbstractProcessorType $processorType) {
    $this->processorType = $processorType;
  }



}