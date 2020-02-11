<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

/**
 * This interface indicates that the output type is accessible from the user interface
 *
 * Interface UIOutputInterface
 *
 * @package Civi\DataProcessor\Output
 */
interface ExportOutputInterface extends OutputInterface {

  /**
   * Download export
   *
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass
   * @param array $dataProcessor
   * @param array $outputBAO
   * @param array $formValues
   * @param string $sortFieldName
   * @param string $sortDirection
   * @param string $idField
   *  Set $idField to the name of the field containing the ID of the array $selectedIds
   * @param array $selectedIds
   *   Array with the selectedIds.
   * @return string
   */
  public function downloadExport(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName=null, $sortDirection='ASC', $idField=null, $selectedIds=array());

  /**
   * Returns the mime type of the export file.
   *
   * @return string
   */
  public function mimeType();

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForExport($output, $dataProcessor);

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return false|string
   */
  public function getExportFileIcon($output, $dataProcessor);

}
