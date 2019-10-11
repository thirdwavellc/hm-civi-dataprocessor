<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

/**
 * Class UIOutputHelper
 *
 * An helper class for the UIOutput
 *
 * @package Civi\DataProcessor\Output
 */
class UIOutputHelper {

  private static $rebuildMenu = false;

  /**
   * Delegation of the alter menu hook. Add the search outputs to the menu system.
   *
   * @param $items
   */
  public static function alterMenu(&$items) {
    $factory = dataprocessor_get_factory();
    // Check whether the factory exists. Usually just after
    // installation the factory does not exists but then no
    // outputs exists either. So we can safely return this function.
    if (!$factory) {
      return;
    }

    $sql = "
    SELECT o.permission, p.id, p.title, o.configuration, o.type, o.id as output_id 
    FROM civicrm_data_processor_output o 
    INNER JOIN civicrm_data_processor p ON o.data_processor_id = p.id 
    WHERE p.is_active = 1";
    $dao = \CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $outputClass = $factory->getOutputByName($dao->type);
      if ($outputClass instanceof \Civi\DataProcessor\Output\UIOutputInterface) {
        $output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $dao->output_id));
        $dataprocessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dao->id));
        $url = $outputClass->getUrlToUi($output, $dataprocessor);

        $configuration = json_decode($dao->configuration, TRUE);
        $title = $outputClass->getTitleForUiLink($output, $dataprocessor);
        $item = [
          'title' => $title,
          'page_callback' => $outputClass->getCallbackForUi(),
          'access_arguments' => [[$dao->permission], 'and'],
        ];
        $items[$url] = $item;
      }
    }
  }

  /**
   * Update the navigation data when an output is saved/deleted from the database.
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public static function preHook($op, $objectName, $id, &$params) {
    if ($objectName == 'DataProcessorOutput') {
      if ($op == 'delete') {
        $output = civicrm_api3('DataProcessorOutput', 'getsingle', ['id' => $id]);
        self::removeOutputFromNavigation($output['configuration']);
      }
      elseif ($op == 'edit') {
        $output = civicrm_api3('DataProcessorOutput', 'getsingle', ['id' => $id]);
        if (!isset($output['configuration']['navigation_id']) && !isset($params['configuration']['navigation_parent_path'])) {
          return;
        }
        elseif (!isset($params['configuration']['navigation_parent_path'])) {
          self::removeOutputFromNavigation($output['configuration']);
        }
        elseif (isset($params['configuration']['navigation_parent_path'])) {
          if (!isset($output['configuration']['navigation_parent_path']) || $params['configuration']['navigation_parent_path'] != $output['configuration']['navigation_parent_path']) {
            // Merge the current output from the database with the updated values
            $configuration = array_merge($output['configuration'], $params['configuration']);
            $configuration['navigation_parent_path'] = $params['configuration']['navigation_parent_path'];
            $output = array_merge($output, $params);
            $output['configuration'] = $configuration;
            $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', ['id' => $output['data_processor_id']]);
            $configuration = self::createOrUpdateNavigationItem($output, $dataProcessor);
            if ($configuration) {
              $params['configuration'] = $configuration;
            }
          }
        }
      }
      elseif ($op == 'create' && isset($params['configuration']['navigation_parent_path'])) {
        $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', ['id' => $params['data_processor_id']]);
        $configuration = self::createOrUpdateNavigationItem($params, $dataProcessor);
        if ($configuration) {
          $params['configuration'] = $configuration;
        }
      }
    } elseif ($objectName == 'DataProcessor' && $op == 'edit') {
      $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', ['id' => $id]);
      if (isset($params['is_active']) && $params['is_active'] != $dataProcessor['is_active']) {
        // Only update navigation when is active is changed
        $dataProcessor = array_merge($dataProcessor, $params);
        $outputs = civicrm_api3('DataProcessorOutput', 'get', ['data_processor_id' => $id, 'options' => ['limit' => 0]]);
        foreach($outputs['values'] as $output) {
          if (isset($output['configuration']['navigation_id'])) {
            self::createOrUpdateNavigationItem($output, $dataProcessor);
          }
        }
      }
    }
  }

  /**
   * Remove an output from the navigation menu.
   *
   * @param array $configuration
   */
  private static function removeOutputFromNavigation($configuration) {
    if (isset($configuration['navigation_id'])) {
      $navId = $configuration['navigation_id'];
      \CRM_Core_BAO_Navigation::processDelete($navId);
      \CRM_Core_BAO_Navigation::resetNavigation();
      self::$rebuildMenu = TRUE;
    }
  }

  /**
   * Update the navigation data when an output is saved/deleted from the database.
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public static function postHook($op, $objectName, $id, &$objectRef) {
    if ($objectName != 'DataProcessorOutput') {
      return;
    }

    if (self::$rebuildMenu) {
      // Rebuild the CiviCRM Menu (which holds all the pages)
      \CRM_Core_Menu::store();

      // also reset navigation
      \CRM_Core_BAO_Navigation::resetNavigation();
      self::$rebuildMenu = false;
    }
  }

  /**
   * Convert the navigation_id to the parent path of the navigation
   * @param $dataProcessor
   */
  public static function hookExport(&$dataProcessor) {
    $navigation = \CRM_Dataprocessor_Utils_Navigation::singleton();
    foreach($dataProcessor['outputs'] as $idx => $output) {
      if (isset($output['configuration']['navigation_id'])) {
        $dataProcessor['outputs'][$idx]['configuration']['navigation_parent_path'] = $navigation->getNavigationParentPathById($output['configuration']['navigation_id']);
        unset($dataProcessor['outputs'][$idx]['configuration']['navigation_id']);
      }
    }
  }

  /**
   * Inserts/updates an navigation item.
   * @param $params
   * @param $dataProcessor
   *
   * @return array
   */
  private static function createOrUpdateNavigationItem($output, $dataProcessor) {
    $url = "";
    $factory = dataprocessor_get_factory();
    if (!isset($output['type'])) {
      return false;
    }
    $outputClass = $factory->getOutputByName($output['type']);
    $configuration = $output['configuration'];

    $title = $dataProcessor['title'];
    if ($outputClass && $outputClass instanceof \Civi\DataProcessor\Output\UIOutputInterface) {
      $url = $outputClass->getUrlToUi($output, $dataProcessor);
      $title = $outputClass->getTitleForUiLink($output, $dataProcessor);
    }

    $navigation = \CRM_Dataprocessor_Utils_Navigation::singleton();
    $navigationParams = array();
    // Retrieve the current navigation ID.
    if (isset($configuration['navigation_id'])) {
      // Get the default navigation parent id.
      $navigationDefaults = [];
      $retrieveNavParams = ['id' => $configuration['navigation_id']];
      \CRM_Core_BAO_Navigation::retrieve($retrieveNavParams, $navigationDefaults);
      if (!empty($navigationDefaults['id'])) {
        $navigationParams['id'] = $navigationDefaults['id'];
        $navigationParams['current_parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
        $navigationParams['parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
      }
    }

    $navigationParams['domain_id'] = \CRM_Core_Config::domainID();
    $navigationParams['permission'] = array();
    $navigationParams['label'] = $title;
    $navigationParams['name'] = $dataProcessor['name'];

    if (isset($configuration['navigation_parent_path'])) {
      $navigationParams['parent_id'] = $navigation->getNavigationIdByPath($configuration['navigation_parent_path']);
    }
    $navigationParams['is_active'] = $dataProcessor['is_active'];

    if (isset($output['permission'])) {
      $navigationParams['permission'][] = $output['permission'];
    }

    unset($configuration['navigation_parent_path']);

    $navigationParams['url'] = $url.'?reset=1';
    $navigation = \CRM_Core_BAO_Navigation::add($navigationParams);
    \CRM_Core_BAO_Navigation::resetNavigation();

    $configuration['navigation_id'] = $navigation->id;

    self::$rebuildMenu = TRUE;

    return $configuration;
  }

}
