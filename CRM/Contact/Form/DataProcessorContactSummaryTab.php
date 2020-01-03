<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataFlow\SqlDataFlow\SimpleWhereClause;
use Civi\DataProcessor\Exception\DataSourceNotFoundException;
use Civi\DataProcessor\Exception\FieldNotFoundException;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Contact_Form_DataProcessorContactSummaryTab extends CRM_DataprocessorSearch_Form_AbstractSearch {
  public function buildQuickform() {
    parent::buildQuickform();
    $this->add('hidden', 'data_processor');
    $this->setDefaults(array('data_processor' => $this->getDataProcessorName()));
    $this->assign('no_result_text', $this->dataProcessorOutput['configuration']['no_result_text']);
  }

  /**
   * Returns the default row limit.
   *
   * @return int
   */
  protected function getDefaultLimit() {
    $defaultLimit = 25;
    if (!empty($this->dataProcessorOutput['configuration']['default_limit'])) {
      $defaultLimit = $this->dataProcessorOutput['configuration']['default_limit'];
    }
    return $defaultLimit;
  }


  /**
   * Returns the name of the ID field in the dataset.
   *
   * @return string
   */
  protected function getIdFieldName() {
    return false;
  }

  /**
   * @return false|string
   */
  protected function getEntityTable() {
    return false;
  }

  /**
   * Returns the url for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  protected function link($row) {
    return false;
  }

  /**
   * Returns the link text for view of the record action
   *
   * @param $row
   *
   * @return false|string
   */
  protected function linkText($row) {
    return false;
  }

  /**
   * Return the data processor ID
   *
   * @return String
   */
  protected function getDataProcessorName() {
    $dataProcessorName = str_replace('civicrm/dataprocessor_contact_summary/', '', CRM_Utils_System::getUrlPath());
    return $dataProcessorName;
  }

  /**
   * Returns the name of the output for this search
   *
   * @return string
   */
  protected function getOutputName() {
    return 'contact_summary_tab';
  }

  /**
   * Checks whether the output has a valid configuration
   *
   * @return bool
   */
  protected function isConfigurationValid() {
    return TRUE;
  }

  /**
   * Add buttons for other outputs of this data processor
   */
  protected function addExportOutputs() {
    // Don't add exports
  }

  /**
   * Alter the data processor.
   *
   * Use this function in child classes to add for example additional filters.
   *
   * E.g. The contact summary tab uses this to add additional filtering on the contact id of
   * the displayed contact.
   *
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass
   */
  protected function alterDataProcessor(AbstractProcessorType $dataProcessorClass) {
    $cid = CRM_Utils_Request::retrieve('contact_id', 'Integer', $this, true);
    list($datasource_name, $field_name) = explode('::', $this->dataProcessorOutput['configuration']['contact_id_field'], 2);
    $dataSource = $dataProcessorClass->getDataSourceByName($datasource_name);
    if (!$dataSource) {
      throw new DataSourceNotFoundException(E::ts("Requires data source '%1' which could not be found. Did you rename or deleted the data source?", array(1=>$datasource_name)));
    }
    $fieldSpecification  =  $dataSource->getAvailableFilterFields()->getFieldSpecificationByAlias($field_name);
    if (!$fieldSpecification) {
      $fieldSpecification  =  $dataSource->getAvailableFilterFields()->getFieldSpecificationByName($field_name);
    }
    if (!$fieldSpecification) {
      throw new FieldNotFoundException(E::ts("Requires a field with the name '%1' in the data source '%2'. Did you change the data source type?", array(
        1 => $field_name,
        2 => $datasource_name
      )));
    }

    $fieldSpecification = clone $fieldSpecification;
    $fieldSpecification->alias = 'contact_summary_tab_contact_id';
    $dataFlow = $dataSource->ensureField($fieldSpecification);
    if ($dataFlow && $dataFlow instanceof SqlDataFlow) {
      $whereClause = new SimpleWhereClause($dataFlow->getName(), $fieldSpecification->name, '=', $cid, $fieldSpecification->type);
      $dataFlow->addWhereClause($whereClause);
    }
  }
}
