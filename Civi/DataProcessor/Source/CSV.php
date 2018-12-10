<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;


use Civi\DataProcessor\DataFlow\InMemoryDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

class CSV extends AbstractSource {

  protected $headerRow;

  protected $rows;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFields;

  /**
   * Initialize the join
   *
   * @return void
   */
  public function initialize() {
    if ($this->dataFlow) {
      return;
    }

    $this->availableFields = new DataSpecification();

    // Read the header row
    $handle = fopen('/var/www/html/test.csv', 'r');
    if (!$handle) {
      return;
    }

    $this->headerRow = fgetcsv($handle);
    foreach($this->headerRow as  $idx => $colName) {
      $field = new FieldSpecification('col_'.$idx, 'String', $colName);
      $this->availableFields->addFieldSpecification('col_'.$idx, $field);
    }
    $this->rows = array();
    while ($row = fgetcsv($handle)) {
      $record = array();
      foreach ($row as $idx => $value) {
        $record['col_'.$idx] = $value;
      }

      $this->rows[] = $record;
    }
    fclose($handle);

    $this->dataFlow = new InMemoryDataFlow($this->rows);

  }




  /**
   * Ensure that filter field is accesible in the query
   *
   * @param String $fieldName
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow|null
   * @throws \Exception
   */
  public function ensureField($fieldName) {
    $field = $this->getAvailableFields()->getFieldSpecificationByName($fieldName);
    $this->dataFlow->getDataSpecification()->addFieldSpecification($fieldName, $field);
    return $this->dataFlow;
  }

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification) {
    if (!$this->dataFlow->getDataSpecification()->doesFieldExist($fieldSpecification->name)) {
      $this->dataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->name, $fieldSpecification);
    }
    return $this;
  }

  /**
   * Ensures an aggregation field in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   * @throws \Exception
   */
  public function ensureAggregationFieldInSource(FieldSpecification $fieldSpecification) {
    $this->dataFlow->getDataSpecification()->addFieldSpecification($fieldSpecification->name, $fieldSpecification);
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFields() {
    return $this->availableFields;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFilterFields() {
    return $this->availableFields;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\AggregationField[]
   */
  public function getAvailableAggregationFields() {
    return $this->availableFields;
  }

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return false;
  }

}