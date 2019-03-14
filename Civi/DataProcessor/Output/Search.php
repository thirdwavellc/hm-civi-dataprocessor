<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

class Search implements OutputInterface {

  /**
   * Return the url to a configuration page.
   * Or return false when no configuration page exists.
   *
   * @return string|false
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/output/search';
  }

}