<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorSearch_Utils_PrevNextCache {

  /**
   * Delete an item from the prevnext cache table based on the entity.
   *
   * @param int $id
   * @param string $cacheKey
   */
  public function deleteItem($id = NULL, $cacheKey = NULL) {
    if (Civi::container()->has('prevnext')) {
      return Civi::service('prevnext')->deleteItem($id, $cacheKey);
    } else {
      // Backwards compatibility
      $sql = "DELETE FROM civicrm_prevnext_cache WHERE (1)";
      $params = [];

      if (is_numeric($id)) {
        $sql .= " AND ( entity_id1 = %2 OR entity_id2 = %2 )";
        $params[2] = [$id, 'Integer'];
      }

      if (isset($cacheKey)) {
        $sql .= " AND cacheKey = %3";
        $params[3] = [$cacheKey, 'String'];
      }
      CRM_Core_DAO::executeQuery($sql, $params);
    }
  }

  public static function fillWithArray($cacheKey, $rows) {
    if (Civi::container()->has('prevnext')) {
      return Civi::service('prevnext')->fillWithArray($cacheKey, $rows);
    } else {
      // Backwards compatibility
      if (empty($rows)) {
        return;
      }

      $insert = CRM_Utils_SQL_Insert::into('civicrm_prevnext_cache')
        ->columns([
          'entity_table',
          'entity_id1',
          'entity_id2',
          'cacheKey',
          'data'
        ]);

      foreach ($rows as &$row) {
        $insert->row($row + ['cacheKey' => $cacheKey, 'entity_id2' => $row['entity_id1']]);
      }

      CRM_Core_DAO::executeQuery($insert->toSQL());
      return TRUE;
    }
    return;
  }

  /**
   * Get the selections.
   *
   * @param string $cacheKey
   *   Cache key.
   * @param string $action
   *   One of the following:
   *   - 'get' - get only selection records
   *   - 'getall' - get all the records of the specified cache key
   *
   * @return array|NULL
   */
  public function getSelection($cacheKey, $action = 'get') {
    if (Civi::container()->has('prevnext')) {
      Civi::service('prevnext')->getSelection($cacheKey, $action);
    } else {
      // Backwards compatibility
      if (!$cacheKey) {
        return NULL;
      }
      $params = [];

      if ($cacheKey && ($action == 'get' || $action == 'getall')) {
        $actionGet = ($action == "get") ? " AND is_selected = 1 " : "";
        $sql = "
  SELECT entity_id1 FROM civicrm_prevnext_cache
  WHERE cacheKey = %1
        $actionGet
  ORDER BY id
  ";
        $params[1] = [$cacheKey, 'String'];

        $contactIds = [$cacheKey => []];
        $cIdDao = CRM_Core_DAO::executeQuery($sql, $params);
        while ($cIdDao->fetch()) {
          $contactIds[$cacheKey][$cIdDao->entity_id1] = 1;
        }
        return $contactIds;
      }
    }
  }

}