<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Dataprocessor_DAO_Output extends CRM_Core_DAO {
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
    return 'civicrm_data_processor_output';
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
        'configuration' => array(
          'name' => 'configuration',
          'title' => E::ts('Configuration'),
          'type' => CRM_Utils_Type::T_TEXT,
        ),
        'api_permission' => array(
          'name' => 'api_permission',
          'title' => E::ts('API Permission'),
          'type' => CRM_Utils_Type::T_STRING
        ),
        'api_entity' => array(
          'name' => 'api_entity',
          'title' => E::ts('API Entity'),
          'type' => CRM_Utils_Type::T_STRING
        ),
        'api_action' => array(
          'name' => 'api_action',
          'title' => E::ts('API Action Name'),
          'type' => CRM_Utils_Type::T_STRING
        ),
        'api_count_action' => array(
          'name' => 'api_count_action',
          'title' => E::ts('API GetCount Action Name'),
          'type' => CRM_Utils_Type::T_STRING
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
        'configuration' => 'configuration',
      );
    }
    return self::$_fieldKeys;
  }
}