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
interface UIFormOutputInterface extends UIOutputInterface {

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getUrlToUi($output, $dataProcessor);

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForUiLink($output, $dataProcessor);

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string|false
   */
  public function getIconForUiLink($output, $dataProcessor);

  /**
   * Returns the callback for the UI.
   *
   * @return string
   */
  public function getCallbackForUi();

}
