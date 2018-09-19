<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor;

use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

class Factory {

  /**
   * @var array<String>
   */
  protected $types = array();


  /**
   * @var array<String>
   */
  protected $typesClasses = array();

  /**
   * @var array<String>
   */
  protected $sources = array();


  /**
   * @var array<String>
   */
  protected $sourceClasses = array();

  /**
   * @var array<String>
   */
  protected $outputs = array();


  /**
   * @var array<String>
   */
  protected $outputClasses = array();

  /**
   * @var array<String>
   */
  protected $joins = array();


  /**
   * @var array<String>
   */
  protected $joinClasses = array();


  public function __construct() {
    $this->addDataProcessorType('default', 'Civi\DataProcessor\ProcessorType\DefaultProcessorType', 'Default');
    $this->addDataSource('contact', 'Civi\DataProcessor\Source\ContactSource', 'Contact');
    $this->addDataSource('contribution', 'Civi\DataProcessor\Source\ContributionSource', 'Contribution');
    $this->addDataSource('relationship', 'Civi\DataProcessor\Source\RelationshipSource', 'Relationship');
    $this->addOutput('api', 'Civi\DataProcessor\Output\Api', 'API');
    $this->addjoinType('simple_join', 'Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin', 'Simple Join');
  }

  /**
   * @return array<String>
   */
  public function getDataProcessorTypes() {
    return $this->types;
  }

  /**
   * @param $name
   *
   * @return AbstractProcessorType
   */
  public function getDataProcessorTypeByName($name) {
    return new $this->typesClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getDataSources() {
    return $this->sources;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function getDataSourceByName($name) {
    return new $this->sourceClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getOutputs() {
    return $this->outputs;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\Output\OutputInterface
   */
  public function getOutputByName($name) {
    return new $this->outputClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getJoins() {
    return $this->joins;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function getJoinByName($name) {
    return new $this->joinClasses[$name]();
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addjoinType($name, $class, $label) {
    $this->joinClasses[$name] = $class;
    $this->joins[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addDataProcessorType($name, $class, $label) {
    $this->typesClasses[$name] = $class;
    $this->types[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addDataSource($name, $class, $label) {
    $this->sourceClasses[$name] = $class;
    $this->sources[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addOutput($name, $class, $label) {
    $this->outputClasses[$name] = $class;
    $this->outputs[$name] = $label;
    return $this;
  }

}