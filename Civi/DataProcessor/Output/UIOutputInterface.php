<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

/**
 * This interface indicates that the output type is accessible from the user interface
 *
 * Interface UIOutputInterface
 *
 * @package Civi\DataProcessor\Output
 */
interface UIOutputInterface extends OutputInterface {

  /**
   * Checks whether the current user has access to this output
   *
   * @param array $output
   * @param array $dataProcessor
   * @return bool
   */
  public function checkUIPermission($output, $dataProcessor);

}
