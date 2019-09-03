<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

interface Markupable {

  /**
   * Return the output as markup.
   *
   * @return String
   */
  public function getMarkupOut();

}
