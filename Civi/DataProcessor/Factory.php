<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor;

use Civi\DataProcessor\DataFlow\Sort\SortCompareFactory;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Event\FilterHandlerEvent;
use Civi\DataProcessor\Event\OutputHandlerEvent;
use Civi\DataProcessor\FieldOutputHandler\FileFieldOutputHandler;
use Civi\DataProcessor\FieldOutputHandler\OptionFieldOutputHandler;
use Civi\DataProcessor\FieldOutputHandler\RawFieldOutputHandler;
use Civi\DataProcessor\FilterHandler\SimpleSqlFilter;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;
use Civi\DataProcessor\Source\SourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use CRM_Dataprocessor_ExtensionUtil as E;


class Factory {

  /**
   * @var EventDispatcher
   */
  protected $dispatcher;

  /**
   * @var array<String>
   */
  protected $types = array();


  /**
   * @var array<String>
   */
  protected $typesClasses = array();

  /**
   * @var array<String>
   */
  protected $sources = array();


  /**
   * @var array<String>
   */
  protected $sourceClasses = array();

  /**
   * @var array<String>
   */
  protected $outputs = array();


  /**
   * @var array<String>
   */
  protected $outputClasses = array();

  /**
   * @var array<String>
   */
  protected $filters = array();


  /**
   * @var array<String>
   */
  protected $filterClasses = array();

  /**
   * @var array<String>
   */
  protected $joins = array();


  /**
   * @var array<String>
   */
  protected $joinClasses = array();

  protected $fieldOutputHandlers = array();

  /**
   * @var SortCompareFactory
   */
  protected $sortCompareFactory;


  public function __construct() {
    $this->dispatcher = \Civi::dispatcher();

    $this->addDataProcessorType('default', 'Civi\DataProcessor\ProcessorType\DefaultProcessorType', E::ts('Default'));
    $this->addDataSource('activity', 'Civi\DataProcessor\Source\Activity\ActivitySource', E::ts('Activity'));
    $this->addDataSource('contact', 'Civi\DataProcessor\Source\Contact\ContactSource', E::ts('Contact'));
    $this->addDataSource('individual', 'Civi\DataProcessor\Source\Contact\IndividualSource', E::ts('Individual'));
    $this->addDataSource('household', 'Civi\DataProcessor\Source\Contact\HouseholdSource', E::ts('Household'));
    $this->addDataSource('organization', 'Civi\DataProcessor\Source\Contact\OrganizationSource', E::ts('Organization'));
    $this->addDataSource('group', 'Civi\DataProcessor\Source\Group\GroupSource', E::ts('Group'));
    $this->addDataSource('group_contact', 'Civi\DataProcessor\Source\Group\GroupContactSource', E::ts('Contacts in a group'));
    $this->addDataSource('email', 'Civi\DataProcessor\Source\Contact\EmailSource', E::ts('E-mail'));
    $this->addDataSource('address', 'Civi\DataProcessor\Source\Contact\AddressSource', E::ts('Address'));
    $this->addDataSource('phone', 'Civi\DataProcessor\Source\Contact\PhoneSource', E::ts('Phone'));
    $this->addDataSource('website', 'Civi\DataProcessor\Source\Contact\WebsiteSource', E::ts('Website'));
    $this->addDataSource('contribution', 'Civi\DataProcessor\Source\Contribution\ContributionSource', E::ts('Contribution'));
    $this->addDataSource('case', 'Civi\DataProcessor\Source\Cases\CaseSource', E::ts('Case'));
    $this->addDataSource('relationship', 'Civi\DataProcessor\Source\Contact\RelationshipSource', E::ts('Relationship'));
    $this->addDataSource('relationship_type', 'Civi\DataProcessor\Source\Contact\RelationshipTypeSource', E::ts('Relationship Type'));
    $this->addDataSource('event', 'Civi\DataProcessor\Source\Event\EventSource', E::ts('Event'));
    $this->addDataSource('participant', 'Civi\DataProcessor\Source\Event\ParticipantSource', E::ts('Participant'));
    $this->addDataSource('mailing', 'Civi\DataProcessor\Source\Mailing\MailingSource', E::ts('Mailing'));
    $this->addDataSource('mailing_job', 'Civi\DataProcessor\Source\Mailing\MailingJobSource', E::ts('Mailing Job'));
    $this->addDataSource('mailing_group', 'Civi\DataProcessor\Source\Mailing\MailingGroupSource', E::ts('Mailing Group'));
    $this->addDataSource('membership', 'Civi\DataProcessor\Source\Member\MembershipSource', E::ts('Membership'));
    $this->addDataSource('membership_type', 'Civi\DataProcessor\Source\Member\MembershipTypeSource', E::ts('Membership Type'));
    $this->addDataSource('membership_status', 'Civi\DataProcessor\Source\Member\MembershipStatusSource', E::ts('Membership Status'));
    $this->addDataSource('csv', 'Civi\DataProcessor\Source\CSV', E::ts('CSV File'));
    $this->addOutput('api', 'Civi\DataProcessor\Output\Api', E::ts('API'));
    $this->addOutput('contact_search', 'CRM_DataprocessorSearch_ContactSearch', E::ts('Contact Search'));
    $this->addOutput('activity_search', 'CRM_DataprocessorSearch_ActivitySearch', E::ts('Activity Search'));
    $this->addOutput('export_csv', 'CRM_DataprocessorOutputExport_CSV', E::ts('CSV Export'));
    $this->addFilter('simple_sql_filter', 'Civi\DataProcessor\FilterHandler\SimpleSqlFilter', E::ts('Field filter'));
    $this->addjoinType('simple_join', 'Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin', E::ts('Select fields to join on'));
    $this->addjoinType('simple_non_required_join', 'Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleNonRequiredJoin', E::ts('Select fields to join on (not required)'));
  }

  /**
   * @return array<String>
   */
  public function getDataProcessorTypes() {
    return $this->types;
  }

  /**
   * @param $name
   *
   * @return AbstractProcessorType
   */
  public function getDataProcessorTypeByName($name) {
    return new $this->typesClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getDataSources() {
    return $this->sources;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\Source\SourceInterface
   */
  public function getDataSourceByName($name) {
    return new $this->sourceClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getOutputs() {
    return $this->outputs;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\Output\OutputInterface
   */
  public function getOutputByName($name) {
    return new $this->outputClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getFilters() {
    return $this->filters;
  }

  /**
   * @param $name
   *
   * @param string $name
   * @return \Civi\DataProcessor\FilterHandler\AbstractFilterHandler
   */
  public function getFilterByName($name) {
    if (!isset($this->filterClasses[$name])) {
      return null;
    }
    return new $this->filterClasses[$name]();
  }

  /**
   * @return array<String>
   */
  public function getJoins() {
    return $this->joins;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinInterface
   */
  public function getJoinByName($name) {
    return new $this->joinClasses[$name]();
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addjoinType($name, $class, $label) {
    $this->joinClasses[$name] = $class;
    $this->joins[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addDataProcessorType($name, $class, $label) {
    $this->typesClasses[$name] = $class;
    $this->types[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addDataSource($name, $class, $label) {
    $this->sourceClasses[$name] = $class;
    $this->sources[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addFilter($name, $class, $label) {
    $this->filterClasses[$name] = $class;
    $this->filters[$name] = $label;
    return $this;
  }

  /**
   * @param $name
   * @param $class
   * @param $label
   * @return Factory
   */
  public function addOutput($name, $class, $label) {
    $this->outputClasses[$name] = $class;
    $this->outputs[$name] = $label;
    return $this;
  }

  public function getOutputHandlers(FieldSpecification $field, SourceInterface $source) {
    $event = new OutputHandlerEvent($field, $source);
    $rawOutputhandler = new RawFieldOutputHandler($field, $source);
    $event->handlers[$rawOutputhandler->getName()] = $rawOutputhandler;
    if ($field->getOptions()) {
      $optionOutputHandler = new OptionFieldOutputHandler($field, $source);
      $event->handlers[$optionOutputHandler->getName()] = $optionOutputHandler;
    }
    if ($field->type == 'File') {
      $fileOutputHandler = new FileFieldOutputHandler($field, $source);
      $event->handlers[$fileOutputHandler->getName()] = $fileOutputHandler;
    }
    $this->dispatcher->dispatch(OutputHandlerEvent::NAME, $event);
    return $event->handlers;
  }

  /**
   * Add an event subscriber class
   *
   * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
   */
  public function addSubscriber(EventSubscriberInterface $subscriber) {
    $this->dispatcher->addSubscriber($subscriber);
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\Sort\SortCompareFactory
   */
  public function getSortCompareFactory() {
    if (!$this->sortCompareFactory) {
      $this->sortCompareFactory = new SortCompareFactory();
    }
    return $this->sortCompareFactory;
  }

}