<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class CustomFieldSpecification extends FieldSpecification {

  public $customGroupName;

  public $customGroupTableName;

  public $customFieldColumnName;

  public $customFieldName;

  public $customFieldTitle;

  public $customGroupTitle;

  public $customFieldId;

  public function __construct($custom_group_name, $custom_group_table_name, $custom_group_title, $id, $column_name, $name, $type, $title, $alias) {
    $this->customFieldColumnName = $column_name;
    $this->customGroupName = $custom_group_name;
    $this->customGroupTableName = $custom_group_table_name;
    $this->customGroupTitle = $custom_group_title;
    $this->customFieldId = $id;
    $this->customFieldName = $name;
    $this->customFieldTitle = $title;

    $options = \CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_' . $id, array(), 'search');
    parent::__construct($column_name, $type, $custom_group_title. ': '.$title, $options, $alias);

  }

}