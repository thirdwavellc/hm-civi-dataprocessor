<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\Utils;

use Civi\DataProcessor\DataSpecification\Aggregatable;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

class Aggregator {

  /**
   * @var array
   */
  protected $records;

  /**
   * @var FieldSpecification[]
   */
  protected $aggregateFields = array();

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $dataSpecification = array();

  public function __construct($records, $aggregateFields, $dataSpecification) {
    $this->records = $records;
    $this->aggregateFields = $aggregateFields;
    $this->dataSpecification = $dataSpecification;
  }

  public function aggregateRecords($fieldNameprefix="") {
    $aggregatedRecrodSets = array();
    foreach($this->records as $record) {
      $key = $this->getAggregationKeyFromRecord($record, $fieldNameprefix);
      $aggregatedRecrodSets[$key][] = $record;
    }

    $newRecordSet = array();
    foreach($aggregatedRecrodSets as $aggregatedSet) {
      $firstRecord = reset($aggregatedSet);
      $newRecord = array();
      foreach($this->dataSpecification->getFields() as $fieldSpecification) {
        if ($fieldSpecification instanceof Aggregatable) {
          $newRecord[$fieldNameprefix.$fieldSpecification->alias] = $fieldSpecification->aggregateRecords($aggregatedSet, $fieldNameprefix.$fieldSpecification->alias);
        } elseif (isset($firstRecord[$fieldNameprefix.$fieldSpecification->alias]))  {
          $newRecord[$fieldNameprefix.$fieldSpecification->alias] = $firstRecord[$fieldNameprefix.$fieldSpecification->alias];
        }
      }
      $newRecordSet[] = $newRecord;
    }
    return $newRecordSet;
  }

  protected function getAggregationKeyFromRecord($record, $fieldNameprefix="") {
    $key = '';
    foreach($this->aggregateFields as $field) {
      $alias = $field->alias;
      if (isset($record[$fieldNameprefix.$alias])) {
        $key .= $record[$fieldNameprefix.$alias].'_';
      } else {
        $key .= 'null_';
      }
    }
    return $key;
  }

}
