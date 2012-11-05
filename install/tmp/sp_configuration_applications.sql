DELIMITER //

DROP PROCEDURE IF EXISTS Configuration_applications_add//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_applications_add(IN `application` VARCHAR(255), IN `url` VARCHAR(255), IN `ip` LONGTEXT, `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates existing records for allowed applications'
BEGIN
 IF EXISTS (SELECT `resource` FROM `configuration_applications` WHERE `resource`=SHA1(application))
 THEN
  UPDATE `configuration_applications` SET `url`=HEX(AES_ENCRYPT(url, SHA1(skey))), `ip`=HEX(AES_ENCRYPT(ip, SHA1(skey))) WHERE `resource`=SHA1(application) LIMIT 1;
 ELSE
  INSERT INTO `configuration_applications` (`resource`, `application`, `url`, `ip`) VALUES (SHA1(application), HEX(AES_ENCRYPT(application, SHA1(skey))), HEX(AES_ENCRYPT(url, SHA1(skey))), HEX(AES_ENCRYPT(ip, SHA1(skey)))) ON DUPLICATE KEY UPDATE `url`=HEX(AES_ENCRYPT(url, SHA1(skey))), `ip`=HEX(AES_ENCRYPT(ip, SHA1(skey)));
 END IF;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Configuration_applications_get//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_applications_get(IN `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Returns list of allowed applications'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(application)), SHA1(sKey)) AS application, AES_DECRYPT(BINARY(UNHEX(url)), SHA1(sKey)) AS url FROM `configuration_applications`;
END//

DROP PROCEDURE IF EXISTS Configuration_applications_search//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_applications_search(IN `dmn` VARCHAR(128), IN `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Searches for specified domain in list of allowed applications'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(url)), SHA1(sKey)) AS url, AES_DECRYPT(BINARY(UNHEX(ip)), SHA1(sKey)) AS ip FROM `configuration_applications` WHERE AES_DECRYPT(BINARY(UNHEX(url)), SHA1(sKey))=dmn;
END//

DELIMITER ;

