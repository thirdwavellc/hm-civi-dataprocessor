# Data Processor

The data processor is an extension with which system administrator can do the following:

* Create custom searches with optional the possibility to export the results
* Create an API to fetch data. This is quite useful if you have external systems which need data from CiviCRM. 
* ... and developers can enhance the outputs of the data processor so that much more is possible, even things we haven't thought of yet.

Below a screenshot of the configuration screen

![Screenshot configuration screen](docs/images/dataprocessor_1.png)

And below a screenshot of the above data processor in action as a custom search:

![Screen of data processor in action as a search](docs/images/dataprocessor_2.png) 

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Documentation

* [How to create a search with the data processor](docs/how_to_create_search.md)

## Developer documentation

* Enhancing the data processor
* [Available Hooks](docs/hooks.md)
* Add your own data source for a CiviCRM Entity
* Add your own data source for a CSV File

## Installation

Install this extension by downloading it from https://lab.civicrm.org/extensions/dataprocessor/-/archive/master/dataprocessor-master.zip
and then upload it to your civicrm server in the extension folder.
And then press install in the Administer -->  System Settings --> Extensions screen.
