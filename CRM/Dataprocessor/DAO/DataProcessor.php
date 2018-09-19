<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Dataprocessor_DAO_DataProcessor extends CRM_Core_DAO {

  const STATUS_IN_DATABASE = 1;
  const STATUS_IN_CODE = 2;
  const STATUS_OVERRIDDEN = 3;

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
    return 'civicrm_data_processor';
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
        'is_active' => array(
          'name' => 'is_active',
          'title' => E::ts('Is active'),
          'type' => CRM_Utils_Type::T_INT,
        ),
        'description' => array(
          'name' => 'description',
          'title' => E::ts('Description'),
          'type' => CRM_Utils_Type::T_STRING,
        ),
        'configuration' => array(
          'name' => 'configuration',
          'title' => E::ts('Configuration'),
          'type' => CRM_Utils_Type::T_TEXT,
        ),
        'storage_type' => array(
          'name' => 'storage_type',
          'title' => E::ts('Storage Type'),
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 80,
          'required' => true,
        ),
        'storage_configuration' => array(
          'name' => 'storage_configuration',
          'title' => E::ts('Storage Configuration'),
          'type' => CRM_Utils_Type::T_TEXT,
        ),
        'status' => array(
          'name' => 'status',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'source_file' => array(
          'name' => 'source_file',
          'title' => E::ts('Source file'),
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 255,
          'required' => false,
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
        'type' => 'type',
        'name' => 'name',
        'title' => 'title',
        'is_active' => 'is_active',
        'description' => 'description',
        'configuration' => 'configuration',
        'storage_type' => 'storage_type',
        'storage_configuration' => 'storage_configuration',
        'status' => 'status',
        'source_file' => 'source_file',
      );
    }
    return self::$_fieldKeys;
  }
}