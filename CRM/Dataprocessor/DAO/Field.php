<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Dataprocessor_DAO_Field extends CRM_Core_DAO {
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  static $_export = null;
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_data_processor_field';
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  public static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'title' => E::ts('ID'),
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ) ,
        'data_processor_id' => array(
          'name' => 'data_processor_id',
          'title' => E::ts('Data Processor ID'),
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
          'FKApiName' => 'DataProcessor',
        ),
        'type' => array(
          'name' => 'type',
          'title' => E::ts('Type'),
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 80,
          'required' => true,
        ),
        'name' => array(
          'name' => 'name',
          'title' => E::ts('Name'),
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 128,
          'required' => true
        ),
        'title' => array(
          'name' => 'title',
          'title' => E::ts('Title'),
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 128,
          'required' => true
        ),
        'configuration' => array(
          'name' => 'configuration',
          'title' => E::ts('Configuration'),
          'type' => CRM_Utils_Type::T_TEXT,
        ),
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the array key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  public static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'data_processor_id' => 'data_processor_id',
        'type' => 'type',
        'name' => 'name',
        'title' => 'title',
        'configuration' => 'configuration',
      );
    }
    return self::$_fieldKeys;
  }
}