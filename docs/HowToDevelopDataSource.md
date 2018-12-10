# Howto Develop a Custom Data Source

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

We are going to use the `InMemoryDataFlow` because we read the data from the csv file and then store it in memory.

