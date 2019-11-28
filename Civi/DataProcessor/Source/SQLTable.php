<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source;

use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

use CRM_Dataprocessor_ExtensionUtil as E;

class SQLTable extends AbstractSource {

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFields;

  /**
   * @var \Civi\DataProcessor\DataSpecification\DataSpecification
   */
  protected $availableFilterFields;

  public function __construct() {
    parent::__construct();
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return $this->configuration['table_name'] ?? NULL;
  }

  /**
   * Returns the default configuration for this data source
   *
   * @return array
   */
  public function getDefaultConfiguration() {
    return [];
  }

  /**
   * Initialize this data source.
   *
   * @throws \Exception
   */
  public function initialize() {
    if ($this->dataFlow) {
      return;
    }
    $this->dataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
  }

  /**
   * Load the fields from this entity.
   *
   * @param DataSpecification $dataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  protected function loadFields(DataSpecification $dataSpecification, $fieldsToSkip=[]) {
    $dao = \CRM_Core_DAO::executeQuery('SHOW COLUMNS FROM ' . $this->getTable());
    while ($dao->fetch()) {
      if (in_array($dao->Field, $fieldsToSkip)) {
        continue;
      }
      // @fixme: This could be improved...
      // Map the sql table types to ones that CiviCRM/dataprocessor understands
      switch (substr($dao->Type,0,3)) {
        case 'int':
          $type = 'Integer';
          break;

        case 'var': // varchar
        case 'cha': // char
          $type = 'String';
          break;

        case 'tex':
          $type = 'Text';
          break;

        case 'dat': // datetime
          $type = 'Date';
          break;

        case 'tin': // tinyint
          $type = 'Boolean';
          break;

        case 'dec': // decimal
          $type = 'Money';
          break;

        case 'dou': // double
          $type = 'Float';
          break;

        default:
          $type = ucfirst($dao->Type);
      }
      $name = $dao->Field;
      $title = $dao->Field;
      $alias = $this->getSourceName().$name;
      $fieldSpec = new FieldSpecification($name, $type, $title, NULL, $alias);
      $dataSpecification->addFieldSpecification($fieldSpec->name, $fieldSpec);
    }
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFields() {
    if (!$this->availableFields) {
      $this->availableFields = new DataSpecification();
      $this->loadFields($this->availableFields, []);
    }
    return $this->availableFields;
  }

  /**
   * Returns true when this source has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return TRUE;
  }

  /**
   * When this source has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $source
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $source=[]) {
    $form->add('text', 'table_name', E::ts('SQL Table Name'), true);

    $defaults = [];
    foreach($source['configuration'] as $field => $value) {
      $defaults[$field] = $value;
    }
    $form->setDefaults($defaults);
  }

  /**
   * When this source has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Dataprocessor/Form/Source/Sqltable.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    $configuration = [];
    $configuration['table_name'] = $submittedValues['table_name'];

    return $configuration;
  }

}
