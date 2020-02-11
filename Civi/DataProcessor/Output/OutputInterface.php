<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Output;

interface OutputInterface {

  /**
   * Returns true when this output has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration();

  /**
   * When this output type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $output
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $output=array());

  /**
   * When this output type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName();


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $output
   * @return array $output
   */
  public function processConfiguration($submittedValues, &$output);

  /**
   * This function is called prior to removing an output
   *
   * @param array $output
   * @return void
   */
  public function deleteOutput($output);

}
