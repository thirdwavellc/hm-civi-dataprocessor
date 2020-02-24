<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor;

use Civi\DataProcessor\DataFlow\Sort\SortCompareFactory;
use Civi\DataProcessor\Event\FilterHandlerEvent;
use Civi\DataProcessor\Event\OutputHandlerEvent;
use Civi\DataProcessor\Event\SourceOutputHandlerEvent;
use Civi\DataProcessor\ProcessorType\AbstractProcessorType;

use CRM_Dataprocessor_ExtensionUtil as E;


class Factory {

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
   * @var String[]
   */
  protected $outputHandlers = array();

  /**
   * @var String[]
   */
  protected $outputHandlerClasses = array();

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
    $this->addDataSource('campaign', 'Civi\DataProcessor\Source\Campaign\CampaignSource', E::ts('Campaign'));
    $this->addDataSource('contribution', 'Civi\DataProcessor\Source\Contribution\ContributionSource', E::ts('Contribution'));
    $this->addDataSource('contribution_recur', 'Civi\DataProcessor\Source\Contribution\ContributionRecurSource', E::ts('Recurring Contribution'));
    $this->addDataSource('case', 'Civi\DataProcessor\Source\Cases\CaseSource', E::ts('Case'));
    $this->addDataSource('relationship', 'Civi\DataProcessor\Source\Contact\RelationshipSource', E::ts('Relationship'));
    $this->addDataSource('relationship_type', 'Civi\DataProcessor\Source\Contact\RelationshipTypeSource', E::ts('Relationship Type'));
    $this->addDataSource('event', 'Civi\DataProcessor\Source\Event\EventSource', E::ts('Event'));
    $this->addDataSource('participant', 'Civi\DataProcessor\Source\Event\ParticipantSource', E::ts('Participant'));
    $this->addDataSource('line_item', 'Civi\DataProcessor\Source\Price\LineItemSource', E::ts('Line Item'));
    $this->addDataSource('mailing', 'Civi\DataProcessor\Source\Mailing\MailingSource', E::ts('Mailing'));
    $this->addDataSource('mailing_job', 'Civi\DataProcessor\Source\Mailing\MailingJobSource', E::ts('Mailing Job'));
    $this->addDataSource('mailing_group', 'Civi\DataProcessor\Source\Mailing\MailingGroupSource', E::ts('Mailing Group'));
    $this->addDataSource('membership', 'Civi\DataProcessor\Source\Member\MembershipSource', E::ts('Membership'));
    $this->addDataSource('membership_type', 'Civi\DataProcessor\Source\Member\MembershipTypeSource', E::ts('Membership Type'));
    $this->addDataSource('membership_status', 'Civi\DataProcessor\Source\Member\MembershipStatusSource', E::ts('Membership Status'));
    $this->addDataSource('csv', 'Civi\DataProcessor\Source\CSV', E::ts('CSV File'));
    $this->addDataSource('sqltable', 'Civi\DataProcessor\Source\SQLTable', E::ts('SQL Table'));
    $this->addOutput('api', 'Civi\DataProcessor\Output\Api', E::ts('API'));
    $this->addOutput('contact_summary_tab', 'CRM_Contact_DataProcessorContactSummaryTab', E::ts('Tab on contact summary'));
    $this->addOutput('dashlet', 'CRM_DataprocessorDashlet_Dashlet', E::ts('Dashlet'));
    $this->addOutput('search', 'CRM_DataprocessorSearch_Search', E::ts('Search / Report'));
    $this->addOutput('contact_search', 'CRM_Contact_DataProcessorContactSearch', E::ts('Contact Search'));
    $this->addOutput('activity_search', 'CRM_DataprocessorSearch_ActivitySearch', E::ts('Activity Search'));
    $this->addOutput('case_search', 'CRM_DataprocessorSearch_CaseSearch', E::ts('Case Search'));
    $this->addOutput('contribution_search', 'CRM_DataprocessorSearch_ContributionSearch', E::ts('Contribution Search'));
    $this->addOutput('membership_search', 'CRM_DataprocessorSearch_MembershipSearch', E::ts('Membership Search'));
    $this->addOutput('participant_search', 'CRM_DataprocessorSearch_ParticipantSearch', E::ts('Participant Search'));
    $this->addOutput('export_csv', 'CRM_DataprocessorOutputExport_CSV', E::ts('CSV Export'));
    $this->addFilter('simple_sql_filter', 'Civi\DataProcessor\FilterHandler\SimpleSqlFilter', E::ts('Field filter'));
    $this->addFilter('multiple_field_filter', 'Civi\DataProcessor\FilterHandler\MultipleFieldFilter', E::ts('Text in multiple fields Filter'));
    $this->addFilter('activity_filter', 'Civi\DataProcessor\FilterHandler\ActivityFilter', E::ts('Activity filter'));
    $this->addFilter('contact_filter', 'Civi\DataProcessor\FilterHandler\ContactFilter', E::ts('Contact filter'));
    $this->addFilter('contact_in_group_filter', 'Civi\DataProcessor\FilterHandler\ContactInGroupFilter', E::ts('Contact in Group filter'));
    $this->addFilter('contact_with_tag_filter', 'Civi\DataProcessor\FilterHandler\ContactWithTagFilter', E::ts('Contact has Tag filter'));
    $this->addFilter('contact_has_membership', 'Civi\DataProcessor\FilterHandler\ContactHasMembershipFilter', E::ts('Contact has Membership filter'));
    $this->addFilter('contact_type_filter', 'Civi\DataProcessor\FilterHandler\ContactTypeFilter', E::ts('Contact Type filter'));
    $this->addFilter('permission_to_view_contact', 'Civi\DataProcessor\FilterHandler\PermissionToViewContactFilter', E::ts('Permission to view contact'));
    $this->addFilter('case_role_filter', 'Civi\DataProcessor\FilterHandler\CaseRoleFilter', E::ts('Case Role filter'));
    $this->addFilter('in_api_filter', 'Civi\DataProcessor\FilterHandler\InApiFilter', E::ts('API filter'));
    $this->addjoinType('simple_join', 'Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleJoin', E::ts('Select fields to join on'));
    $this->addjoinType('simple_non_required_join', 'Civi\DataProcessor\DataFlow\MultipleDataFlows\SimpleNonRequiredJoin', E::ts('Select fields to join on (not required)'));
    $this->addOutputHandler('raw', 'Civi\DataProcessor\FieldOutputHandler\RawFieldOutputHandler', E::ts('Raw field value'));
    $this->addOutputHandler('number', 'Civi\DataProcessor\FieldOutputHandler\NumberFieldOutputHandler', E::ts('Formatted Number field value'));
    $this->addOutputHandler('date', 'Civi\DataProcessor\FieldOutputHandler\DateFieldOutputHandler', E::ts('Date field value'));
    $this->addOutputHandler('contact_link', 'Civi\DataProcessor\FieldOutputHandler\ContactLinkFieldOutputHandler', E::ts('Link to view contact'));
    $this->addOutputHandler('is_active', 'Civi\DataProcessor\FieldOutputHandler\IsActiveFieldOutputHandler', E::ts('Is Active (based on dates)'));
    $this->addOutputHandler('file_field', 'Civi\DataProcessor\FieldOutputHandler\FileFieldOutputHandler', E::ts('File download link'));
    $this->addOutputHandler('option_label', 'Civi\DataProcessor\FieldOutputHandler\OptionFieldOutputHandler', E::ts('Option label'));
    $this->addOutputHandler('relationships', 'Civi\DataProcessor\FieldOutputHandler\RelationshipsFieldOutputHandler', E::ts('Relationships'));
    $this->addOutputHandler('case_roles', 'Civi\DataProcessor\FieldOutputHandler\CaseRolesFieldOutputHandler', E::ts('Case Roles'));
    $this->addOutputHandler('groups_of_contact', 'Civi\DataProcessor\FieldOutputHandler\GroupsOfContactFieldOutputHandler', E::ts('Display the groups of a contact'));
    $this->addOutputHandler('event_repeating_info', 'Civi\DataProcessor\FieldOutputHandler\EventRepeatingInfoFieldOutputHandler', E::ts('Display info about repeating event'));
    $this->addOutputHandler('event_participants', 'Civi\DataProcessor\FieldOutputHandler\EventParticipantsFieldOutputHandler', E::ts('List participants'));
    $this->addOutputHandler('calculations_substract', 'Civi\DataProcessor\FieldOutputHandler\Calculations\SubtractFieldOutputHandler', E::ts('Calculations (on multiple fields): Subtract'));
    $this->addOutputHandler('calculations_total', 'Civi\DataProcessor\FieldOutputHandler\Calculations\TotalFieldOutputHandler', E::ts('Calculations (on multiple fields): Adding up'));
    $this->addOutputHandler('aggregation_function', 'Civi\DataProcessor\FieldOutputHandler\AggregateFunctionFieldOutputHandler', E::ts('Aggregation function'));
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
   */
  public function addOutputHandler($name, $class, $label) {
    $this->outputHandlerClasses[$name] = $class;
    $this->outputHandlers[$name] = $label;
  }

  /**
   * @return String[]
   */
  public function getOutputHandlers() {
    return $this->outputHandlers;
  }

  /**
   * @param $name
   *
   * @return \Civi\DataProcessor\FieldOutputHandler\AbstractFieldOutputHandler
   */
  public function getOutputHandlerByName($name) {
    if ($name && isset($this->outputHandlerClasses[$name])) {
      $class = $this->outputHandlerClasses[$name];
      return new $class();
    } else {
      return null;
    }
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
