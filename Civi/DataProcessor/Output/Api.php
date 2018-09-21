<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

use Civi\API\Event\ResolveEvent;
use Civi\API\Event\RespondEvent;
use Civi\API\Events;
use Civi\API\Provider\ProviderInterface as API_ProviderInterface;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Api implements OutputInterface, API_ProviderInterface, EventSubscriberInterface{

  public function __construct() {

  }

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    // Some remarks on the solution to implement the getFields method.
    // We would like to have implemented it as a normal api call. Meaning
    // it would been processed in the invoke method.
    //
    // However the reflection provider has a stop propegation so we cannot use
    // getfields here unles we are earlier then the reflection provider.
    // We should then use a weight lower than Events::W_EARLY and do a
    // stop propegation in our event. But setting an early flag is not a neat way to do stuff.
    // So instead we use the Respond event and check in the respond event whether the action is getFields and
    // if so do our getfields stuff there.
    return array(
      Events::RESOLVE => array('onApiResolve'),
      Events::RESPOND => array('onGetFieldsRepsonse'), // we use this method to add our field definition to the getFields action.
    );
  }

  public function onApiResolve(ResolveEvent $event) {
    $apiRequest = $event->getApiRequest();
    if (strtolower($apiRequest['entity']) == 'dataprocessorapi') {
      $event->setApiProvider($this);
    }
  }

  /**
   * Event listener on the ResponddEvent to handle the getfields actions.
   * So the fields defined by the user are availble in the api explorer for example.
   */
  public function onGetFieldsRepsonse(RespondEvent $event) {
    $apiRequest = $event->getApiRequest();
    $params = $apiRequest['params'];
    $result = $event->getResponse();

    // First check whether the entity is dataprocessorapi and the action is getfields.
    // If not return this function.
    if (strtolower($apiRequest['entity']) != 'dataprocessorapi' || strtolower($apiRequest['action']) != 'getfields') {
      return;
    }
    // Now check whether the action param is set. With the action param we can find the data processor.
    if (isset($params['action'])) {
      if (stripos($params['action'], 'getcount') === 0) {
        return; // Is a get count action.
      }
      // Find the data processor
      try {
        $dataProcessor = \CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorByOutputTypeAndName('api', $params['action']);

        foreach ($dataProcessor->getDataFlow()->getDataSpecification()->getFields() as $fieldSpec) {
          $field = [
            'name' => $fieldSpec->alias,
            'title' => $fieldSpec->title,
            'description' => '',
            'type' => $fieldSpec->type,
            'api.required' => FALSE,
            'api.aliases' => [],
          ];
          if ($fieldSpec->getOptions()) {
            $field['options'] = $fieldSpec->getOptions();
          }
          $result['values'][$fieldSpec->alias] = $field;
        }
        foreach($dataProcessor->getFilterHandlers() as $filterHandler) {
          $fieldSpec = $filterHandler->getFieldSpecification();
          $field = [
            'name' => $fieldSpec->alias,
            'title' => $fieldSpec->title,
            'description' => '',
            'type' => $fieldSpec->type,
            'api.required' => $filterHandler->isRequired(),
            'api.aliases' => [],
          ];
          if ($fieldSpec->getOptions()) {
            $field['options'] = $fieldSpec->getOptions();
          }
          if (!isset($result['values'][$fieldSpec->alias])) {
            $result['values'][$fieldSpec->alias] = $field;
          } else {
            $result['values'][$fieldSpec->alias] = array_merge($result['values'][$fieldSpec->alias], $field);
          }
        }
      } catch(\Exception $e) {
        // Do nothing.
      }

      $result['count'] = count($result['values']);
      $event->setResponse($result);
    }
  }


  /**
   * @param array $apiRequest
   *   The full description of the API request.
   * @return array
   *   structured response data (per civicrm_api3_create_success)
   * @see civicrm_api3_create_success
   * @throws \API_Exception
   */
  public function invoke($apiRequest) {
    // Check whether we do a count...
    $dataProcessorName = $apiRequest['action'];
    $isCountAction = false;
    if (stripos($apiRequest['action'], 'getcount') === 0) {
      $isCountAction = true;
      $dataProcessorName = substr($apiRequest['action'], 8);
    }
    // Find the form processor
    $dataProcessor = \CRM_Dataprocessor_BAO_DataProcessor::getDataProcessorByOutputTypeAndName('api', $dataProcessorName);
    if (!$dataProcessor instanceof AbstractProcessorType) {
      throw new \API_Exception('Could not find a form processor');
    }

    $params = $apiRequest['params'];
    foreach($dataProcessor->getFilterHandlers() as $filter) {
      $filterSpec = $filter->getFieldSpecification();
      if ($filter->isRequired() && !isset($params[$filterSpec->alias])) {
        throw new \API_Exception('Field '.$filterSpec->alias.' is required');
      }
      if (isset($params[$filterSpec->alias])) {
        if (!is_array($params[$filterSpec->alias])) {
          $filterParams = [
            'op' => '=',
            'value' => $params[$filterSpec->alias],
          ];
        } else {
          $value = reset($params[$filterSpec->alias]);
          $filterParams = [
            'op' => key($params[$filterSpec->alias]),
            'value' => $value,
          ];
        }
        $filter->setFilter($filterParams);
      }
    }

    if ($isCountAction) {
      $count = $dataProcessor->getDataFlow()->recordCount();
      return array('count' => $count, 'is_error' => 0);
    } else {
      $options = _civicrm_api3_get_options_from_params($apiRequest['params']);

      if (isset($options['limit'])) {
        $dataProcessor->getDataFlow()->setLimit($options['limit']);
      }
      if (isset($options['offset'])) {
        $dataProcessor->getDataFlow()->setOffset($options['offset']);
      }
      $records = $dataProcessor->getDataFlow()->allRecords();
      $values = array();
      foreach($records as $idx => $record) {
        foreach($record as $fieldname => $field) {
          $values[$idx][$fieldname] = $field->formattedValue;
        }
      }
      $return = array(
        'values' => $values,
        'count' => count($values),
        'is_error' => 0,
      );
      if (isset($apiRequest['params']['debug']) && $apiRequest['params']['debug']) {
        $return['debug_info'] = $dataProcessor->getDataFlow()->getDebugInformation();
      }
      return $return;
    }
  }

  /**
   * @param int $version
   *   API version.
   * @return array<string>
   */
  public function getEntityNames($version) {
    return array(
      'DataProcessorApi'
    );
  }

  /**
   * @param int $version
   *   API version.
   * @param string $entity
   *   API entity.
   * @return array<string>
   */
  public function getActionNames($version, $entity) {
    if (strtolower($entity) != 'dataprocessorapi') {
      return array();
    }
    $params['is_active'] = 1;
    $data_processors = \CRM_Dataprocessor_BAO_DataProcessor::getValues($params);
    $actions[] = 'getfields';
    foreach($data_processors as $data_processor) {
      $actions[] = $data_processor['name'];
      $actions[] = 'getcount'.$data_processor['name'];
    }
    return $actions;
  }


}