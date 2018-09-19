<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Storage;

use Civi\DataProcessor\DataFlow\AbstractDataFlow;

interface StorageInterface {

  /**
   * Sets the source data flow
   *
   * @param \Civi\DataProcessor\DataFlow\AbstractDataFlow $sourceDataFlow
   * @return \Civi\DataProcessor\Storage\StorageInterface
   */
  public function setSourceDataFlow(AbstractDataFlow $sourceDataFlow);

  /**
   * @return \Civi\DataProcessor\DataFlow\AbstractDataFlow
   */
  public function getDataFlow();

  /**
   * Initialize the storage
   *
   * @return void
   */
  public function initialize();

  /**
   * Chekcs whether the storage is initialized.
   *
   * @return bool
   */
  public function isInitialized();

}