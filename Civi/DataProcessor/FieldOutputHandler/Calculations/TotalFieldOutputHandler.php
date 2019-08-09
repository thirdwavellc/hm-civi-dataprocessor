<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler\Calculations;

use Civi\DataProcessor\FieldOutputHandler\FieldOutput;

class TotalFieldOutputHandler extends CalculationFieldOutputHandler {

  /**
   * @param array $values
   * @return int|float
   */
  protected function doCalculation($values) {
    $value = 0;
    foreach($values as $v) {
      $value = $value + $v;
    }
    return $value;
  }

}