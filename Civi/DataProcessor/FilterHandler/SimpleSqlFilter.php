<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Source\SourceInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class SimpleSqlFilter extends AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  protected $dataSource;

  public function __construct(FieldSpecification $filterField, SourceInterface $dataSource) {
    $this->dataSource = $dataSource;
    $this->fieldSpecification = $filterField;
  }

  /**
   * Returns the name of the handler type.
   *
   * @return String
   */
  public function getName() {
    return 'simple_filter_'.$this->fieldSpecification->alias;
  }

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return $this->fieldSpecification->type;
  }

  /**
   * Returns the title of this field
   *
   * @return String
   */
  public function getTitle() {
    return E::ts('%1::%2 (Simple Filter)', array(1 => $this->dataSource->getSourceTitle(), 2 => $this->fieldSpecification->title));
  }

  /**
   * @param array $filter
   *   The filter settings
   * @return mixed
   */
  public function setFilter($filter) {
    $this->dataSource->addFilter($this->fieldSpecification->name, $filter);
  }

}