# Howto Develop a Csv Data Source

# Table of contents

In this tutorial we are going to develop our own data source, it is going to be a an importer for CSV files.

# Before we start

When the administrator adds this data source it should be able to define where the CSV file could be found.

# The datasource class.

Begin with a new php file in `Civi\DataProcessor\Source\CSV.php`:

```php

namespace Civi\DataProcessor\Source;

class CSV extends AbstractSource {

}

```

# Giving back a DataFlow

The data source holds the configuration of the source and gives back a `DataFlow` object. 
The extension provides the following `DataFlow` objects:

* `InMemoryDataFlow` which retreives data which are stored in memory. 
* `SQLTableDataFlow` which retrieves data from a Database Table
* `CombinedSqlDataFlow` which combines several SqlDataFlows
* `CombinedDataFlow` which combines several DataFlows

We are going to implement our own `DataFlow` class which does the actual reading of the csv file.

# Implement a Csv DataFlow Class

The data flow class has two main functions which should be implemented:

* **retrieveNextRecord** this function retrieves the next record from the dataflow.
* **Initialize**  this function initalizes the dataflow.

# Implement the configuration form

The configuration form is available under the url `civicrm/dataprocessor/form/source/csv`. 
We will first make this form and later add it to our source class, so that the data processor extension knows about it.

Add a form _CRM\Dataprocessor\Form\Source\Csv.php_:

```php
<?php

use CRM_Dataprocessor_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Dataprocessor_Form_Source_Csv extends CRM_Dataprocessor_Form_Source_BaseForm {

  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('text', 'uri', E::ts('URI'), true);
    $this->add('text', 'delimiter', E::ts('Field delimiter'), true);
    $this->add('text', 'enclosure', E::ts('Field enclosure character'), true);
    $this->add('text', 'escape', E::ts('Escape character'), true);
    $this->add('checkbox', 'first_row_as_header', E::ts('First row contains column names'));
  }

  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    foreach($this->source['configuration'] as $field => $value) {
      $defaults[$field] = $value;
    }
    if (!isset($defaults['delimiter'])) {
      $defaults['delimiter'] = ',';
    }
    if (!isset($defaults['enclosure'])) {
      $defaults['enclosure'] = '"';
    }
    if (!isset($defaults['escape'])) {
      $defaults['escape'] = '\\';
    }
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if ($this->dataProcessorId) {
      $params['data_processor_id'] = $this->dataProcessorId;
    }
    if ($this->source_id) {
      $params['id'] = $this->source_id;
    }

    $params['configuration']['uri'] = $values['uri'];
    $params['configuration']['delimiter'] = $values['delimiter'];
    $params['configuration']['enclosure'] = $values['enclosure'];
    $params['configuration']['escape'] = $values['escape'];
    $params['configuration']['first_row_as_header'] = $values['first_row_as_header'];
    CRM_Dataprocessor_BAO_Source::add($params);
    parent::postProcess();
  }

}

```

This is the configuration form presented to the administrator. The administrator can configure the source by giving a url,
the delimiter, the enclosure character, and the escape character. 

The template for the form looks like _templates\CRM\Dataprocessor\Form\Source\Csv.tpl_:

```smarty 

{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* block for rule data *}
    <h3>{ts}Data Processor Sources configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source_configuration-block">
        <div class="help-block" id="help">
            {ts}<p>On this form you can configure the CSV data source.</p>
                <p>The <strong>URI</strong> is the file location. Which is either a path on the server  or a URL from where the file could be downloaded.</p>
                <p>The <strong>Field delimiter</strong> the character which separates each field.</p>
                <p>The <strong>Field enclosure character</strong> is the a character which is wrapped around each field.</p>
                <p>The <strong>Escape character</strong> is the character which marks special characters</p>
            {/ts}
        </div>

        <div class="crm-section">
            <div class="label">{$form.uri.label}</div>
            <div class="content">{$form.uri.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">&nbsp;</div>
            <div class="content">{$form.first_row_as_header.html} &nbsp; {$form.first_row_as_header.label}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.delimiter.label}</div>
            <div class="content">{$form.delimiter.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.enclosure.label}</div>
            <div class="content">{$form.enclosure.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.escape.label}</div>
            <div class="content">{$form.escape.html}</div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}

```

## Add the configuration form the source class

Add the configuration form to the source class

In _Civi\DataProcessor\Source\Csv.php_ add the following function:

```php

class CSV extends AbstractSource {

  /**
   * Returns URL to configuration screen
   *
   * @return false|string
   */
  public function getConfigurationUrl() {
    return 'civicrm/dataprocessor/form/source/csv';
  }  

  \\...

}

```

# Implement the initialization of the Csv Data Flow object

In  the `initialize `function of the source class we initialize the `CsvDataFlow` object.


