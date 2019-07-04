<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_DataprocessorSearch_StateMachine_Search extends CRM_Core_StateMachine {

  /**
   * The task that the wizard is currently processing.
   *
   * @var string
   */
  protected $_task;

  /**
   * Class constructor.
   *
   * @param object $controller
   * @param \const|int $action
   */
  public function __construct($controller, $action = CRM_Core_Action::NONE) {
    parent::__construct($controller, $action);

    $this->_pages = array();
    $this->_pages['Search'] = array(
      'className' => 'CRM_DataprocessorSearch_Form_Search',
    );
    list($task, $result) = $this->taskName($controller, 'Search');
    $this->_task = $task;

    if (is_array($task)) {
      foreach ($task as $t) {
        $this->_pages[$t] = NULL;
      }
    }
    elseif ($task) {
      $this->_pages[$task] = NULL;
    }

    $this->addSequentialPages($this->_pages, $action);
  }

  /**
   * Determine the form name based on the action. This allows us
   * to avoid using  conditional state machine, much more efficient
   * and simpler
   *
   * @param CRM_Core_Controller $controller
   *   The controller object.
   *
   * @param string $formName
   *
   * @return array
   *   the name of the form that will handle the task
   */
  public function taskName($controller, $formName = 'Search') {
    // total hack, check POST vars and then session to determine stuff
    $value = CRM_Utils_Array::value('task', $_POST);
    if (!isset($value)) {
      $value = $this->_controller->get('task');
    }
    $this->_controller->set('task', $value);
    if ($value) {
      return CRM_DataprocessorSearch_Task::getTask($value);
    }
    return array(null, null);
  }

  /**
   * Return the form name of the task.
   *
   * @return string
   */
  public function getTaskFormName() {
    return CRM_Utils_String::getClassName($this->_task);
  }

  /**
   * Should the controller reset the session.
   * In some cases, specifically search we want to remember
   * state across various actions and want to go back to the
   * beginning from the final state, but retain the same session
   * values
   *
   * @return bool
   */
  public function shouldReset() {
    return FALSE;
  }

}
