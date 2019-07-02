<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow\SqlDataFlow;

use Civi\DataProcessor\DataFlow\SqlDataFlow\WhereClauseInterface;

class AndClause implements WhereClauseInterface {

  /**
   * @var WhereClauseInterface[]
   */
  protected $clauses = array();

  protected $isJoinClause = FALSE;

  /**
   * OrClause constructor.
   *
   * @param WhereClauseInterface[] $clauses
   */
  public function __construct($clauses=array(), $isJoinClause=FALSE) {
    $this->isJoinClause = $isJoinClause;
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
   * Returns true when this where clause can be added to the
   * join or whether this clause should be propagated to the where part of the query
   *
   * @return bool
   */
  public function isJoinClause() {
    return $this->isJoinClause;
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
      return "(" . implode(" AND ", $clauses) . ")";
    }
    return "1";
  }

}