<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\Sort;

class SortComparer {

  /**
   * Sort compare function
   * Returns 0 when both values are equal
   * Returns -1 when a is less than b
   * Return 1 when b is less than a
   *
   * @param $value_a
   * @param $value_b
   * @return int
   */
  public function sort($value_a, $value_b) {
    if ($value_a == $value_b) {
      return 0;
    }
    if ($value_a < $value_b) {
      return -1;
    }
    return 1;
  }

}