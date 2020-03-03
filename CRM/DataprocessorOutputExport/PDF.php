<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\Output\ExportOutputInterface;
use Civi\DataProcessor\Output\DirectDownloadExportOutputInterface;

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_DataprocessorOutputExport_PDF implements ExportOutputInterface, DirectDownloadExportOutputInterface {

  const MAX_DIRECT_SIZE = 500;

  const RECORDS_PER_JOB = 250;

  /**
   * Returns true when this filter has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this filter type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $filter
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $output=array()) {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $output['data_processor_id']));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    $fields = array();
    foreach($dataProcessorClass->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $fields[$field->alias] = $field->title;
    }

    $pdfFormats = array();
    $pdfFormatsApi = civicrm_api3('OptionValue', 'get', ['option_group_id' => 'pdf_format', 'options' => ['limit' => 0]]);
    foreach($pdfFormatsApi['values'] as $pdfFormat) {
      $pdfFormats[$pdfFormat['id']] = $pdfFormat['label'];
    }

    $form->add('select', 'hidden_fields', E::ts('Hidden fields'), $fields, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'multiple' => true,
      'placeholder' => E::ts('- select -'),
    ));

    $form->add('select', 'pdf_format', E::ts('PDF Format'), $pdfFormats, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'multiple' => false,
      'placeholder' => E::ts('- Default PDF Format -'),
    ));
    $form->assign('ManagePdfFormatUrl', CRM_Utils_System::url('civicrm/admin/pdfFormats', ['reset'=>1]));

    $borderStyles = [
      'full' => E::ts('Border around every cell'),
      'row_bottom' => E::ts('Border at the bottom of the row'),
    ];
    $form->add('select', 'border', E::ts('Border'), $borderStyles, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'multiple' => false,
      'placeholder' => E::ts('- No border -'),
    ));

    $form->add('checkbox', 'anonymous', E::ts('Available for anonymous users'), array(), false);

    $form->add('wysiwyg', 'header', E::ts('Header'), array('rows' => 6, 'cols' => 80));

    $form->add('checkbox', 'additional_column', E::ts('Add an additional column'), array(), false);
    $form->add('text', 'additional_column_title', E::ts('Additional column title'));
    $form->add('text', 'additional_column_width', E::ts('Additional column width'));
    $form->add('text', 'additional_column_height', E::ts('Additional column height'));

    $configuration = false;
    if ($output && isset($output['configuration'])) {
      $configuration = $output['configuration'];
    }
    if ($configuration && isset($configuration['hidden_fields'])) {
      $defaults['hidden_fields'] = $configuration['hidden_fields'];
    }
    if ($configuration && isset($configuration['pdf_format'])) {
      $defaults['pdf_format'] = $configuration['pdf_format'];
    }
    if ($configuration && isset($configuration['header'])) {
      $defaults['header'] = $configuration['header'];
    }
    if ($configuration && isset($configuration['anonymous'])) {
      $defaults['anonymous'] = $configuration['anonymous'];
    }
    if ($configuration && isset($configuration['additional_column'])) {
      $defaults['additional_column'] = $configuration['additional_column'];
    }
    if ($configuration && isset($configuration['additional_column_title'])) {
      $defaults['additional_column_title'] = $configuration['additional_column_title'];
    }
    if ($configuration && isset($configuration['additional_column_width'])) {
      $defaults['additional_column_width'] = $configuration['additional_column_width'];
    }
    if ($configuration && isset($configuration['additional_column_height'])) {
      $defaults['additional_column_height'] = $configuration['additional_column_height'];
    }
    if ($configuration && isset($configuration['border'])) {
      $defaults['border'] = $configuration['border'];
    }
    $form->setDefaults($defaults);
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/DataprocessorOutputExport/Form/Configuration/PDF.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $output
   * @return array
   */
  public function processConfiguration($submittedValues, &$output) {
    $configuration = array();
    $configuration['hidden_fields'] = $submittedValues['hidden_fields'];
    $configuration['pdf_format'] = $submittedValues['pdf_format'];
    $configuration['header'] = $submittedValues['header'];
    $configuration['anonymous'] = $submittedValues['anonymous'];
    $configuration['additional_column'] = $submittedValues['additional_column'];
    $configuration['additional_column_title'] = $submittedValues['additional_column_title'];
    $configuration['additional_column_width'] = $submittedValues['additional_column_width'];
    $configuration['additional_column_height'] = $submittedValues['additional_column_height'];
    $configuration['border'] = $submittedValues['border'];
    return $configuration;
  }

  /**
   * This function is called prior to removing an output
   *
   * @param array $output
   * @return void
   */
  public function deleteOutput($output) {
    // Do nothing
  }


  /**
   * Returns the mime type of the export file.
   *
   * @return string
   */
  public function mimeType() {
    return 'application/pdf';
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForExport($output, $dataProcessor) {
    return E::ts('Download as PDF');
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string|false
   */
  public function getExportFileIcon($output, $dataProcessor) {
    return '<i class="fa fa-file-pdf-o">&nbsp;</i>';
  }

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
   * @throws \Exception
   */
  public function downloadExport(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName = null, $sortDirection = 'ASC', $idField=null, $selectedIds=array()) {
    if ($dataProcessorClass->getDataFlow()->recordCount() > self::MAX_DIRECT_SIZE) {
      $this->startBatchJob($dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName, $sortDirection, $idField, $selectedIds);
    } else {
      $this->doDirectDownload($dataProcessorClass, $dataProcessor, $outputBAO, $sortFieldName, $sortDirection, $idField, $selectedIds);
    }
  }

  public function doDirectDownload(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $sortFieldName = null, $sortDirection = 'ASC', $idField, $selectedIds=array()) {
    $filename = date('Ymdhis').'_'.$dataProcessor['id'].'_'.$outputBAO['id'].'_'.CRM_Core_Session::getLoggedInContactID().'_'.$dataProcessor['name'].'.html';
    $download_name = date('Ymdhis').'_'.$dataProcessor['name'].'.pdf';

    $basePath = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_pdf';
    CRM_Utils_File::createDir($basePath);
    CRM_Utils_File::restrictAccess($basePath.'/');

    $path = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_pdf/'. $filename;
    if ($sortFieldName) {
      $dataProcessorClass->getDataFlow()->addSort($sortFieldName, $sortDirection);
    }

    self::createHeader($path, $dataProcessorClass, $outputBAO['configuration'], $dataProcessor);
    self::exportDataProcessor($path, $dataProcessorClass, $outputBAO['configuration'], $idField, $selectedIds);
    $path = self::createFooter($path, $dataProcessorClass, $outputBAO['configuration'], $dataProcessor);

    $mimeType = $this->mimeType();

    if (!$path) {
      \CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $buffer = file_get_contents($path);
    if (!$buffer) {
      \CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    \CRM_Utils_System::download(
      $download_name,
      $mimeType,
      $buffer,
      NULL,
      TRUE,
      'download'
    );
  }


  protected function startBatchJob(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName = null, $sortDirection = 'ASC', $idField=null, $selectedIds=array()) {
    $session = CRM_Core_Session::singleton();

    $name = date('Ymdhis').'_'.$dataProcessor['id'].'_'.$outputBAO['id'].'_'.CRM_Core_Session::getLoggedInContactID().'_'.$dataProcessor['name'];

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => $name,
      'reset' => TRUE, //do flush queue upon creation
    ));

    $basePath = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_pdf';
    CRM_Utils_File::createDir($basePath);
    CRM_Utils_File::restrictAccess($basePath.'/');
    $filename = $basePath.'/'. $name.'.html';

    self::createHeader($filename, $dataProcessorClass, $outputBAO['configuration'], $dataProcessor);

    $count = $dataProcessorClass->getDataFlow()->recordCount();
    $recordsPerJob = self::RECORDS_PER_JOB;
    for($i=0; $i < $count; $i = $i + $recordsPerJob) {
      $title = E::ts('Exporting records %1/%2', array(
        1 => ($i+$recordsPerJob) <= $count ? $i+$recordsPerJob : $count,
        2 => $count,
      ));

      //create a task without parameters
      $task = new CRM_Queue_Task(
        array(
          'CRM_DataprocessorOutputExport_PDF',
          'exportBatch'
        ), //call back method
        array($filename,$formValues, $dataProcessor['id'], $outputBAO['id'], $i, $recordsPerJob, $sortFieldName, $sortDirection, $idField, $selectedIds), //parameters,
        $title
      );
      //now add this task to the queue
      $queue->createItem($task);
    }

    $task = new CRM_Queue_Task(
      array(
        'CRM_DataprocessorOutputExport_PDF',
        'exportBatchFooter'
      ), //call back method
      array($filename,$formValues, $dataProcessor['id'], $outputBAO['id'], $i, $recordsPerJob, $sortFieldName, $sortDirection, $idField, $selectedIds), //parameters,
      $title
    );
    //now add this task to the queue
    $queue->createItem($task);

    $url = str_replace("&amp;", "&", $session->readUserContext());

    $runner = new CRM_Queue_Runner(array(
      'title' => E::ts('Exporting data'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE, //abort upon error and keep task in queue
      'onEnd' => array('CRM_DataprocessorOutputExport_PDF', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => $url,
    ));

    $runner->runAllViaWeb(); // does not return
  }

  protected static function createHeader($filename, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $configuration, $dataProcessor) {
    $borderStyle = "";
    if (isset($configuration['border']) && $configuration['border'] == 'full') {
      $borderStyle = "th, td { border: 1px solid black; padding-left: 4px; padding-right: 4px; }";
    } elseif (isset($configuration['border']) && $configuration['border'] == 'row_bottom') {
      $borderStyle = "th, td { border-bottom: 1px solid black; }";
    }

    $content = "
        <html>
        <head>
        <title>{$dataProcessor['title']}</title>
        </head>
        <body>
        <style>
        table { border-collapse: collapse; }
        {$borderStyle}
        </style>
        <h1>{$dataProcessor['title']}</h1>
    ";

    if (isset($configuration['header'])) {
      $content .= $configuration['header'];
    }

    $content .= "<table style=\"width: 100%;\"><thead><tr>";
    $hiddenFields = array();
    if (isset($configuration['hidden_fields']) && is_array($configuration['hidden_fields'])) {
      $hiddenFields = $configuration['hidden_fields'];
    }
    foreach($dataProcessorClass->getDataFlow()->getOutputFieldHandlers() as $outputHandler) {
      if (!in_array($outputHandler->getOutputFieldSpecification()->alias, $hiddenFields)) {
        $content .= "<th>" . self::encodeValue($outputHandler->getOutputFieldSpecification()->title) . "</th>";
      }
    }

    if (isset($configuration['additional_column']) && $configuration['additional_column']) {
      $additionalColumnStyle = "";
      if (isset($configuration['additional_column_width']) && $configuration['additional_column_width']) {
        $additionalColumnStyle .= "width: {$configuration['additional_column_width']};";
      }
      $content .= "<th style=\"{$additionalColumnStyle}\">";
      if (isset($configuration['additional_column_title']) && $configuration['additional_column_title']) {
        $content .= $configuration['additional_column_title'];
      }
      $content .= "</th>";
    }

    $content .= "</tr></thead>";
    $file = fopen($filename, 'a');
    fwrite($file, $content."\r\n");
    fclose($file);
  }

  protected static function createFooter($filename, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $configuration, $dataProcessor) {
    $content = "</table></body></html>";
    $file = fopen($filename, 'a');
    fwrite($file, $content."\r\n");
    fclose($file);

    $content = file_get_contents($filename);
    unlink($filename);
    $pdfFilename = str_replace(".html", ".pdf", $filename);
    $pdfFormat = isset($configuration['pdf_format']) ? $configuration['pdf_format'] : null;
    $pdfContents = \CRM_Utils_PDF_Utils::html2pdf($content, basename($pdfFilename), TRUE, $pdfFormat);
    $file = fopen($pdfFilename, 'a');
    fwrite($file, $pdfContents."\r\n");
    fclose($file);

    return $pdfFilename;
  }

  protected static function exportDataProcessor($filename, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor, $configuration, $idField, $selectedIds=array()) {
    $hiddenFields = array();
    if (isset($configuration['hidden_fields']) && is_array($configuration['hidden_fields'])) {
      $hiddenFields = $configuration['hidden_fields'];
    }

    $additionalColumn = '';
    if (isset($configuration['additional_column']) && $configuration['additional_column']) {
      $style = "";
      if (isset($configuration['additional_column_width']) && $configuration['additional_column_width']) {
        $style .= "width: {$configuration['additional_column_width']};";
      }
      if (isset($configuration['additional_column_height']) && $configuration['additional_column_height']) {
        $style .= "height: {$configuration['additional_column_height']};";
      }
      $additionalColumn = "<td style=\"{$style}\">&nbsp;</td>";
    }

    $content = "";
    try {
      while($record = $dataProcessor->getDataFlow()->nextRecord()) {
        $row = array();
        $rowIsSelected = true;
        if (isset($idField) && is_array($selectedIds) && count($selectedIds)) {
          $rowIsSelected = false;
          $id = $record[$idField]->rawValue;
          if (in_array($id, $selectedIds)) {
            $rowIsSelected = true;
          }
        }
        if ($rowIsSelected) {
          $content .= "<tr>";
          foreach ($record as $field => $value) {
            if (!in_array($field, $hiddenFields)) {
              $content .= "<td>" . self::encodeValue($value->formattedValue) . "</td>";
            }
          }
          $content .= $additionalColumn . "</tr>";
        }
      }
    } catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }
    $file = fopen($filename, 'a');
    fwrite($file, $content . "\r\n");
    fclose($file);
  }

  protected static function encodeValue($value) {
    return htmlentities($value);
  }

  public static function exportBatch(CRM_Queue_TaskContext $ctx, $filename, $params, $dataProcessorId, $outputId, $offset, $limit, $sortFieldName = null, $sortDirection = 'ASC', $idField=null, $selectedIds=array()) {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dataProcessorId));
    $output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $outputId));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    CRM_Dataprocessor_Form_Output_AbstractUIOutputForm::applyFilters($dataProcessorClass, $params);
    if ($sortFieldName) {
      $dataProcessorClass->getDataFlow()->addSort($sortFieldName, $sortDirection);
    }
    $dataProcessorClass->getDataFlow()->setOffset($offset);
    $dataProcessorClass->getDataFlow()->setLimit($limit);
    self::exportDataProcessor($filename, $dataProcessorClass, $output['configuration'], $idField, $selectedIds);
    return TRUE;
  }

  public static function exportBatchFooter(CRM_Queue_TaskContext $ctx, $filename, $params, $dataProcessorId, $outputId, $offset, $limit, $sortFieldName = null, $sortDirection = 'ASC', $idField=null, $selectedIds=array()) {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dataProcessorId));
    $output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $outputId));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    CRM_Dataprocessor_Form_Output_AbstractUIOutputForm::applyFilters($dataProcessorClass, $params);
    if ($sortFieldName) {
      $dataProcessorClass->getDataFlow()->addSort($sortFieldName, $sortDirection);
    }
    $dataProcessorClass->getDataFlow()->setOffset($offset);
    $dataProcessorClass->getDataFlow()->setLimit($limit);
    self::createFooter($filename, $dataProcessorClass, $output['configuration'], $dataProcessor);
    return TRUE;
  }

  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    $queue_name = $ctx->queue->getName();
    $pdf_filename = $queue_name.'.pdf';
    $downloadLink = CRM_Utils_System::url('civicrm/dataprocessor/form/output/download', 'filename='.$pdf_filename.'&directory=dataprocessor_export_pdf');
    //set a status message for the user
    CRM_Core_Session::setStatus(E::ts('<a href="%1">Download PDF file</a>', array(1=>$downloadLink)), E::ts('Exported data'), 'success');
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getUrl($output, $dataProcessor) {
    return CRM_Utils_System::url('civicrm/dataprocessor/output/export', array(
      'name' => $dataProcessor['name'],
      'type' => $output['type']
    ));
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForLink($output, $dataProcessor) {
    return $dataProcessor['title'];
  }

  /**
   * Checks whether the current user has access to this output
   *
   * @param array $output
   * @param array $dataProcessor
   * @return bool
   */
  public function checkPermission($output, $dataProcessor) {
    $anonymous = false;
    if (isset($output['configuration']) && isset($output['configuration']['anonymous'])) {
      $anonymous = $output['configuration']['anonymous'] ? true : false;
    }
    $userId = \CRM_Core_Session::getLoggedInContactID();
    if ($userId) {
      return true;
    } elseif ($anonymous) {
      return true;
    }
    return false;
  }


}
