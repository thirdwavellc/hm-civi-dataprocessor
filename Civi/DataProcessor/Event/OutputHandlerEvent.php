<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Event;

use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use Symfony\Component\EventDispatcher\Event;

class OutputHandlerEvent extends Event {

  const NAME = 'dataprocessor.outputhandler';

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  public $dataProcessor;

  /**
   * @var array
   */
  public $handlers = array();

  public function __construct(AbstractProcessorType $dataProcessor) {
      $this->dataProcessor = $dataProcessor;
  }

}