DELIMITER //

DROP PROCEDURE IF EXISTS Users_AddUpdate//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Users_AddUpdate(IN `email` VARCHAR(128), IN `password` LONGTEXT, IN `lvl` VARCHAR(40), IN `grp` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or updates user accounts'
BEGIN
 SET foreign_key_checks = 0;
 INSERT INTO `authentication` (`resource`, `email`, `password`, `level`, `group`) VALUES (SHA1(email), HEX(AES_ENCRYPT(email, SHA1(sKey))), HEX(AES_ENCRYPT(password, SHA1(sKey))), HEX(AES_ENCRYPT(lvl, SHA1(sKey))), HEX(AES_ENCRYPT(grp, SHA1(sKey)))) ON DUPLICATE KEY UPDATE `resource`=SHA1(email), `email`=HEX(AES_ENCRYPT(email, SHA1(sKey))), `password`=HEX(AES_ENCRYPT(password, SHA1(sKey))), `level`=HEX(AES_ENCRYPT(lvl, SHA1(sKey))), `group`=HEX(AES_ENCRYPT(grp, SHA1(sKey)));
 SET foreign_key_checks = 1;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Users_verify//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Users_verify(IN `email` VARCHAR(128), IN `password` LONGTEXT, IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Verifies user account for authentication'
BEGIN
 SELECT COUNT(*) FROM `authentication` WHERE `email`=HEX(AES_ENCRYPT(email, SHA1(sKey))) AND `password`=HEX(AES_ENCRYPT(password, SHA1(sKey)));
END//

DROP PROCEDURE IF EXISTS Users_AddUpdateToken//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Users_AddUpdateToken(IN `emailAddy` VARCHAR(128), IN `token` LONGTEXT, IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Updates users authentication token'
BEGIN
 UPDATE `authentication` SET `authentication_token`=HEX(AES_ENCRYPT(token, SHA1(sKey))) WHERE AES_DECRYPT(BINARY(UNHEX(email)), SHA1(sKey))=emailAddy LIMIT 1;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Users_GetToken//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Users_GetToken(IN `emailAddy` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Decrypts and retrieves users authenticated token signature'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(authentication_token)), SHA1(sKey)) AS signature FROM `authentication` WHERE AES_DECRYPT(BINARY(UNHEX(email)), SHA1(sKey))=emailAddy;
END//

DROP PROCEDURE IF EXISTS Users_GetLevelGroup//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Users_GetLevelGroup(IN `email` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves the users access level and group membership'
BEGIN
 SELECT `level`,`group` FROM `authentication` WHERE AES_DECRYPT(BINARY(UNHEX(email)), SHA1(sKey))=email;
END//

DROP PROCEDURE IF EXISTS Groups_GetList//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Groups_GetList()
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Returns list of currently configured groups'
BEGIN
 SELECT `group` FROM `authentication_groups`;
END//

DROP PROCEDURE IF EXISTS Levels_GetList//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Levels_GetList()
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Returns list of currently configured access levels'
BEGIN
 SELECT `level` FROM `authentication_levels`;
END//

DELIMITER ;