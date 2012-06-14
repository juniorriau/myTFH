#myTFH
My Tin Foil Hat - A unique SSO solution utilizing XMLHttpRequests, CORS
whitelisting, RSA public key encryption and more...

#Features
* RSA Public key authentication
* RSA Public keyring helping isolate user accounts
* XMLHttpRequest proxy using JSON formatted responses
* Blacklist ACL per host (Refering hosts are auto added to this list upon several factors)
* Whitelist ACL per application
* Simple implementation
* PDO stored procedures for all DB transactions

#Installation
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



#Implementation
To implement this service within an application simply copy the /auth.php file
to the remote application directory. Configure the first variable to point to
your installation of the myTFH service then add the remote URL to the allowed
applications whitelist.

```php
$sso = 'https://myTFH.service/?nxs=proxy/remote';
```

#Notes
Refering applications using authentication must have their application added to
the whitelist ACL and use PHP cURL functionality. If server configuration uses
SELinux the following command will resolve permission denied errors.
```sh
setsebool -P httpd_can_network_connect 1
```
