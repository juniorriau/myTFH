DELIMITER //

-- Retrieve current session
DROP PROCEDURE IF EXISTS Session_Search//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Session_Search(IN `sessionID` VARCHAR(128), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Retrieve current session'
BEGIN
 SELECT `session_id`,AES_DECRYPT(BINARY(UNHEX(session_data)), SHA1(sKey)) AS session_data,`session_expire`,`session_agent`,`session_ip`,`session_referer` FROM `sessions` WHERE `session_id`=sessionID;
END//

DROP PROCEDURE IF EXISTS Session_Add//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Session_Add(IN `sessionID` VARCHAR(64), `sessionData` LONGTEXT, `sessionExpire` INT(10), `sessionAgent` VARCHAR(64), `sessionIP` VARCHAR(64), `sessionReferer` VARCHAR(64), IN `sKey` LONGTEXT)
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Add or update existing session id & data'
BEGIN
 DECLARE x INT DEFAULT 0;
 SELECT COUNT(*) INTO x FROM `sessions` WHERE `session_id`=sessionID AND session_agent=sessionAgent AND session_ip=sessionIP;
 IF x > 0
 THEN
  UPDATE `sessions` SET `session_data`=HEX(AES_ENCRYPT(sessionData, SHA1(sKey))) WHERE `session_id`=sessionID LIMIT 1;
 ELSE
  INSERT INTO `sessions` (`session_id`,`session_data`,`session_expire`,`session_agent`,`session_ip`,`session_referer`) VALUES (sessionID, HEX(AES_ENCRYPT(sessionData, SHA1(sKey))), sessionExpire, sessionAgent, sessionIP, sessionReferer) ON DUPLICATE KEY UPDATE `session_id`=sessionID, `session_data`=HEX(AES_ENCRYPT(sessionData, SHA1(sKey))), `session_expire`=sessionExpire;
 END IF;
 SELECT ROW_COUNT() AS affected;
END//

DROP PROCEDURE IF EXISTS Session_Destroy//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Session_Destroy(IN `sessionID` VARCHAR(64))
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Delete users sessions id'
BEGIN
 DELETE FROM `sessions` WHERE `session_id`=sessionID LIMIT 1;
END//

DROP PROCEDURE IF EXISTS Session_Timeout//
CREATE DEFINER='[dbUser]'@'[dbHost]' PROCEDURE Session_Timeout(IN `session_expire` INT(10))
 DETERMINISTIC
 SQL SECURITY INVOKER
 COMMENT 'Expire session based on timeout option'
BEGIN
 DELETE FROM `sessions` WHERE `session_expire`>session_expire LIMIT 1;
END//

DELIMITER ;
