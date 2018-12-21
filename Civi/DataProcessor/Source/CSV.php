<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;


use Civi\DataProcessor\DataFlow\CsvDataFlow;
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
    $uri = $this->configuration['uri'];
    $skipRows = 0;
    $headerRowNumber = 0;
    if (isset($this->configuration['first_row_as_header']) && $this->configuration['first_row_as_header']) {
      $skipRows = 1;
      $headerRowNumber = 1;
    }
    $delimiter = $this->configuration['delimiter'];
    $enclosure = $this->configuration['enclosure'];
    $escape = $this->configuration['escape'];
    $this->dataFlow = new CsvDataFlow($uri, $skipRows, $delimiter, $enclosure, $escape);
    $this->headerRow = $this->dataFlow->getHeaderRow($headerRowNumber);

    foreach($this->headerRow as  $idx => $colName) {
      $name = 'col_'.$idx;
      $alias = $this->getSourceName().$name;
      $field = new FieldSpecification($name, 'String', $colName, null, $alias);
      $this->availableFields->addFieldSpecification($name, $field);
    }
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
    if ($field) {
      $this->dataFlow->getDataSpecification()
        ->addFieldSpecification($fieldName, $field);
    }
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
    return array();
  }

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/source/csv';
  }

}