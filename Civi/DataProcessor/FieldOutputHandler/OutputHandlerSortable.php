<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

interface OutputHandlerSortable {

  /**
   * @return \Civi\DataProcessor\DataSpecification\FieldSpecification
   */
  public function getSortableInputFieldSpec();

}