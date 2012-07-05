#myTFH (My Tin Foil Hat)
A single sign on solution

#Notes
Some general installation & implementation notes

## Installation
Installation requires webserver write access to the following folders during installation.

1. config/
2. install/tmp/

```sh
%> chown -R httpd:httpd config/
%> mkdir install/tmp && chown -R httpd:httpd install/tmp
```

Once installation process occurs it is recommended to remove the following files/folders.

1. config/configuration.php.example
2. install/
3. install.php

```sh
%> rm -fr config/configuration.php.example install/ install.php
```

## SELinux
Refering applications using authentication must have their application added to the whitelist ACL and use PHP cURL functionality. If server configuration uses SELinux the following command will resolve permission denied errors.
```sh
%> setsebool -P httpd_can_network_connect 1
```
