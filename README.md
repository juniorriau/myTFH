#myTFH
My Tin Foil Hat - A unique SSO solution utilizing XMLHttpRequests, CORS
whitelisting, RSA public key encryption and more...

#Features
* RSA Public key authentication
* RSA Public keyring helping isolate user accounts
* XMLHttpRequest proxy using JSON formatted responses
* Blacklist ACL per host
* Whitelist ACL per application
* Simple implementation
* PDO stored procedures for all DB transactions

#Notes
Refering applications using authentication must have their application added to
the whitelist ACL and use PHP cURL functionality. If server configuration uses
SELinux the following command will resolve permission denied errors.
```sh
setsebool -P httpd_can_network_connect 1
```
