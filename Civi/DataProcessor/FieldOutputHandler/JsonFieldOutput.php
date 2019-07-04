<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\FieldOutputHandler;

class JsonFieldOutput extends FieldOutput implements arrayFieldOutput {

  protected $data;

  public function setData($data) {
    $this->data = $data;
  }

  public function getArrayData() {
    return $this->data;
  }

}