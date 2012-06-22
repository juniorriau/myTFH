DELIMITER //

DROP PROCEDURE IF EXISTS Configuration_keys_add//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Configuration_keys_add(IN `countryName` VARCHAR(64), IN `stateOrProvinceName` VARCHAR(64), IN `localityName` VARCHAR(64), IN `organizationName` VARCHAR(64), IN `organizationalUnitName` VARCHAR(64), IN `commonName` VARCHAR(64), IN `emailAddy` VARCHAR(64), IN `privateKey` LONGTEXT, IN `publicKey` LONGTEXT, IN `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates users key pair'
BEGIN
 SET foreign_key_checks = 0;
 IF (SELECT COUNT(*) FROM `configuration_openssl_keys` WHERE AES_DECRYPT(BINARY(UNHEX(emailAddress)), SHA1(skey))=emailAddy > 0)
 THEN
  UPDATE `configuration_openssl_keys` SET `resource`=SHA1(emailAddy), `countryName`=HEX(AES_ENCRYPT(countryName, SHA1(skey))), `stateOrProvinceName`=HEX(AES_ENCRYPT(stateOrProvinceName, SHA1(skey))), `localityName`=HEX(AES_ENCRYPT(localityName, SHA1(skey))), `organizationName`=HEX(AES_ENCRYPT(organizationName, SHA1(skey))), `organizationalUnitName`=HEX(AES_ENCRYPT(organizationalUnitName, SHA1(skey))), `commonName`=HEX(AES_ENCRYPT(commonName, SHA1(skey))), `emailAddress`=HEX(AES_ENCRYPT(emailAddy, SHA1(skey))), `privateKey`=HEX(AES_ENCRYPT(privateKey, SHA1(skey))), `publicKey`=HEX(AES_ENCRYPT(publicKey, SHA1(skey))), `sKey`=HEX(AES_ENCRYPT(sKey, SHA1(skey))) WHERE HEX(AES_ENCRYPT(emailAddress, SHA1(skey)))=emailAddy;
 ELSE
  INSERT INTO `configuration_openssl_keys` (`resource`, `countryName`, `stateOrProvinceName`, `localityName`, `organizationName`, `organizationalUnitName`, `commonName`, `emailAddress`, `privateKey`, `publicKey`, `sKey`) VALUES (SHA1(emailAddy), HEX(AES_ENCRYPT(countryName, SHA1(skey))), HEX(AES_ENCRYPT(stateOrProvinceName, SHA1(skey))), HEX(AES_ENCRYPT(localityName, SHA1(skey))), HEX(AES_ENCRYPT(organizationName, SHA1(skey))), HEX(AES_ENCRYPT(organizationalUnitName, SHA1(skey))), HEX(AES_ENCRYPT(commonName, SHA1(skey))), HEX(AES_ENCRYPT(emailAddy, SHA1(skey))), HEX(AES_ENCRYPT(privateKey, SHA1(skey))), HEX(AES_ENCRYPT(publicKey, SHA1(skey))), HEX(AES_ENCRYPT(skey, SHA1(skey)))) ON DUPLICATE KEY UPDATE `resource`=SHA1(emailAddy), `countryName`=HEX(AES_ENCRYPT(countryName, SHA1(skey))), `stateOrProvinceName`=HEX(AES_ENCRYPT(stateOrProvinceName, SHA1(skey))), `localityName`=HEX(AES_ENCRYPT(localityName, SHA1(skey))), `organizationName`=HEX(AES_ENCRYPT(organizationName, SHA1(skey))), `organizationalUnitName`=HEX(AES_ENCRYPT(organizationalUnitName, SHA1(skey))), `commonName`=HEX(AES_ENCRYPT(commonName, SHA1(skey))), `emailAddress`=HEX(AES_ENCRYPT(emailAddy, SHA1(skey))), `privateKey`=HEX(AES_ENCRYPT(privateKey, SHA1(skey))), `publicKey`=HEX(AES_ENCRYPT(publicKey, SHA1(skey))), `sKey`=HEX(AES_ENCRYPT(skey, SHA1(skey)));
 END IF;
 SET foreign_key_checks = 1;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Configuration_keys_get//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Configuration_keys_get(IN `emailAddy` VARCHAR(64), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves OpenSSL keypair by email address'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(countryName)), SHA1(sKey)) AS countryName, AES_DECRYPT(BINARY(UNHEX(stateOrProvinceName)), SHA1(sKey)) AS stateOrProvinceName, AES_DECRYPT(BINARY(UNHEX(localityName)), SHA1(sKey)) AS localityName, AES_DECRYPT(BINARY(UNHEX(organizationName)), SHA1(sKey)) AS organizationName, AES_DECRYPT(BINARY(UNHEX(organizationalUnitName)), SHA1(sKey)) AS organizationalUnitName, AES_DECRYPT(BINARY(UNHEX(commonName)), SHA1(sKey)) AS commonName, AES_DECRYPT(BINARY(UNHEX(privateKey)), SHA1(sKey)) AS privateKey, AES_DECRYPT(BINARY(UNHEX(publicKey)), SHA1(sKey)) AS publicKey FROM `configuration_openssl_keys` WHERE AES_DECRYPT(BINARY(UNHEX(emailAddress)), SHA1(sKey))=emailAddy;
END//

DELIMITER ;
