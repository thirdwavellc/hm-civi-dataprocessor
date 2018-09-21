<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;

abstract class AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $fieldSpecification;

  /**
   * @var bool
   */
  protected $is_required;

  /**
   * Returns the name of the handler type.
   *
   * @return String
   */
  abstract public function getName();

  /**
   * Returns the title of the handler type.
   *
   * @return String
   */
  abstract public function getTitle();

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  abstract protected function getType();

  /**
   * @param array $filterParams
   *   The filter settings
   * @return mixed
   */
  abstract public function setFilter($filterParams);

  public function __construct() {
    $this->fieldSpecification = new FieldSpecification($this->getName(), $this->getType(), $this->getName());
  }

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param bool $is_required
   * @param array $configuration
   */
  public function initialize($alias, $title, $is_required, $configuration) {
    // Override this in child classes.
    $this->fieldSpecification->title = $title;
    $this->fieldSpecification->alias = $alias;
    $this->is_required = $is_required;
  }

  public function isRequired() {
    return $this->is_required;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getFieldSpecification() {
    return $this->fieldSpecification;
  }

}