#!/bin/bash

certbot certonly -d wiki.php.net --webroot

systemctl restart apache2
