<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

interface SqlFieldSpecification {

  /**
   * Returns the select statement for this field.
   * E.g. COUNT(civicrm_contact.id) AS contact_id_count
   *
   * @param String $table_alias
   * @return String
   */
  public function getSqlSelectStatement($table_alias);

  /**
   * Returns the group by statement for this field.
   * E.g. civicrm_contribution.financial_type_id
   * or MONTH(civicrm_contribution.receive_date)
   *
   * @param String $table_alias
   * @return String
   */
  public function getSqlGroupByStatement($table_alias);

  /**
   * Returns the SQL column name for this field.
   * This could be used in join statements
   *
   * @param $table_alias
   * @return string
   */
  public function getSqlColumnName($table_alias);

}
