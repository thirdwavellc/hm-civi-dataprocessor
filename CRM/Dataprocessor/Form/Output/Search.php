<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Output_Search extends CRM_Dataprocessor_Form_Output_AbstractOutputForm {

  protected $_navigation = array();

  protected static $navigationPathToIds = array();

  protected static $navigationIdToPaths = array();

  protected static $navigationIdToParentId = array();

  protected static $navigationTree = array();

  public function buildQuickForm() {
    parent::buildQuickForm();

    $dataProcessor = CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorById($this->dataProcessorId);
    $fields = array();
    foreach($dataProcessor->getDataFlow()->getOutputFieldHandlers() as $outputFieldHandler) {
      $field = $outputFieldHandler->getOutputFieldSpecification();
      $fields[$field->alias] = $field->title;
    }

    $this->add('text', 'title', E::ts('Title'), true);

    $this->add('select','permission', E::ts('Permission'), CRM_Core_Permission::basicPermissions(), true, array('class' => 'crm-select2 crm-huge40'));
    $this->add('select', 'contact_id_field', E::ts('Contact ID field'), $fields, true, array('class' => 'crm-select2 crm-huge40'));

    // navigation field
    $navigationTree = $this->getNavigationTree();
    if (isset($this->output['configuration']['navigation_id'])) {
      $navigationId = $this->output['configuration']['navigation_id'];
      $navigationPath = self::$navigationIdToPaths[$navigationId];
      unset($navigationTree[$navigationPath]);
    }

    $this->add('select', 'navigation_parent_path', ts('Parent Menu'), array('' => ts('- select -')) + $navigationTree, true);
  }

  protected static function getNavigationTree() {
    if (!self::$navigationTree) {
      self::$navigationTree = array();
      $tree = CRM_Core_BAO_Navigation::buildNavigationTree();
      foreach($tree as $item) {
        self::buildNavigationTree($item);
      }
    }
    return self::$navigationTree;
  }

  protected static function buildNavigationTree($item, $path='', $prefix='') {
    if (strlen($path)) {
      $path .= '/'.$item['attributes']['name'];
    } else {
      $path = $item['attributes']['name'];
    }
    self::$navigationIdToPaths[$item['attributes']['navID']] = $path;
    self::$navigationPathToIds[$path] = $item['attributes']['navID'];
    if (isset($item['attributes']['parentID'])) {
      self::$navigationIdToParentId[$item['attributes']['navID']] = $item['attributes']['parentID'];
    }
    self::$navigationTree[$path] = $prefix.$item['attributes']['label'];

    if (isset($item['child'])) {
      foreach ($item['child'] as $child) {
        self::buildNavigationTree($child, $path, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
      }
    }
  }

  function setDefaultValues() {
    $dataProcessors = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $this->dataProcessorId));
    $dataProcessor = $dataProcessors[$this->dataProcessorId];

    $defaults = parent::setDefaultValues();
    if ($this->output) {
      if (isset($this->output['permission'])) {
        $defaults['permission'] = $this->output['permission'];
      }
      if (isset($this->output['configuration']) && is_array($this->output['configuration'])) {
        if (isset($this->output['configuration']['contact_id_field'])) {
          $defaults['contact_id_field'] = $this->output['configuration']['contact_id_field'];
        }
        if (isset($this->output['configuration']['navigation_id'])) {
          $defaults['navigation_id'] = $this->output['configuration']['navigation_id'];
        }
        if (isset($this->output['configuration']['title'])) {
          $defaults['title'] = $this->output['configuration']['title'];
        }
      }
    }
    if (!isset($defaults['permission'])) {
      $defaults['permission'] = 'access CiviCRM';
    }
    if (empty($defaults['title'])) {
      $defaults['title'] = $dataProcessor['title'];
    }

    $navigationDefaults = array();
    if (!empty($defaults['navigation_id'])) {
      // Get the default navigation parent id.
      $params = array('id' => $defaults['navigation_id']);
      CRM_Core_BAO_Navigation::retrieve($params, $navigationDefaults);
      $defaults['navigation_parent_id'] = CRM_Utils_Array::value('parent_id', $navigationDefaults);

      if (!empty($navigationDefaults['id'])) {
        $this->_navigation['id'] = $navigationDefaults['id'];
        $this->_navigation['parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
      }
    }

    if (isset($defaults['navigation_parent_id']) && isset(self::$navigationIdToPaths[$defaults['navigation_parent_id']])) {
      $defaults['navigation_parent_path'] = self::$navigationIdToPaths[$defaults['navigation_parent_id']];
    }

    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();

    $session = CRM_Core_Session::singleton();
    $redirectUrl = $session->readUserContext();

    $params['id'] = $this->id;
    $params['permission'] = $values['permission'];
    $params['configuration']['title'] = $values['title'];
    $params['configuration']['contact_id_field'] = $values['contact_id_field'];
    $params['configuration']['navigation_parent_path'] = $values['navigation_parent_path'];

    CRM_Dataprocessor_BAO_Output::add($params);

    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

  /**
   * Delete navigation when an output gets deleted
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public static function preHook($op, $objectName, $id, &$params) {
    $rebuildMenu = false;
    if ($objectName != 'DataProcessorOutput') {
      return;
    }
    if ($op == 'delete') {
      $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $id));
      if (isset($outputs[$id]['configuration']['navigation_id'])) {
        $navId = $outputs[$id]['configuration']['navigation_id'];
        CRM_Core_BAO_Navigation::processDelete($navId);
        CRM_Core_BAO_Navigation::resetNavigation();
        $rebuildMenu = true;
      }
    } elseif ($op == 'edit') {
      $outputs = CRM_Dataprocessor_BAO_Output::getValues(array('id' => $id));
      $output = $outputs[$id];
      if (!isset($output['configuration']['navigation_id']) && !isset($params['configuration']['navigation_parent_path'])) {
        return;
      } elseif (!isset($params['configuration']['navigation_parent_path'])) {
        // Delete the navigation item
        $navId = $outputs[$id]['configuration']['navigation_id'];
        CRM_Core_BAO_Navigation::processDelete($navId);
        CRM_Core_BAO_Navigation::resetNavigation();
        return;
      }

      self::getNavigationTree();
      $dataProcessors = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $output['data_processor_id']));
      $dataProcessor = $dataProcessors[$output['data_processor_id']];

      $navigationParams = array();
      if (isset($outputs[$id]['configuration']['navigation_id'])) {
        // Get the default navigation parent id.
        $navigationDefaults = array();
        $navParams = array('id' => $outputs[$id]['configuration']['navigation_id']);
        CRM_Core_BAO_Navigation::retrieve($navParams, $navigationDefaults);
        if (!empty($navigationDefaults['id'])) {
          $navigationParams['id'] = $navigationDefaults['id'];
          $navigationParams['current_parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
          $navigationParams['parent_id'] = !empty($navigationDefaults['parent_id']) ? $navigationDefaults['parent_id'] : NULL;
        }
      }

      $navigationParams['permission'] = array();
      $navigationParams['label'] = isset($params['configuration']['title']) ? $params['configuration']['title'] : $dataProcessor['title'];
      $navigationParams['name'] = $dataProcessor['name'];

      $navigationParams['parent_id'] = self::$navigationPathToIds[$params['configuration']['navigation_parent_path']];
      $navigationParams['is_active'] = 1;

      if (isset($params['permission'])) {
        $navigationParams['permission'][] = $params['permission'];
      }

      unset($params['configuration']['navigation_parent_path']);

      $navigationParams['url'] = "civicrm/dataprocessor_search/{$dataProcessor['id']}?reset=1";
      $navigation = CRM_Core_BAO_Navigation::add($navigationParams);
      CRM_Core_BAO_Navigation::resetNavigation();
      $params['configuration']['navigation_id'] = $navigation->id;
      $rebuildMenu = true;
    } elseif ($op == 'create' && isset($params['configuration']['navigation_parent_path'])) {
      self::getNavigationTree();
      $dataProcessors = CRM_Dataprocessor_BAO_DataProcessor::getValues(array('id' => $params['data_processor_id']));
      $dataProcessor = $dataProcessors[$params['data_processor_id']];

      $navigationParams = array();

      $navigationParams['permission'] = array();
      $navigationParams['label'] = isset($params['configuration']['title']) ? $params['configuration']['title'] : $dataProcessor['title'];
      $navigationParams['name'] = $dataProcessor['name'];

      $navigationParams['parent_id'] = self::$navigationPathToIds[$params['configuration']['navigation_parent_path']];
      $navigationParams['is_active'] = 1;

      if (isset($params['permission'])) {
        $navigationParams['permission'][] = $params['permission'];
      }

      unset($params['configuration']['navigation_parent_path']);

      $navigationParams['url'] = "civicrm/dataprocessor_search/{$dataProcessor['id']}?reset=1";
      $navigation = CRM_Core_BAO_Navigation::add($navigationParams);
      CRM_Core_BAO_Navigation::resetNavigation();
      $params['configuration']['navigation_id'] = $navigation->id;

      $rebuildMenu = true;
    }

    if ($rebuildMenu) {
      // Rebuild the CiviCRM Menu (which holds all the pages)
      CRM_Core_Menu::store();
    }
  }

  public static function hookExport(&$dataProcessor) {
    self::getNavigationTree();
    foreach($dataProcessor['outputs'] as $idx => $output) {
      if (isset($output['configuration']['navigation_id'])) {
        $navParentId = self::$navigationIdToParentId[$output['configuration']['navigation_id']];
        $navParentPath = self::$navigationIdToPaths[$navParentId];
        $dataProcessor['outputs'][$idx]['configuration']['navigation_parent_path'] = $navParentPath;
        unset($dataProcessor['outputs'][$idx]['configuration']['navigation_id']);
      }
    }
  }

}