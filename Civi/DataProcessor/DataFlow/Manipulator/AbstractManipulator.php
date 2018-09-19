<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\Manipulator;

use Civi\DataProcessor\DataSpecification\DataSpecification;

abstract class AbstractManipulator {

  /**
   * Manipulates the dataspecification
   *
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  abstract public function manipulateDataSpecification(DataSpecification $dataSpecification);

  /**
   * @param array $record
   * @return array
   */
  abstract public function manipulate($record, $fieldNamePrefix);

}