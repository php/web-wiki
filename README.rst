wiki.php.net
============

Upgrades
--------

- Make a backup: ``cd /srv && tar cvzf wiki-`date +"%Y-%m-%d"`.tar.gz``
- Follow https://www.dokuwiki.org/install:upgrade
- Instead of unpacking on the server, unpack in your local Git directory
- Restore ``register.txt`` via ``git restore -- dokuwiki/inc/lang/en/register.txt``
- Restore ``resetpwd.txt`` via ``git restore -- dokuwiki/inc/lang/en/resetpwd.txt``
- Review the changes (not every detail in larger upgrades of course)
- Commit and push
- Pull on the server (``cd /srv/web-wiki && sudo -u kelunik git pull``)
