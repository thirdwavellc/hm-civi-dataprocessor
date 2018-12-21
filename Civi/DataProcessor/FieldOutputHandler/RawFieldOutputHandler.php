<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\DataProcessor\Source\SourceInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

class RawFieldOutputHandler extends AbstractFieldOutputHandler implements OutputHandlerSortable{

  /**
   * @var \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  protected $inputFieldSpec;

  public function __construct(FieldSpecification $inputFieldSpec, SourceInterface $dataSource) {
    parent::__construct($dataSource);
    $this->inputFieldSpec = $inputFieldSpec;
    $this->outputFieldSpecification = clone $inputFieldSpec;
    $this->outputFieldSpecification->alias = $this->getName();
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getSortableInputFieldSpec() {
    return $this->inputFieldSpec;
  }

  /**
   * Returns the name of the handler type.
   *
   * @return String
   */
  public function getName() {
    return 'raw_'.$this->inputFieldSpec->alias;
  }

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return $this->inputFieldSpec->type;
  }

  /**
   * Returns the title of this field
   *
   * @return String
   */
  public function getTitle() {
    return E::ts('%1::%2 (Raw)', array(1 => $this->dataSource->getSourceTitle(), 2 => $this->inputFieldSpec->title));
  }

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param array $configuration
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $processorType
   */
  public function initialize($alias, $title, $configuration) {
    parent::initialize($alias, $title, $configuration);
    $this->dataSource->ensureFieldInSource($this->inputFieldSpec);
  }

  /**
   * Returns the formatted value
   *
   * @param $rawRecord
   * @param $formattedRecord
   *
   * @return \Civi\DataProcessor\FieldOutputHandler\FieldOutput
   */
  public function formatField($rawRecord, $formattedRecord) {
    return new FieldOutput($rawRecord[$this->inputFieldSpec->alias]);
  }


}