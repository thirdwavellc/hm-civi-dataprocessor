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

CREATE TABLE IF NOT EXISTS `civicrm_data_processor_source` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_processor_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `type` VARCHAR(128) NOT NULL,
  `title` VARCHAR(128) NOT NULL,
  `configuration` TEXT NULL,
  `join_type` VARCHAR(128) NULL,
  `join_configuration` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `civicrm_data_processor_output` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_processor_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(128) NOT NULL,
  `configuration` TEXT NULL,
  `permission` VARCHAR(255) NULL,
  `api_entity` VARCHAR(255) NULL,
  `api_action` VARCHAR(255) NULL,
  `api_count_action` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;