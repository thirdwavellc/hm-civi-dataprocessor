<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

interface SourceInterface {

  /**
   * Returns the data flow.
   *
   * @return AbstractDataFlow
   */
  public function getDataFlow();


  /**
   * Initialize this data source.
   *
   * @param array $configuration
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function initialize($configuration);

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFields();

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  public function getAvailableFilterFields();

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl();

  /**
   * Ensures a field is in the data source
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification);

  /**
   * @return String
   */
  public function getSourceName();

  /**
   * @param String $name
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceName($name);

  /**
   * @return String
   */
  public function getSourceTitle();

  /**
   * @param String $title
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function setSourceTitle($title);

}