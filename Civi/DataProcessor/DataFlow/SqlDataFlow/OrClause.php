<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

use Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface;

class OrClause implements WhereClauseInterface {

  /**
   * @var WhereClauseInterface[]
   */
  protected $clauses = array();

  /**
   * OrClause constructor.
   *
   * @param WhereClauseInterface[] $clauses
   */
  public function __construct($clauses=array()) {
    $this->clauses = $clauses;
  }

  /**
   * Add a where clause to this clause
   *
   * @param \Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface $clause
   */
  public function addWhereClause(WhereClauseInterface $clause) {
    $this->clauses[] = $clause;
  }

  /**
   * Returns the where clause
   * E.g. contact_type = 'Individual'
   *
   * @return string
   */
  public function getWhereClause() {
    if (count($this->clauses)) {
      $clauses = array();
      foreach($this->clauses as $clause) {
        $clauses[] = "(". $clause->getWhereClause() . ")";
      }
      return "(" . implode(" OR ", $clauses) . ")";
    }
    return "1";
  }

}