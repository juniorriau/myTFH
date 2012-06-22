DELIMITER //

DROP PROCEDURE IF EXISTS Auth_CheckUser//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Auth_CheckUser(IN `emailAddy` VARCHAR(128), IN `pword` LONGTEXT, IN `challenge` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Performs authentication check'
BEGIN
 SELECT COUNT(*) AS x FROM `authentication` WHERE AES_DECRYPT(BINARY(UNHEX(email)), SHA1(challenge))=emailAddy AND AES_DECRYPT(BINARY(UNHEX(password)), SHA1(challenge))=pword;
END//

DROP PROCEDURE IF EXISTS Auth_GetLevelGroup//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Auth_GetLevelGroup(IN `emailAddy` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieves the users access level and group membership'
BEGIN
 SELECT AES_DECRYPT(BINARY(UNHEX(level)), SHA1(sKey)) AS level, AES_DECRYPT(BINARY(UNHEX(authentication.group)), SHA1(sKey)) AS grp FROM `authentication` WHERE AES_DECRYPT(BINARY(UNHEX(email)), SHA1(sKey))=emailAddy;
END//

DELIMITER ;