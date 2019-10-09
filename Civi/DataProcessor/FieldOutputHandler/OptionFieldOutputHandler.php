<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

use CRM_Dataprocessor_ExtensionUtil as E;
use Civi\DataProcessor\Source\SourceInterface;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;

class OptionFieldOutputHandler extends AbstractSimpleFieldOutputHandler {


  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return 'String';
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
    $rawValue = $rawRecord[$this->inputFieldSpec->alias];
    if (strpos($rawValue, \CRM_Core_DAO::VALUE_SEPARATOR) !== false) {
      $rawValue = explode(\CRM_Core_DAO::VALUE_SEPARATOR, substr($rawValue,1, -1));
    } elseif (is_string($rawValue) && strlen($rawValue) > 0) {
      $rawValue = array($rawValue);
    }
    $formattedOptions = array();
    $options = $this->inputFieldSpec->getOptions();
    if (is_array($rawValue)) {
      foreach ($rawValue as $v) {
        $formattedOptions[] = $options[$v];
      }
    }
    $formattedValue = new FieldOutput($rawRecord[$this->inputFieldSpec->alias]);
    $formattedValue->formattedValue = implode(",", $formattedOptions);
    return $formattedValue;
  }

  /**
   * Callback function for determining whether this field could be handled by this output handler.
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $field
   * @return bool
   */
  public function isFieldValid(FieldSpecification $field) {
    if ($field->getOptions()) {
      return true;
    }
    return false;
  }


}
