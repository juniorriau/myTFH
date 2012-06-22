DELIMITER //

DROP PROCEDURE IF EXISTS Perms_AddUpdate//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_AddUpdate(IN `name` VARCHAR(128), IN `owner` VARCHAR(128), IN `grp` VARCHAR(128), IN `gr` INT(1), IN `gw` INT(1), IN `usr` VARCHAR(128), IN `ur` INT(1), IN `uw` INT(1), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates resource object permissions'
BEGIN
 DECLARE x INT DEFAULT 0;
 DECLARE y INT DEFAULT 0;

 INSERT INTO `resources` (`resource`, `common_name`, `owner`) VALUES (SHA1(name), HEX(AES_ENCRYPT(name, SHA1(sKey))), HEX(AES_ENCRYPT(owner, SHA1(sKey)))) ON DUPLICATE KEY UPDATE `resource`=SHA1(name), `common_name`=HEX(AES_ENCRYPT(name, SHA1(sKey))), `owner`=HEX(AES_ENCRYPT(owner, SHA1(sKey)));

 SELECT COUNT(*) INTO x FROM `resources_groups` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(ggroup)), SHA1(sKey))=grp;
 IF x > 0
 THEN
  UPDATE `resources_groups` SET `read`=gr, `write`=gw WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(ggroup)), SHA1(sKey))=grp LIMIT 1;
 ELSE
  INSERT INTO `resources_groups` (`resource`, `ggroup`, `read`, `write`) VALUES (SHA1(name), HEX(AES_ENCRYPT(grp, SHA1(sKey))), gr, gw);
 END IF;

 SELECT COUNT(*) INTO y FROM `resources_users` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(uuser)), SHA1(sKey))=usr;
 IF y > 0
 THEN
  UPDATE `resources_users` SET `read`=ur, `write`=uw WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(uuser)), SHA1(sKey))=usr LIMIT 1;
 ELSE
  INSERT INTO `resources_users` (`resource`, `uuser`, `read`, `write`) VALUES (SHA1(name), HEX(AES_ENCRYPT(usr, SHA1(sKey))), ur, uw);
 END IF;

 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Perms_DelAll//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_DelAll(IN `name` VARCHAR(128))
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Deletes ALL resource object permissions'
BEGIN
 DELETE FROM `resources` WHERE `resource`=SHA1(name);
END//

DROP PROCEDURE IF EXISTS Perms_DelGroup//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_DelGroup(IN `name` VARCHAR(128), IN `grp` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Deletes resource object permissions for specified group'
BEGIN
 DELETE FROM `resources_groups` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(ggroup)), SHA1(sKey))=grp LIMIT 1;
END//

DROP PROCEDURE IF EXISTS Perms_DelUser//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_DelUser(IN `name` VARCHAR(128), IN `usr` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Deletes resource object permissions for specified user'
BEGIN
 DELETE FROM `resources_users` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(uuser)), SHA1(sKey))=usr LIMIT 1;
END//

DROP PROCEDURE IF EXISTS Perms_SearchUser//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_SearchUser(IN `name` VARCHAR(128), IN `usr` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Finds read/write permissions per object per user'
BEGIN
 SELECT `read`,`write` FROM `resources_users` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(uuser)), SHA1(sKey))=usr;
END//

DROP PROCEDURE IF EXISTS Perms_SearchGroup//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Perms_SearchGroup(IN `name` VARCHAR(128), IN `grp` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Finds read/write permissions per object per group'
BEGIN
 SELECT `read`,`write` FROM `resources_groups` WHERE `resource`=SHA1(name) AND AES_DECRYPT(BINARY(UNHEX(ggroup)), SHA1(sKey))=grp;
END//

DELIMITER ;
