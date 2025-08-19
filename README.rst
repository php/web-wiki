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

- Follow https://www.dokuwiki.org/install:upgrade
- Instead of unpacking on the server, unpack in your local Git directory
- Restore ``register.txt`` via ``git restore -- dokuwiki/inc/lang/en/register.txt``
- Restore ``resetpwd.txt`` via ``git restore -- dokuwiki/inc/lang/en/resetpwd.txt``
- Restore ``entities.conf`` via ``git restore -- dokuwiki/conf/entities.conf``
- Reapply commits (for auth, and rendering just the username):
  ``git cherry-pick 3f6e9d7e7380d1e7a31e6d1203ea5b9b9a20cdaf 3afc81ab78f4e5278bb0c6c4c0d390a1c8d43913``
- Review the changes (not every detail in larger upgrades of course)
- Commit and push
- The wiki server will pull the new GIT content through rsync automatically
  once an hour
