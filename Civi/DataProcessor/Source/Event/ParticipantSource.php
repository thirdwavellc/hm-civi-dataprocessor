<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Event;

use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use Civi\DataProcessor\Utils\AlterExportInterface;
use CRM_Dataprocessor_ExtensionUtil as E;

class ParticipantSource extends AbstractCivicrmEntitySource implements AlterExportInterface {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Participant';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_participant';
  }

  /**
   * Function to alter the export data.
   * E.g. use this to convert ids to names
   *
   * @param array $data
   *
   * @return array
   */
  public function alterExportData($data) {
    if (isset($data['configuration']) && is_array($data['configuration'])) {
      $configuration = $data['configuration'];

      if (isset($configuration['filter']['status_id'])) {
        $status_ids = [];
        foreach ($configuration['filter']['status_id']['value'] as $status_id) {
          try {
            $status_ids[] = civicrm_api3('ParticipantStatusType', 'getvalue', [
              'id' => $status_id,
              'return' => 'name'
            ]);
          } catch (\CiviCRM_API3_Exception $ex) {
            $status_ids[] = $status_id;
          }
        }
        $configuration['filter']['status_id']['value'] = $status_ids;
      }

      if (isset($configuration['filter']['role_id'])) {
        $roles = [];
        foreach ($configuration['filter']['role_id']['value'] as $role_id) {
          try {
            $roles[] = civicrm_api3('OptionValue', 'getvalue', [
              'option_group_id' => 'participant_role',
              'value' => $role_id,
              'return' => 'name'
            ]);
          } catch (\CiviCRM_API3_Exception $ex) {
            $roles[] = $role_id;
          }
        }
        $configuration['filter']['role_id']['value'] = $roles;
      }
      $data['configuration'] = $configuration;
    }
    return $data;
  }

  /**
   * Function to alter the export data.
   * E.g. use this to convert names to ids
   *
   * @param array $data
   *
   * @return array
   */
  public function alterImportData($data) {
    if (isset($data['configuration']) && is_array($data['configuration'])) {
      $configuration = $data['configuration'];
      if (isset($configuration['filter']['status_id'])) {
        $status_ids = [];
        foreach ($configuration['filter']['status_id']['value'] as $status_name) {
          try {
            $status_ids[] = civicrm_api3('ParticipantStatusType', 'getvalue', [
              'name' => $status_name,
              'return' => 'id'
            ]);
          } catch (\CiviCRM_API3_Exception $ex) {
            $status_ids[] = $status_name;
          }
        }
        $configuration['filter']['status_id']['value'] = $status_ids;
      }

      if (isset($configuration['filter']['role_id'])) {
        $roles = [];
        foreach ($configuration['filter']['role_id']['value'] as $role_name) {
          try {
            $roles[] = civicrm_api3('OptionValue', 'getvalue', [
              'option_group_id' => 'participant_role',
              'name' => $role_name,
              'return' => 'value'
            ]);
          } catch (\CiviCRM_API3_Exception $ex) {
            $roles[] = $role_name;
          }
        }
        $configuration['filter']['role_id']['value'] = $roles;
      }
      $data['configuration'] = $configuration;
    }
    return $data;
  }


}
