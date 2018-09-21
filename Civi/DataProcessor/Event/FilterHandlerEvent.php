<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Event;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Source\SourceInterface;
use Symfony\Component\EventDispatcher\Event;

class FilterHandlerEvent extends Event {

  const NAME = 'dataprocessor.filterhandler';

  /**
   * @var FieldSpecification
   */
  public $fieldSpecification;

  /**
   * @var SourceInterface
   */
  public $dataSource;

  /**
   * @var array
   */
  public $handlers = array();

  public function __construct(FieldSpecification $field, SourceInterface $source) {
      $this->fieldSpecification = $field;
      $this->dataSource = $source;
  }

}