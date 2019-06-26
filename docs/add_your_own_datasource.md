# Add your own data source for a CiviCRM Entity

In this tutorial we are going to add a Data Source for CiviCRM Entity.  
For example we will add Case Data Source.

Follow the below mentioned steps to add you own Data Source for CiviCRM Entity to Data Processor: 

- ###### First navigate to the Civi/DataProcessor and open Factory.php
 
	- Add following line referring the other lines
	`$this->addDataSource(key with which it will be stored, namespace and the php file for the data source, E::ts(this will be the value with which Data Source will be shown));`

	        for example for case data source
	           `$this->addDataSource('case', 'Civi\DataProcessor\Source\Cases\CaseSource', E::ts('Case'));`

- ###### Create a Data Source File 
	- Navigate to Civi/DataProcessor/Source
	- Create a New Folder for new data source and create a new file, or just add a new data source file in a existing folder (if related)
	- Define the namespace similar to the one used in Factory.php
	  Example: `namespace Civi\DataProcessor\Source\Cases;`
	- Referring other files like Contribution/ContributionSource.php, you have to change return value of two functions getEntity and getTable

		**_getEntity_**: Name of the CiviCRM Entity i.e. to be added as Data Source
		**_getTable_**: TableName in CiviCRM Schema from which information about the Entity is to be retrieved
	   Example: For adding Case DataSource
	            - _getEntity_ will return 'Case'
	            - _getTable_ will return 'civicrm_case'

- ###### If all done properly you can see the added Data Source in the dropdown while adding new Sources in DataProcessor

