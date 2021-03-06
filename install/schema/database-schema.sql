-- Create the database & drop if it already exists
DROP DATABASE IF EXISTS `[dbName]`;
CREATE DATABASE `[dbName]`;

-- Grant priviledge for account then drop it
GRANT USAGE ON *.* TO "[dbUser]"@"[dbHost]";
DROP USER "[dbUser]"@"[dbHost]";
FLUSH PRIVILEGES;

-- Create a default user and assign limited permissions
CREATE USER "[dbUser]"@"[dbHost]" IDENTIFIED BY "[dbPassword]";
GRANT SELECT, INSERT, UPDATE, DELETE, REFERENCES, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE ON `[dbName]`.* TO "[dbUser]"@"[dbHost]";
FLUSH PRIVILEGES;

-- Switch to newly created db context
USE `[dbName]`;

-- Set FK checks to 0 during table creation
SET foreign_key_checks = 0;

-- Creates a table for authentication groups
--  Primary key: id
--  Unique key: resource
--  Index: resource
DROP TABLE IF EXISTS `authentication_groups`;
CREATE TABLE IF NOT EXISTS `authentication_groups` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `manager` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `owner` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group` (`group`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a default 'administrative' group
INSERT INTO `authentication_groups` (`group`, `owner`) VALUES ("admin", "admin");

-- Creates a table for authentication access levels
--  Primary key: id
--  Unique key: level
DROP TABLE IF EXISTS `authentication_levels`;
CREATE TABLE IF NOT EXISTS `authentication_levels` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `level` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `level` (`level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a set of default access levels
INSERT INTO `authentication_levels` (`level`) VALUES ("admin");
INSERT INTO `authentication_levels` (`level`) VALUES ("user");
INSERT INTO `authentication_levels` (`level`) VALUES ("view");

-- Creates the authentication users table
--  Primary key: id
--  Unique key: resource, username
--  Index: group, level, username
--  Foreign key details:
--   authentication.group updates from authentication_groups.group
--   authentication.level updates from authentication_levels.level
DROP TABLE IF EXISTS `authentication`;
CREATE TABLE IF NOT EXISTS `authentication` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` longtext NOT NULL,
  `level` varchar(40) NOT NULL,
  `group` varchar(255) NOT NULL,
  `authentication_token` longtext NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`email`),
  UNIQUE KEY `resource` (`resource`),
  INDEX (`group`),
  FOREIGN KEY (`group`)
   REFERENCES `authentication_groups` (`group`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (`level`),
  FOREIGN KEY (`level`)
   REFERENCES `authentication_levels` (`level`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table for default application settings
--  Primary key: id
--  Unique key: email
DROP TABLE IF EXISTS `configuration`;
CREATE TABLE IF NOT EXISTS `configuration` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `version` varchar(15) NOT NULL,
  `title` varchar(128) NOT NULL,
  `templates` varchar(255) NOT NULL,
  `cache` varchar(255) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `email` varchar(45) NOT NULL,
  `timeout` int(10) NOT NULL,
  `flogin` int(10) NOT NULL,
  `privateKey` longtext NOT NULL,
  `publicKey` longtext NOT NULL,
  `sKey` longtext NOT NULL,
  `countryName` varchar(64) NOT NULL,
  `stateOrProvinceName` varchar(64) NOT NULL,
  `localityName` varchar(64) NOT NULL,
  `organizationName` varchar(64) NOT NULL,
  `organizationalUnitName` varchar(64) NOT NULL,
  `commonName` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

INSERT INTO `configuration` (`version`) VALUES ("0.1");

-- Create a table for client acess control list
--  Primary key: id
DROP TABLE IF EXISTS `configuration_access`;
CREATE TABLE IF NOT EXISTS `configuration_access` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(32) DEFAULT NULL,
  `filter` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `filter` (`filter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table for default application access list
--  Primary key: id
DROP TABLE IF EXISTS `configuration_applications`;
CREATE TABLE IF NOT EXISTS `configuration_applications` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `application` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `ip` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource` (`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table for default OpenSSL extension options
--  Primary key: id
DROP TABLE IF EXISTS `configuration_openssl_cnf`;
CREATE TABLE IF NOT EXISTS `configuration_openssl_cnf` (
  `id` INT(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `config` VARCHAR(64) NOT NULL,
  `encrypt_key` BOOLEAN NOT NULL,
  `private_key_type` VARCHAR(64) NOT NULL,
  `digest_algorithm` VARCHAR(64) NOT NULL,
  `private_key_bits` INT(4) NOT NULL,
  `x509_extensions` VARCHAR(32) NOT NULL,
  `encrypt_key_cipher` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

INSERT INTO `configuration_openssl_cnf` (`id`, `config`, `encrypt_key`, `private_key_type`, `digest_algorithm`, `private_key_bits`, `x509_extensions`, `encrypt_key_cipher`) VALUES (1, 'config/openssl.cnf', 1, 'OPENSSL_KEYTYPE_RSA', 'sha1', 2048, 'usr_cert', 'OPENSSL_CIPHER_AES_256_CBC');

-- Create a table for configuration of OpenSSL keys
--  Primary key: id
--  Indexed key: emailAddress
--  Foreign key: authentication.email
DROP TABLE IF EXISTS `configuration_openssl_keys`;
CREATE TABLE IF NOT EXISTS `configuration_openssl_keys` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `countryName` longtext NOT NULL,
  `stateOrProvinceName` longtext NOT NULL,
  `localityName` longtext NOT NULL,
  `organizationName` longtext NOT NULL,
  `organizationalUnitName` longtext NOT NULL,
  `commonName` longtext NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `privateKey` longtext NOT NULL,
  `publicKey` longtext NOT NULL,
  `sKey` longtext NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`emailAddress`),
  FOREIGN KEY (`emailAddress`)
   REFERENCES `authentication` (`email`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table for the software licenses
--  Primary key: id
--  Indexed key: name
DROP TABLE IF EXISTS `license`;
CREATE TABLE IF NOT EXISTS `license` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `expiration` varchar(20) NOT NULL,
  `amount` int(4) NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `purchased` varchar(20) NOT NULL,
  `type` char(5) NOT NULL,
  `maintenance` int(1) NOT NULL,
  `notes` longtext NOT NULL,
  `serial` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table to use for access logging
--  Primary key: id
--  Indexed key: guid
DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `guid` varchar(64) NOT NULL,
  `adate` varchar(64) NOT NULL,
  `ip` varchar(10) NOT NULL,
  `hostname` varchar(80) NOT NULL,
  `agent` varchar(128) NOT NULL,
  `query` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guid` (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Create a table for per user session data
--  Primary key: id
--  Unique key: session_id
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) NOT NULL,
  `session_data` longtext NOT NULL,
  `session_expire` int(10) NOT NULL,
  `session_agent` longtext NOT NULL,
  `session_ip` longtext NOT NULL,
  `session_referer` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

DROP TABLE IF EXISTS `resources`;
CREATE TABLE IF NOT EXISTS `resources` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `common_name` varchar(128) NOT NULL,
  `owner` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource` (`resource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

DROP TABLE IF EXISTS `resources_groups`;
CREATE TABLE IF NOT EXISTS `resources_groups` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `ggroup` varchar(128) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `write` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`resource`),
  FOREIGN KEY (`resource`)
   REFERENCES `resources` (`resource`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

DROP TABLE IF EXISTS `resources_users`;
CREATE TABLE IF NOT EXISTS `resources_users` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `resource` varchar(128) NOT NULL,
  `uuser` varchar(128) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `write` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`resource`),
  FOREIGN KEY (`resource`)
   REFERENCES `resources` (`resource`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=0;

-- Re-enable the foreign key checks
SET foreign_key_checks = 1;
