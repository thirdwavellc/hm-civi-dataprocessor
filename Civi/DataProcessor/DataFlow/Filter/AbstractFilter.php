<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\Filter;

abstract class AbstractFilter {

  /**
   * Filters a record. Returns true when a record is valid, and false when the record does not
   * match the filter criteria.
   *
   * @param array $record
   * @return bool
   */
  abstract public function filter($record);

}