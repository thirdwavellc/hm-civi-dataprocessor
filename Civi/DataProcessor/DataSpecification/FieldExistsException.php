<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class FieldExistsException extends \Exception {

  public function __construct($name) {
    parent::__construct('Field "'.$name.'" already exists');
  }

}