#myTFH (My Tin Foil Hat)
A single sign on solution

#Notes
Some general installation & implementation notes

## Installation
Installation requires webserver write access to the following folders.

1. config/
2. install/tmp/

##

## SELinux
Refering applications using authentication must have their application added to the whitelist ACL and use PHP cURL functionality. If server configuration uses SELinux the following command will resolve permission denied errors.
```sh
setsebool -P httpd_can_network_connect 1
```
