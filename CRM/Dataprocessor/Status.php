<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_Dataprocessor_Status {

  const STATUS_IN_DATABASE = 1;
  const STATUS_IN_CODE = 2;
  const STATUS_OVERRIDDEN = 3;

  /**
   * Returns a textual representation of the status
   *
   * @param $status
   * @return string
   */
  public static function statusToLabel($status) {
    switch ($status) {
      case CRM_Dataprocessor_Status::STATUS_IN_CODE:
        return E::ts('In code');
        break;
      case CRM_Dataprocessor_Status::STATUS_OVERRIDDEN:
        return E::ts('Overridden');
        break;
      case CRM_Dataprocessor_Status::STATUS_IN_DATABASE:
        return E::ts('In database');
        break;
    }
    return E::ts('Unknown');
  }

}