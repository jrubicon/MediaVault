# MediaVault
Media vault for images and company assets

Generic media vault allows for uploading images, videos and documents.

## Installation
Upon installing on a server, visiting /setup.php sets up the database connection and creates the tables and admin access.

***Certain files need to be edited for outgoing emails, this includes:***
- keypanel.php
- requestitems.php
- forgotpassword.php

This includes editing the variables at the top of said file.
```
$setAdminEmailFrom = "noreply@example.com";
$setAdminEmailCC = "admin@example.com";
```

## Screen examples
Screen 1:
![alt-text](https://github.com/jrubix/MediaVault/blob/main/markdown/screen1.png "Screen 1")

Screen 2:
![alt-text](https://github.com/jrubix/MediaVault/blob/main/markdown/screen2.png "Screen 2")

Screen 3:
![alt-text](https://github.com/jrubix/MediaVault/blob/main/markdown/screen3.png "Screen 3")

Screen 4:
![alt-text](https://github.com/jrubix/MediaVault/blob/main/markdown/screen4.png "Screen 4")
