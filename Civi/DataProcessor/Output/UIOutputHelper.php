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

    $sql = "
    SELECT o.permission, p.id, p.title, o.configuration, o.type, o.id as output_id 
    FROM civicrm_data_processor_output o 
    INNER JOIN civicrm_data_processor p ON o.data_processor_id = p.id 
    WHERE p.is_active = 1";
    $dao = \CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $outputClass = $factory->getOutputByName($dao->type);
      if ($outputClass instanceof \Civi\DataProcessor\Output\UIOutputInterface) {
        $outputs = \CRM_Dataprocessor_BAO_Output::getValues(['id' => $dao->output_id]);
        $output = $outputs[$dao->output_id];
        $dataprocessors = \CRM_Dataprocessor_BAO_DataProcessor::getValues(['id' => $dao->id]);
        $dataprocessor = $dataprocessors[$dao->id];
        $url = $outputClass->getUrlToUi($output, $dataprocessor);

        $configuration = json_decode($dao->configuration, TRUE);
        $title = $dao->title;
        if (isset($configuration['title'])) {
          $title = $configuration['title'];
        }
        $item = [
          'title' => $title,
          'page_callback' => $outputClass->getCallbackForUi(),
          'access_arguments' => [$dao->permission],
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
    if ($objectName != 'DataProcessorOutput') {
      return;
    }
    if ($op == 'delete') {
      $outputs = \CRM_Dataprocessor_BAO_Output::getValues(array('id' => $id));
      if (isset($outputs[$id]['configuration']['navigation_id'])) {
        $navId = $outputs[$id]['configuration']['navigation_id'];
        \CRM_Core_BAO_Navigation::processDelete($navId);
        \CRM_Core_BAO_Navigation::resetNavigation();
        self::$rebuildMenu = TRUE;
      }
    } elseif ($op == 'edit') {
      $outputs = \CRM_Dataprocessor_BAO_Output::getValues(array('id' => $id));
      $output = $outputs[$id];
      if (!isset($output['configuration']['navigation_id']) && !isset($params['configuration']['navigation_parent_path'])) {
        return;
      } elseif (!isset($params['configuration']['navigation_parent_path'])) {
        // Delete the navigation item
        $navId = $outputs[$id]['configuration']['navigation_id'];
        \CRM_Core_BAO_Navigation::processDelete($navId);
        \CRM_Core_BAO_Navigation::resetNavigation();
        self::$rebuildMenu = TRUE;
      } else {
        $dataProcessors = \CRM_Dataprocessor_BAO_DataProcessor::getValues(['id' => $output['data_processor_id']]);
        $dataProcessor = $dataProcessors[$output['data_processor_id']];

        // Retrieve the current navigation params.
        $navigationParams = [];
        if (isset($outputs[$id]['configuration']['navigation_id'])) {
          // Get the default navigation parent id.
          $navigationDefaults = [];
          $navParams = ['id' => $outputs[$id]['configuration']['navigation_id']];
          \CRM_Core_BAO_Navigation::retrieve($navParams, $navigationDefaults);
          if (!empty($navigationDefaults['id'])) {
            $navigationParams['id'] = $navigationDefaults['id'];
            $navigationParams['current_parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
            $navigationParams['parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
          }
        }
        self::$rebuildMenu = self::newNavigationItem($params, $dataProcessor, $navigationParams, $output);
      }
    }
    elseif ($op == 'create' && isset($params['configuration']['navigation_parent_path'])) {
      $dataProcessors = \CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $params['data_processor_id']));
      $dataProcessor = $dataProcessors[$params['data_processor_id']];
      self::$rebuildMenu = self::newNavigationItem($params, $dataProcessor);
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
   * @param array $navigationParams
   *
   * @return bool
   */
  private static function newNavigationItem(&$params, $dataProcessor, $navigationParams=array(), $output=null) {
    $url = "";
    $factory = dataprocessor_get_factory();
    $outputClass = FALSE;
    if (isset($params['type'])) {
      $outputClass = $factory->getOutputByName($params['type']);
    } elseif(isset($output['type'])) {
      $outputClass = $factory->getOutputByName($output['type']);
    }

    if ($outputClass && $outputClass instanceof \Civi\DataProcessor\Output\UIOutputInterface) {
      $url = $outputClass->getUrlToUi($params, $dataProcessor);
    }

    $navigation = \CRM_Dataprocessor_Utils_Navigation::singleton();
    $navigationParams['domain_id'] = \CRM_Core_Config::domainID();
    $navigationParams['permission'] = array();
    $navigationParams['label'] = isset($params['configuration']['title']) ? $params['configuration']['title'] : $dataProcessor['title'];
    $navigationParams['name'] = $dataProcessor['name'];

    $navigationParams['parent_id'] = $navigation->getNavigationIdByPath($params['configuration']['navigation_parent_path']);
    $navigationParams['is_active'] = 1;

    if (isset($params['permission'])) {
      $navigationParams['permission'][] = $params['permission'];
    }

    unset($params['configuration']['navigation_parent_path']);

    $navigationParams['url'] = $url.'?reset=1';
    $navigation = \CRM_Core_BAO_Navigation::add($navigationParams);
    \CRM_Core_BAO_Navigation::resetNavigation();

    $params['configuration']['navigation_id'] = $navigation->id;

    return true;
  }

}