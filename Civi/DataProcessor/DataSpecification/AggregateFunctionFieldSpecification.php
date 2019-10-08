<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

use Civi\DataProcessor\Exception\InvalidConfigurationException;
use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Class used to add aggregate functions (such as SUM) to a database query.
 *
 * @package Civi\DataProcessor\DataSpecification
 */
class AggregateFunctionFieldSpecification extends FieldSpecification {

  protected $function;

  public function setAggregateFunction($function) {
    $functions = self::functionList();
    if (!isset($functions[$function])) {
      throw new InvalidConfigurationException(E::ts('Field %1 has an invalid aggregate function.', [1=>$this->title]));
    }
    $this->function = $function;
  }

  /**
   * Convert a fieldSpecification into the aggregate function field specification.
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   * @param $function
   *
   * @return \Civi\DataProcessor\DataSpecification\AggregateFunctionFieldSpecification
   */
  public static function convertFromFieldSpecification(FieldSpecification $fieldSpecification, $function) {
    $return = new AggregateFunctionFieldSpecification($fieldSpecification->name, $fieldSpecification->type, $fieldSpecification->title, $fieldSpecification->options, $fieldSpecification->alias);
    $return->setAggregateFunction($function);
    return $return;
  }

  /**
   * Returns the select statement for this field.
   * E.g. COUNT(civicrm_contact.id) AS contact_id_count
   *
   * @param String $table_alias
   * @return string
   */
  public function getSqlSelectStatement($table_alias) {
    $function_arg = "";
    $function = $this->function;
    if ($function == 'COUNT_DISTINCT') {
      $function = 'COUNT';
      $function_arg = 'DISTINCT';
    }
    return "{$function}({$function_arg}`{$table_alias}`.`{$this->name}`) AS `{$this->alias}`";
  }

  /**
   * Returns the valid aggregate functions
   *
   * @return array
   */
  public static function functionList() {
    return [
      'SUM' => E::ts('Sum'),
      'AVG' => E::ts('Average'),
      'MIN' => E::ts('Minimum'),
      'MAX' => E::ts('Maximum'),
      'STDDEV_POP' => E::ts('Standard deviation'),
      'STDDEV_SAMP' => E::ts('Sample standard deviation'),
      'VAR_POP' => E::ts('Standard variance'),
      'VAR_SAMP' => E::ts('Sample variance'),
      'COUNT' => E::ts('Count'),
      'COUNT_DISTINCT' => E::ts('Distinct count')
    ];
  }

}
