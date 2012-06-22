#uSSO
A single sign on solution for the Marriott Library at the University Of Utah.

#Features
* RSA Public key authentication
* XMLHttpRequest proxy
* Blacklist ACL per host
* Whitelist ACL per application

#Notes
Refering applications using authentication must have their application added to the whitelist ACL and use PHP cURL functionality. If server configuration uses SELinux the following command will resolve permission denied errors.
```sh
setsebool -P httpd_can_network_connect 1
```
