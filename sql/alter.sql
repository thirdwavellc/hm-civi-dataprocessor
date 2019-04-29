ALTER TABLE `civicrm_data_processor_field`
CHANGE `name` `name` VARCHAR(255) NOT NULL,
CHANGE `type` `type` VARCHAR(255) NOT NULL,
CHANGE `title` `title` VARCHAR(255) NOT NULL;

ALTER TABLE `civicrm_data_processor_filter`
CHANGE `name` `name` VARCHAR(255) NOT NULL,
CHANGE `type` `type` VARCHAR(255) NOT NULL,
CHANGE `title` `title` VARCHAR(255) NOT NULL,
ADD `weight` int NULL;

UPDATE `civicrm_data_processor_filter` SET `weight` = `id`;