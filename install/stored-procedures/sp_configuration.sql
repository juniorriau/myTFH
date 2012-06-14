DELIMITER //

DROP PROCEDURE IF EXISTS Configuration_def_add//
CREATE DEFINER='licensing'@'localhost' PROCEDURE Configuration_def_add(IN `title` VARCHAR(128), IN `templates` VARCHAR(255), IN `cache` VARCHAR(255), IN `flogin` INT(10), IN `private` INT(1), IN `email` VARCHAR(64), IN `timeout` INT(10), IN `pvkey` LONGTEXT, IN `pkey` LONGTEXT, IN `skey` LONGTEXT, IN `countryName` VARCHAR(64), IN `stateOrProvinceName` VARCHAR(64), IN `localityName` VARCHAR(64), IN `organizationName` VARCHAR(64), IN `organizationalUnitName` VARCHAR(64), `commonName` VARCHAR(64))
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates configuration'
BEGIN
 INSERT INTO `configuration` (`title`, `templates`, `cache`, `flogin`, `private`, `email`, `timeout`, `privateKey`, `publicKey`, `sKey`, `countryName`, `stateOrProvinceName`, `localityName`, `organizationName`, `organizationalUnitName`, `commonName`) VALUES (title, templates, cache, flogin, private, email, timeout, HEX(AES_ENCRYPT(privateKey, SHA1(skey))), HEX(AES_ENCRYPT(publicKey, SHA1(skey))), HEX(AES_ENCRYPT(sKey, SHA1(skey))), countryName, stateOrProvinceName, localityName, organizationName, organizationalUnitName, commonName) ON DUPLICATE KEY UPDATE `title`=title, `templates`=templates, `cache`=cache, `flogin`=flogin, `private`=private, `email`=email, `timeout`=timeout, `privateKey`=HEX(AES_ENCRYPT(pvkey, SHA1(skey))), `publicKey`=HEX(AES_ENCRYPT(pkey, SHA1(skey))), `sKey`=HEX(AES_ENCRYPT(skey, SHA1(skey))), `countryName`=countryName, `stateOrProvinceName`=stateOrProvinceName, `localityName`=localityName, `organizationName`=organizationName, `organizationalUnitName`=organizationalUnitName, `commonName`=commonName;
END//

DROP PROCEDURE IF EXISTS Configuration_def_get//
CREATE DEFINER='licensing'@'localhost' PROCEDURE Configuration_def_get(IN `challenge` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves configuration'
BEGIN
 SELECT `title`, `templates`, `cache`, `flogin`, `private`, `email` AS `emailAddress`, `timeout`, AES_DECRYPT(BINARY(UNHEX(privateKey)), SHA1(challenge)) AS privateKey, AES_DECRYPT(BINARY(UNHEX(publicKey)), SHA1(challenge)) AS publicKey, AES_DECRYPT(BINARY(UNHEX(sKey)), SHA1(challenge)) AS pword, `countryName`, `stateOrProvinceName`, `localityName`, `organizationName`, `organizationalUnitName`, `commonName` FROM `configuration`;
END//

DROP PROCEDURE IF EXISTS Configuration_def_keys//
CREATE DEFINER='licensing'@'localhost' PROCEDURE Configuration_def_keys(IN `challenge` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves currently configured keyring data'
BEGIN
 SELECT `email` AS `emailAddress`, AES_DECRYPT(BINARY(UNHEX(privateKey)), SHA1(challenge)) AS privateKey, AES_DECRYPT(BINARY(UNHEX(publicKey)), SHA1(challenge)) AS publicKey, AES_DECRYPT(BINARY(UNHEX(sKey)), SHA1(challenge)) AS pword FROM `configuration`;
END//

DROP PROCEDURE IF EXISTS Configuration_def_get_dn//
CREATE DEFINER='licensing'@'localhost' PROCEDURE Configuration_def_get_dn()
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves OpenSSL DN configuration'
BEGIN
 SELECT `countryName`,`stateOrProvinceName`,`localityName`,`organizationName`,`organizationalUnitName`,`commonName`,`email` AS emailAddress FROM `configuration`;
END//

DELIMITER ;
