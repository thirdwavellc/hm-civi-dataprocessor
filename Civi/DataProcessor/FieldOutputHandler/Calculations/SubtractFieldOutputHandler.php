<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler\Calculations;

use Civi\Api4\Phone;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;

class SubtractFieldOutputHandler extends CalculationFieldOutputHandler {

  /**
   * @param array $values
   * @return int|float
   */
  protected function doCalculation($values) {
    $value = 0;
    $i =0;
    foreach($values as $v) {
      if ($i == 0) {
        $value = $v;
      } else {
        $value = $value - $v;
      }
      $i++;
    }
    return $value;
  }

}