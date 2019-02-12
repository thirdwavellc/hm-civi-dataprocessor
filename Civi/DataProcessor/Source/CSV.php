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
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFields() {
    $this->initialize();
    return $this->availableFields;
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