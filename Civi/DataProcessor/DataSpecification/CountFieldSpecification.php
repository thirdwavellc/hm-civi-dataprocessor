<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class CountFieldSpecification extends FieldSpecification {

  /**
   * Returns the select statement for this field.
   * E.g. COUNT(civicrm_contact.id) AS contact_id_count
   *
   * @param String $table_alias
   * @return string
   */
  public function getSqlSelectStatement($table_alias) {
    return "COUNT(`{$table_alias}`.`{$this->name}`) AS `{$this->alias}`";
  }

}