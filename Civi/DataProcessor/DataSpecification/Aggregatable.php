<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

interface Aggregatable {

  /**
   * Aggregate the field in all the records and return the aggregated value.
   *
   * @param $records
   * @param string $fieldName
   *
   * @return mixed
   */
  public function aggregateRecords($records, $fieldName="");

}
