<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

use Civi\DataProcessor\Source\SourceInterface;

class AggregationField {

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public $fieldSpecification;

  /**
   * @var \Civi\DataProcessor\Source\SourceInterface
   */
  public $dataSource;

  public function __construct(FieldSpecification $fieldSpecification, SourceInterface $dataSource) {
    $this->dataSource = $dataSource;
    $this->fieldSpecification = $fieldSpecification;
  }



}
