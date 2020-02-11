<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataSpecification;

class Utils {

  /**
   * Add fields from a DAO class to a data specification object
   *
   * @param $daoClass
   * @param \Civi\DataProcessor\DataSpecification\DataSpecification $dataSpecification
   * @param array $fieldsToSkip
   * @param string $namePrefix
   * @param string $aliasPrefix
   * @param string $titlePrefix
   *
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public static function addDAOFieldsToDataSpecification($daoClass, DataSpecification $dataSpecification, $fieldsToSkip=array(), $namePrefix='', $aliasPrefix='', $titlePrefix='') {
    $fields = $daoClass::fields();
    foreach($fields as $field) {
      if (in_array($field['name'], $fieldsToSkip)) {
        continue;
      }
      $type = \CRM_Utils_Type::typeToString($field['type']);
      $options = $daoClass::buildOptions($field['name']);
      $alias = $aliasPrefix.$field['name'];
      $name = $namePrefix.$field['name'];
      $title = $titlePrefix.$field['title'];
      $fieldSpec = new FieldSpecification($name, $type, $title, $options, $alias);
      $dataSpecification->addFieldSpecification($fieldSpec->name, $fieldSpec);
    }
  }

  /**
   * Add custom fields to a data specification object
   *
   * @param $entity
   * @param DataSpecification $dataSpecification
   * @param bool $onlySearchAbleFields
   * @param $aliasPrefix
   * @param $titlePrefix
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   * @throws \Exception
   */
  public static function addCustomFieldsToDataSpecification($entity, DataSpecification $dataSpecification, $onlySearchAbleFields, $aliasPrefix = '') {
    $customGroupToReturnParam = [
      'custom_field' => [
        'id',
        'name',
        'label',
        'column_name',
        'data_type',
        'html_type',
        'default_value',
        'attributes',
        'is_required',
        'is_view',
        'is_searchable',
        'help_pre',
        'help_post',
        'options_per_line',
        'start_date_years',
        'end_date_years',
        'date_format',
        'time_format',
        'option_group_id',
        'in_selector',
      ],
      'custom_group' => [
        'id',
        'name',
        'table_name',
        'title',
        'help_pre',
        'help_post',
        'collapse_display',
        'style',
        'is_multiple',
        'extends',
        'extends_entity_column_id',
        'extends_entity_column_value',
        'max_multiple',
      ],
    ];
    $customGroups = \CRM_Core_BAO_CustomGroup::getTree($entity, $customGroupToReturnParam, NULL, NULL, NULL, NULL, NULL, NULL, TRUE, FALSE, FALSE);
    foreach ($customGroups as $cgId => $customGroup) {
      if ($cgId == 'info') {
        continue;
      }
      foreach ($customGroup['fields'] as $field) {
        if (!$onlySearchAbleFields || (isset($field['is_searchable']) && $field['is_searchable'])) {
          $alias = $aliasPrefix . $customGroup['name'] . '_' . $field['name'];
          $customFieldSpec = new CustomFieldSpecification(
            $customGroup['name'], $customGroup['table_name'], $customGroup['title'],
            $field,
            $alias
          );
          $dataSpecification->addFieldSpecification($customFieldSpec->name, $customFieldSpec);
        }
      }
    }
  }

}
