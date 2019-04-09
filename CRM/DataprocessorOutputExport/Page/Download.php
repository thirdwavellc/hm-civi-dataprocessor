<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorOutputExport_Page_Download extends CRM_Core_Page {

  /**
   * Run page.
   */
  public function run() {
    $factory = dataprocessor_get_factory();

    $download = CRM_Utils_Request::retrieve('download', 'Integer', $this, FALSE, 1);
    $disposition = $download == 0 ? 'inline' : 'download';

    $directory = CRM_Utils_Request::retrieve('directory', 'String', $this, FALSE);
    if (!empty($directory)) {
      $directory = $directory.'/';
    }
    $fileName = CRM_Utils_Request::retrieve('filename', 'String', $this, FALSE);
    if (empty($fileName)) {
      CRM_Core_Error::statusBounce("Cannot access file");
    }

    if (!self::isValidFileName($fileName)) {
      CRM_Core_Error::statusBounce("Malformed filename");
    }

    list($prefix, $dataProcessorId, $outputId, $userId, $download_name) = explode("_", $fileName);
    $download_name = $prefix.'_'.$download_name;

    $data_processors = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $dataProcessorId));
    $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $outputId));
    $outputClass = $factory->getOutputByName($outputs[$outputId]['type']);
    if (!$outputClass instanceof \Civi\DataProcessor\Output\ExportOutputInterface) {
      CRM_Core_Error::statusBounce("Malformed filename");
    } elseif ($userId != CRM_Core_Session::getLoggedInContactID()) {
      CRM_Core_Error::statusBounce("Malformed filename");
    }


    $path = CRM_Core_Config::singleton()->templateCompileDir . $directory. $fileName;
    $mimeType = $outputClass->mimeType();

    if (!$path) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $buffer = file_get_contents($path);
    if (!$buffer) {
      CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    CRM_Utils_System::download(
      $download_name,
      $mimeType,
      $buffer,
      NULL,
      TRUE,
      $disposition
    );
  }

  /**
   * Is the filename a safe and valid filename passed in from URL
   *
   * @param string $fileName
   * @return bool
   */
  protected static function isValidFileName($fileName = NULL) {
    if ($fileName) {
      $check = $fileName !== basename($fileName) ? FALSE : TRUE;
      if ($check) {
        if (substr($fileName, 0, 1) == '/' || substr($fileName, 0, 1) == '.' || substr($fileName, 0, 1) == DIRECTORY_SEPARATOR) {
          $check = FALSE;
        }
      }
      return $check;
    }
    return FALSE;
  }

}