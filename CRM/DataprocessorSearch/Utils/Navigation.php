<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorSearch_Utils_Navigation {

  private $navigationPathToIds = array();

  private $navigationIdToPaths = array();

  private $navigationIdToParentId = array();

  private $navigationOptions = array();

  private static $singelton;

  private function __construct() {
    $tree = CRM_Core_BAO_Navigation::buildNavigationTree();
    foreach($tree as $item) {
      $this->buildNavigationTree($item);
    }
  }

  /**
   * @return \CRM_DataprocessorSearch_Utils_Navigation
   */
  public static function singleton() {
    if (!self::$singelton) {
      self::$singelton = new CRM_DataprocessorSearch_Utils_Navigation();
    }
    return self::$singelton;
  }

  public function getNavigationOptions() {
    return $this->navigationOptions;
  }

  public function getNavigationPathById($navId) {
    return $this->navigationIdToPaths[$navId];
  }

  public function getNavigationIdByPath($path) {
    return $this->navigationPathToIds[$path];
  }

  public function getNavigationParentPathById($navId) {
    $parent_id = $this->navigationIdToParentId[$navId];
    return $this->navigationIdToPaths[$parent_id];
  }

  private function buildNavigationTree($item, $path='', $prefix='') {
    if (strlen($path)) {
      $path .= '/'.$item['attributes']['name'];
    } else {
      $path = $item['attributes']['name'];
    }
    $this->navigationIdToPaths[$item['attributes']['navID']] = $path;
    $this->navigationPathToIds[$path] = $item['attributes']['navID'];
    if (isset($item['attributes']['parentID'])) {
      $this->navigationIdToParentId[$item['attributes']['navID']] = $item['attributes']['parentID'];
    }
    $this->navigationOptions[$path] = $prefix.$item['attributes']['label'];

    if (isset($item['child'])) {
      foreach ($item['child'] as $child) {
        $this->buildNavigationTree($child, $path, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
      }
    }
  }


}