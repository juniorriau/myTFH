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
Use the [guide](INSTALL.md)



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
