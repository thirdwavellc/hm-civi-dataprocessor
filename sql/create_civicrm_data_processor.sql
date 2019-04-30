CREATE TABLE IF NOT EXISTS `civicrm_data_processor` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(128) NOT NULL,
  `configuration` TEXT NULL,
  `aggregation` TEXT NULL,
  `name` VARCHAR(128) NOT NULL,
  `title` VARCHAR(128) NULL,
  `is_active` TINYINT NULL DEFAULT 1,
  `description` TEXT NULL,
  `storage_type` VARCHAR(128) NULL,
  `storage_configuration` TEXT NULL,
  `status` TINYINT NULL DEFAULT 1,
  `source_file` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;