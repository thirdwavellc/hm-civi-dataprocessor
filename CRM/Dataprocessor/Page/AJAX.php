<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 */

use Civi\DataProcessor\FieldOutputHandler\FieldOutput;
use Civi\DataProcessor\FieldOutputHandler\Markupable;

/**
 * This class contains all contact related functions that are called using AJAX (jQuery)
 */
class CRM_Dataprocessor_Page_AJAX {

  public static function getDashlet() {

  	$dataProcessorName = CRM_Utils_Request::retrieve('dataProcessorName', 'String');
  	$dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('name' => $dataProcessorName));
  	$dataProcessorClass = CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);

  	$results = [];

  	try {
      while($record = $dataProcessorClass->getDataFlow()->nextRecord()) {
        $row = [];
        $row['record'] = $record;
        $result = [];
        foreach ($record as $key => $value) {
          if ($value instanceof Markupable) {
            $result[$key] = $value->getMarkupOut();
          }
          elseif ($value instanceof FieldOutput) {
            $result[$key] = $value->formattedValue;
          }
        }
        $results[] = $result;
      }
    } catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }

    $return_output['data'] = $results;

    CRM_Utils_JSON::output($return_output);

  }

}
