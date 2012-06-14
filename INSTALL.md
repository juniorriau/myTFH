#Installation guide
Currently the installation process is very manual and requires several components.
First the database structure must exist, in the install/schema folder you can
modify the database-schema.sql file then import it to your existing MySQL installation.

Here are the fields that must be modified. [dbName] & [dbUser] instances must be
changed to reflect your installation environment. The first few lines...
```sql
DROP DATABASE IF EXISTS `[dbName]`;
CREATE DATABASE `[dbName]`;

-- Create a default user and assign limited permissions
CREATE USER "[dbUser]"@"localhost" IDENTIFIED BY "[dbPass]";
GRANT SELECT, INSERT, UPDATE, DELETE, REFERENCES, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE ON `[dbName]`.* TO "[dbUser]"@"localhost";
FLUSH PRIVILEGES;

-- Switch to newly created db context
USE `[dbName]`;
```

Once modifications have been made simply import the new database structure like
so. This does require root access to the MySQL installation.
```sql
mysql -u root -p < install/schema/database-schema.sql
```