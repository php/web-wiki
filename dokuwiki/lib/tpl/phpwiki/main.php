<?php
/**
 * DokuWiki Default Template
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    PHP: <?php tpl_pagetitle()?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

 <style type="text/css" media="all">
  @import url("https://static.php.net/www.php.net/styles/site.css");
  @import url("https://static.php.net/www.php.net/styles/phpnet.css");
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("https://static.php.net/www.php.net/styles/print.css");
  </style>
 <!--[if IE]><![endif]><![endif]-->
  <style type="text/css">
  div.dokuwiki input.button_disabled {
    color: #999;
  }
  </style>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="https://static.php.net/www.php.net/favicon.ico" />
 <link rel="search" type="application/opensearchdescription+xml" href="http://www.php.net/phpnetimprovedsearch.src" title="Add PHP.net search" />
 <script type="text/javascript" src="https://static.php.net/www.php.net/userprefs.js"></script>
 <script type="text/javascript">
function installSummaryEnforcement()
{
    var summary_input = document.getElementById('edit__summary');
    if(summary_input !== null)
    {
        var minoredit_input = document.getElementById('minoredit');

        addEvent(summary_input, 'change', enforceSummary);
        addEvent(summary_input, 'keyup', enforceSummary);
        addEvent(minoredit_input, 'change', enforceSummary);
        addEvent(minoredit_input, 'click', enforceSummary);
        enforceSummary(); // summary may be there if we're previewing
    }
}

function enforceSummary()
{
    var btn_save = document.getElementById('edbtn__save');
    var summary_input = document.getElementById('edit__summary');
    var minoredit_input = document.getElementById('minoredit');
    var disabled = false;

    if(summary_input.value.replace(/^\s+/,"") === '' && !minoredit_input.checked)
        {disabled = true;}

    if(disabled != btn_save.disabled || btn_save.disabled === null)
    {
        btn_save.className = disabled ? 'button button_disabled' : 'button';
        btn_save.disabled = disabled;
    }
}

addInitEvent(function(){installSummaryEnforcement();});
</script>
</head>
<body onload="boldEvents();">

<div id="headnav">
 <a href="http://php.net">
  <img src="https://static.php.net/www.php.net/images/php.gif" alt="PHP" width="120" height="67" />
 </a>
 <div id="headmenu">
  <a href="http://php.net/downloads.php">downloads</a> |
  <a href="http://php.net/docs.php">documentation</a> |
  <a href="http://php.net/FAQ.php">faq</a> |
  <a href="http://php.net/support.php">getting help</a> |
  <a href="http://php.net/mailing-lists.php">mailing lists</a> |
  <a href="http://bugs.php.net/">reporting bugs</a> |
  <a href="http://php.net/sites.php">php.net sites</a> |
  <a href="http://php.net/links.php">links</a> |
  <a href="http://php.net/conferences/">conferences</a> |
  <a href="http://php.net/my.php">my php.net</a>
 </div>
</div>

<div id="headsearch"><br /></div>

<br />

<?php /*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>
<div class="dokuwiki">
  <?php html_msgarea()?>

  <div class="stylehead">

    <div class="header">
      <div class="pagename">
        [[<a href="/">start</a><?php
if (tpl_pagetitle($ID,true) !== 'start') {
  $_link = '';
  $_parts = explode(':', tpl_pagetitle($ID,true));  while ($_part = array_shift($_parts)) {
    $_link .= '/'.$_part;
    echo ':<a href="'.$_link.'">'.$_part.'</a>';
  }
}
    ?>]]
    </div>
      <div class="logo">
        <?php tpl_link(wl(),$conf['title'],'name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[ALT+H]"')?>
      </div>

      <div class="clearer"></div>
    </div>

    <?php /*old includehook*/ @include(dirname(__FILE__).'/header.html')?>

    <div class="bar" id="bar__top">
      <div class="bar-left" id="bar__topleft">
        <?php tpl_button('edit')?>
        <?php tpl_button('history')?>
      </div>

      <div class="bar-right" id="bar__topright">
        <?php tpl_button('recent')?>
        <?php tpl_searchform()?>&nbsp;
      </div>

      <div class="clearer"></div>
    </div>

    <?php if($conf['breadcrumbs']){?>
    <div class="breadcrumbs">
      <?php tpl_breadcrumbs()?>
      <?php //tpl_youarehere() //(some people prefer this)?>
    </div>
    <?php }?>

    <?php if($conf['youarehere']){?>
    <div class="breadcrumbs">
      <?php tpl_youarehere() ?>
    </div>
    <?php }?>

  </div>
  <?php flush()?>

  <?php /*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>

  <div class="page">
    <!-- wikipage start -->
    <?php tpl_content()?>
    <!-- wikipage stop -->
  </div>

  <div class="clearer">&nbsp;</div>

  <?php flush()?>

  <div class="stylefoot">

    <div class="meta">
      <div class="user">
        <?php tpl_userinfo()?>
      </div>
      <div class="doc">
        <?php tpl_pageinfo()?>
      </div>
    </div>

   <?php /*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

    <div class="bar" id="bar__bottom">
      <div class="bar-left" id="bar__bottomleft">
        <?php tpl_button('edit')?>
        <?php tpl_button('history')?>
      </div>
      <div class="bar-right" id="bar__bottomright">
        <?php tpl_button('subscription')?>
        <?php tpl_button('admin')?>
        <?php tpl_button('profile')?>
        <?php tpl_button('login')?>
        <?php tpl_button('index')?>
        <?php tpl_button('top')?>&nbsp;
      </div>
      <div class="clearer"></div>
    </div>

  </div>

</div>

<div id="footnav">
<a href="http://php.net/feed.atom">Atom</a> | <a href="http://php.net/source.php?url=/index.php">show source</a> |
 <a href="http://php.net/credits.php">credits</a> |
 <a href="http://php.net/stats/">stats</a> |
 <a href="http://php.net/sitemap.php">sitemap</a> |
 <a href="http://php.net/contact.php">contact</a> |
 <a href="http://php.net/contact.php#ads">advertising</a> |
 <a href="http://php.net/mirrors.php">mirror sites</a>
</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="http://php.net/copyright.php">Copyright &copy; 2001-<?php echo date('Y');?> The PHP Group</a><br />
  All rights reserved.
 </div>
 <br />
 <?php /*old includehook*/ @include(dirname(__FILE__).'/footer.html')?>
 <br />
</div>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>
</body>
</html>
