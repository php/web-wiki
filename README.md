# wiki.php.net

# Upgrades

 - Follow https://www.dokuwiki.org/install:upgrade
 - Instead of unpacking on the server, unpack in your local Git directory
 - Restore `register.txt` via `git restore -- dokuwiki/inc/lang/en/register.txt`
 - Review the changes (not every detail in larger upgrades of course)
 - Commit and push
 - Pull on the server (`/srv/web-wiki`)
