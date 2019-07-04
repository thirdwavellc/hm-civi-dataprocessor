<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorSearch_Form_Task_SearchActionDesigner extends CRM_Searchactiondesigner_Form_Task_Task {

  protected function setEntityShortName() {
    self::$entityShortname = 'DataprocessorSearch';
  }


}