<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

abstract class AbstractFilterHandler {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  protected $data_processor;

  /**
   * @var bool
   */
  protected $is_required;

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  abstract public function getFieldSpecification();

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param bool $is_required
   * @param array $configuration
   */
  abstract public function initialize($alias, $title, $is_required, $configuration);

  /**
   * @param array $filterParams
   *   The filter settings
   * @return mixed
   */
  abstract public function setFilter($filterParams);

  public function __construct() {

  }

  public function setDataProcessor(AbstractProcessorType $dataProcessor) {
    $this->data_processor = $dataProcessor;
  }

  public function isRequired() {
    return $this->is_required;
  }

  /**
   * Returns the URL to a configuration screen.
   * Return false when no configuration screen is present.
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return false;
  }

}