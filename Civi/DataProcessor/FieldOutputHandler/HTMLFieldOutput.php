<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

class HTMLFieldOutput extends FieldOutput implements Markupable {

  protected $html;

  /**
   * Sets the HTML value
   *
   * @param $html
   */
  public function setHtmlOutput($html) {
    $this->html = $html;
  }

  /**
   * Return the output as markup.
   *
   * @return String
   */
  public function getMarkupOut() {
    if ($this->html) {
      return $this->html;
    }
    return $this->formattedValue;
  }

}
