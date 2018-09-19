<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class DataSpecification {

  /**
   * @var <FieldSpecification> array
   */
  protected $fields = array();

  public function __construct($fields=array()) {
    foreach($fields as $field) {
      $this->addFieldSpecification($field);
    }
  }

  /**
   * Add a field specification
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $field
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function addFieldSpecification(FieldSpecification $field) {
    if (isset($this->fields[$field->name])) {
      throw new FieldExistsException($field->name);
    }
    $this->fields[$field->name] = $field;
    return $this;
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification[]
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * @param string
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getFieldSpecificationByName($name) {
    return $this->fields[$name];
  }

  /**
   * Merge with another dataspecification.
   *
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification
   * @param string $prefix
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function merge(DataSpecification $dataSpecification, $prefix='') {
    foreach($dataSpecification->getFields() as $field) {
      $f = clone $field;
      $f->name = $prefix.$field->name;
      $this->addFieldSpecification($f);
    }
    return $this;
  }

}