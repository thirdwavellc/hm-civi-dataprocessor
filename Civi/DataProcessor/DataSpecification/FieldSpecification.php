<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class FieldSpecification {

  /**
   * @var String
   */
  public $name;

  /**
   * @var String
   */
  public $type;

  /**
   * @var String
   */
  public $alias;

  public function __construct($name, $type, $alias=null) {
    if (empty($alias)) {
      $this->alias = $name;
    } else {
      $this->alias = $alias;
    }
    $this->name = $name;
    $this->type = $type;
  }

}