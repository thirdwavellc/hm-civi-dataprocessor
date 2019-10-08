<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Utils_Extensions {

  /**
   * Helper function for extension to load and unload data processors from the extension.
   *
   * @param $extension
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function updateDataProcessorsFromExtension($extension) {
    $result = civicrm_api3('DataProcessor', 'import', ['extension' => $extension]);
    if (isset($result['import']['kept data processors']) && count($result['import']['kept data processors'])) {
      $message = E::ts('Kept data processors because you made changes to it.');
      $message .= '<ul>';
      foreach($result['import']['kept data processors'] as $dataprocessor) {
        $message .= '<li>'.$dataprocessor.'</li>';
      }
      $message .= '</ul>';
      CRM_Core_Session::setStatus($message, E::ts('Kept data processors'), 'info', ['expires' => 0]);
    }
  }

}
