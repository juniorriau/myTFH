#Installation guide
Currently the installation process is very manual and requires several components.
First the database structure must exist, in the install/schema folder you can
modify the database-schema.sql file then import it to your existing MySQL installation.

#Database creation
Here are the fields that must be modified. [dbName] & [dbUser] instances must be
changed to reflect your installation environment. The first few lines...
```sql
DROP DATABASE IF EXISTS `[dbName]`;
CREATE DATABASE `[dbName]`;

-- Create a default user and assign limited permissions
CREATE USER "[dbUser]"@"[dbHost]" IDENTIFIED BY "[dbPass]";
GRANT SELECT, INSERT, UPDATE, DELETE, REFERENCES, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE ON `[dbName]`.* TO "[dbUser]"@"[dbHost]";
FLUSH PRIVILEGES;

-- Switch to newly created db context
USE `[dbName]`;
```

Once modifications have been made simply import the new database structure like
so. This does require root access to the MySQL installation.
```sql
mysql -u root -p < install/schema/database-schema.sql
```

#Stored procedure creation
Next you will need to import the various stored procedures used to perform the
various database transactions.

Because I have not written an installer script yet you will need to perform a
global replace on the stored procedures definer context.

First replace the username attribute within the stored procedures where 'username'
is the user you assigned during the database creation process above.
```sql
sed -i 's/[dbUser]/username/g' install/stored-procedures/sp_*.sql
```

Next replace the dbName with the name of the database you assigned to the newly
setup database replacing 'database' with the name you selected.
```sql
sed -i 's/[dbName]/database/g' install/stored-procedures/sql_*.sql
```

And last but not least the dbHost attribute needs to be changed as well.
```sql
sed -i 's/[dbHost]/hostname/g' install/stored-procedures/sql_*.sql
```

#Configuration
Next you will need to configure the application defaults. Once I have written
an installer script all of this will be performed automagically but until then
manual configuration is necessary.