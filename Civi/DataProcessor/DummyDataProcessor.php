<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor;

use Civi\DataProcessor\DataFlow\MultipleDataFlows\JoinSpecification;
use Civi\DataProcessor\ProcessorType\DefaultProcessorType;
use Civi\DataProcessor\Source\ContactSource;
use Civi\DataProcessor\Source\ContributionSource;
use Civi\DataProcessor\Storage\DatabaseTable;

class DummyDataProcessor {

  /**
   * @var \Civi\DataProcessor\ProcessorType\AbstractProcessorType
   */
  public $dataprocessor;

  public function __construct() {
    $this->dataprocessor = new DefaultProcessorType();
    $this->dataprocessor->addDataSource(new ContactSource('civicrm_contact_'));
    $this->dataprocessor->addDataSource(new ContributionSource('civicrm_contribution_'), new JoinSpecification('id', 'civicrm_contact_', 'contact_id', 'civicrm_contribution_'));
    $this->dataprocessor->setStorage(new DatabaseTable('aaaa'));
  }

}