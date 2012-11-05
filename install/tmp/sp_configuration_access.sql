DELIMITER //

DROP PROCEDURE IF EXISTS Configuration_access_add//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_access_add(IN `name` VARCHAR(32), IN `filter` VARCHAR(255), `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates denied access control lists'
BEGIN
 IF EXISTS (SELECT `name` FROM `configuration_access` WHERE `name`=HEX(AES_ENCRYPT(name, SHA1(skey))))
 THEN
  UPDATE `configuration_access` SET `name`=HEX(AES_ENCRYPT(name, SHA1(skey))), `filter`=HEX(AES_ENCRYPT(filter, SHA1(skey))) WHERE `name`=HEX(AES_ENCRYPT(name, SHA1(skey))) LIMIT 1;
 ELSE
  INSERT INTO `configuration_access` (`name`, `filter`) VALUES (HEX(AES_ENCRYPT(name, SHA1(skey))), HEX(AES_ENCRYPT(filter, SHA1(skey)))) ON DUPLICATE KEY UPDATE `name`=HEX(AES_ENCRYPT(name, SHA1(skey))), `filter`=HEX(AES_ENCRYPT(filter, SHA1(skey)));
 END IF;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Configuration_access_del//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_access_del(IN `name` VARCHAR(32))
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Deletes access control list entry'
BEGIN
 DELETE FROM `configuration_access` WHERE `name`=HEX(AES_ENCRYPT(name, SHA1(skey))) LIMIT 1;
END//

DROP PROCEDURE IF EXISTS Configuration_access_list//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_access_list(IN `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Returns list of access controls'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(name)), SHA1(sKey)) AS name FROM `configuration_access`;
END//

DROP PROCEDURE IF EXISTS Configuration_access_get//
CREATE DEFINER='myTFHAdmin'@'localhost' PROCEDURE Configuration_access_get(IN `skey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Returns list of access controls'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(filter)), SHA1(sKey)) AS deny FROM `configuration_access`;
END//

DELIMITER ;

