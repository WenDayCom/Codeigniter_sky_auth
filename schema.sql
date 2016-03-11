SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `sky_auth_users` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uuid` VARCHAR(36) NOT NULL,
	`name` VARCHAR(20) NOT NULL,
	`email` VARCHAR(60) NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	`is_active` BIT(1) NOT NULL DEFAULT b'0',
	`is_banned` BIT(1) NOT NULL DEFAULT b'0',
	`create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`update_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`remember_token` VARCHAR(255) NULL DEFAULT NULL,
	`reset_password_token` VARCHAR(255) NULL DEFAULT NULL,
	`reset_password_at` TIMESTAMP NULL DEFAULT NULL,
	`activate_token` VARCHAR(255) NULL DEFAULT NULL,
	`activate_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `uuid` (`uuid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=15
;

CREATE TABLE IF NOT EXISTS `sky_auth_groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(20) NOT NULL COLLATE 'utf8_unicode_ci',
	`descrption` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `sky_auth_users_sky_auth_groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`group_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `user_id` (`user_id`),
	INDEX `group_id` (`group_id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `sky_auth_throttles` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(60) NOT NULL COLLATE 'utf8_unicode_ci',
	`ip_address` INT(11) NOT NULL,
	`create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `create_at` (`create_at`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
