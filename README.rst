wiki.php.net
============

Docker
------

Building::

	docker build -t php-wiki .

Running::

	docker run -ti --rm -e dokuwikitoken="$DOKUWIKITOKEN" -v "$PWD/dokuwiki":/var/www/html/ -v "$PWD/httpd.conf":/etc/apache2/sites-available/000-default.conf --name php-wiki -p 8080:80 --user 1000 --sysctl net.ipv4.ip_unprivileged_port_start=0 php-wiki

Upgrades
--------

- Make a backup: ``cd /srv && tar cvzf wiki-`date +"%Y-%m-%d"`.tar.gz web-wiki``
- Follow https://www.dokuwiki.org/install:upgrade
- Instead of unpacking on the server, unpack in your local Git directory
- Restore ``register.txt`` via ``git restore -- dokuwiki/inc/lang/en/register.txt``
- Restore ``resetpwd.txt`` via ``git restore -- dokuwiki/inc/lang/en/resetpwd.txt``
- Restore ``entities.conf`` via ``git restore -- dokuwiki/conf/entities.conf``
- Review the changes (not every detail in larger upgrades of course)
- Commit and push
- Pull on the server (``cd /srv/web-wiki && sudo -u www-data git pull``)
