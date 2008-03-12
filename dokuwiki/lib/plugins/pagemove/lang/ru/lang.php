<?php
/**
 * Russian language file 
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     S'Adm*n <s-adm_n@mail.ru>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Перемещение/переименование страницы...';
$lang['desc'] = 'Page Move/Rename Plugin';

$lang['pm_notexist']   = 'Эта страница еще не существует';
$lang['pm_notstart']   = 'Недоступно для стартовой страницы';
$lang['pm_notwrite']   = 'Ваши права доступа не позволяют Вам изменять эту страницу';
$lang['pm_badns']      = 'В названии пространства имён присутствуют недопустимые символы.';
$lang['pm_badname']    = 'Недопустимые символы в названии страниц.';
$lang['pm_nochange']   = 'Параметры страницы не изменены.';
$lang['pm_existing1']  = 'Страница с именем ';
$lang['pm_existing2']  = ' уже существует в ';
$lang['pm_root']       = '[Корневой каталог]';
$lang['pm_current']    = '(Текущий)';
$lang['pm_movedfrom']  = 'Документ перемещён из ';
$lang['pm_movedto']    = 'Документ перемещён в ';
$lang['pm_norights']   = 'У Вас нет прав на редактирование одной из страниц, ссылающихся на данный документ.';
$lang['pm_tryagain']   = 'Попробуйте позже.';
$lang['pm_filelocked']  = 'Изменение данного файла запрещено - ';
$lang['pm_fileslocked'] = 'Изменение данных файлов запрещено - ';
$lang['pm_linkchange1'] = 'Ссылки на страницу ';
$lang['pm_linkchange2'] = ' изменены на ';
$lang['pm_newname']     = 'Новое название документа :';
$lang['pm_targetns']    = 'Переместить в пространство :';
$lang['pm_submit']      = 'Применить';
?>