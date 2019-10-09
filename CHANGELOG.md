# Master version (in development)

* Respect selected permissions for outputs 
* Allow to specify "Is Empty" for various filters.
* Allow to limit ContactFilter to only show contacts from specific groups.
* Output a data processor as a dashboard.
* Output a data processor as a tab on the contact summary screen.
* Added field outputs for simple calculations (substract and total).
* Added escaped output to search screens.
* Replaced the value separator in the raw field with a comma.
* Added filter to search text in multiple fields.
* Added filter for searching contacts with a certain tag.
* Added filter for searching contacts with a certain type.
* Added filter to respect the ACL. So that a user only sees the contacts he is allowed to see.
* Removed the title attribute from the outputs as those don't make sense.
* Refactored aggregation functionality and added aggregation function field.
* Fixed issue with updating navigation after editing an output.
* Added option to expand criteria forms on search forms.

# Version 1.0.7

* Changed Event Participants Field Output Handler to return a string.
* Build a cache clear when a data processor configuration is changed.

# Version 1.0.6

* Performance improvement by caching the data processor and the api calls.

# Version 1.0.5

* Added error handling to importer
* Added sort in Manage data processor screen

# Version 1.0.4

* Fixed issue with activity search and actions after the search when the actions are run on all records.

# Version 1.0.3

* Fixed issue with date filters.

# Version 1.0.2

* Fixed bug #11 (Fatal error clone on non object)

# Version 1.0.1

Initial release.
