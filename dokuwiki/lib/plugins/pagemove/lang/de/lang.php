<?php
/**
 * german language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     picsar <>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Seite verschieben/umbenennen...';
$lang['desc'] = 'Page Move/Rename Plugin';

$lang['pm_notexist']	= 'Dieses Thema existiert noch nicht';
$lang['pm_notstart']	= 'Die Startseite kann nicht verschoben oder umbenannt werden';
$lang['pm_notwrite']	= 'Sie haben unzureichende Rechte um diese Seite zu ändern';
$lang['pm_badns']		= 'Ungültige Zeichen in der Namensraum-Bezeichnung.';
$lang['pm_badname']		= 'Ungültiges Zeichen im Seitennamen.';
$lang['pm_nochange']	= 'Name und Namensraum des Dokuments sind unverändert.';
$lang['pm_existing1']	= 'Ein Dokument mit der Bezeichnung ';
$lang['pm_existing2']	= ' existiert bereits in ';
$lang['pm_root']		= '[Wurzel des Namensraumes / Root namespace]';
$lang['pm_current']		= '(Aktueller)';
$lang['pm_movedfrom']	= 'Dokument verschoben von ';
$lang['pm_movedto']		= 'Dokument verschoben nach ';
$lang['pm_norights']	= 'Sie haben unzureichende Rechte, einen oder mehrere Rückverweise mit diesem Dokument zu verändern.';
$lang['pm_tryagain']	= 'Versuchen Sie es später nochmal.';
$lang['pm_filelocked']	= 'Diese Datei ist gesperrt - ';
$lang['pm_fileslocked']	= 'Diese Dateien sind gesperrt - ';
$lang['pm_linkchange1']	= 'Verknüpfungen mit ';
$lang['pm_linkchange2']	= ' geändert zu ';
$lang['pm_newname']		= 'Neuer Seitenname:';
$lang['pm_targetns']	= 'Wählen Sie den neuen Namensraum: ';
?>
