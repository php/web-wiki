<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Milan Oparnica <milan.opa@gmail.com>
 * @author Марко М. Костић <marko.m.kostic@gmail.com>
 */
$lang['account_suffix']        = 'Суфикс на вашем налогу. Нпр.: <code>@moj.domen.rs</code>';
$lang['base_dn']               = 'Ваше основно име домена. Нпр.: <code>DC=moj,DC=domen,DC=org</code>';
$lang['domain_controllers']    = 'Списак доменских контролера, одвојених зарезима. Нпр.: <code>srv1.domen.org,srv2.domen.org</code>';
$lang['admin_username']        = 'Повлашћени Active Directory корисник са приступом подацима свих корисника. Изборно али је потребно за одређене радње као што је слање мејлова о претплаћивању.';
$lang['admin_password']        = 'Лозинка за корисника изнад.';
$lang['sso']                   = 'Да ли треба да се користи Single-Sign-On преко Кербероса или NTLM-а?';
$lang['sso_charset']           = 'Znakovni kod u kom će vaš webserver proslediti Kerberos ili NTLM serveru vaše ime. Ostavite prazno za UTF-8 ili latin-1. Zahteva iconv ekstenziju.';
$lang['real_primarygroup']     = 'Da li treba razrešiti pravu primarnu grupu ili pretpostaviti grupu "Domain Users" (sporije)';
$lang['use_ssl']               = 'Користити SSL везу? Ако се користи, не омогућујте TLS испод.';
$lang['use_tls']               = 'Користити TLS везу? Ако се користи, не омогућујте SSL испод.';
$lang['debug']                 = 'Приказати додатан излаз за поправљање грешака код настанка грешака?';
$lang['expirywarn']            = 'Дана унапред за које треба упозорити корисника на истицање лозинке. 0 за искључивање.';
$lang['additional']            = 'Spisak dodatni AD atributa, razdvojen zarezima, koje treba preuzeti iz korisničkih podataka. Koristi se u nekim dodacima (plugin).';
$lang['update_name']           = 'Дозволити корисницима да ажурирају њихово AD приказно име?';
$lang['update_mail']           = 'Дозволити корисницима да ажурирају њихове мејл адрсе?';
$lang['recursive_groups']      = 'Razrešenje ugnježdenih grupa do nivoa pripadajućih članova (sporije)';
