<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

class FieldOutput {

  public $rawValue;

  public $formattedValue;

  public function __construct($rawValue) {
    $this->rawValue = $rawValue;
    $this->formattedValue = $rawValue;
  }

}