<?php
/**
 * DokuWiki Default Template 2012
 *
 * @link     http://dokuwiki.org/template
 * @author   Anika Henke <anika@selfthinker.org>
 * @author   Clarence Lee <clarencedglee@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */
header('X-UA-Compatible: IE=edge,chrome=1');

$hasSidebar = page_findnearest($conf['sidebar']);
$showSidebar = $hasSidebar && ($ACT=='show');
$TITLE = tpl_pagetitle(null, true);
$SEARCH    = array(
 "method"      => "get",
 "action"      => "/start",
 "placeholder" => "Search",
 "name"        => "id",
 "hidden"      => array(array("name" => "do", "value" => "search")),

);

if (empty($_SERVER['REMOTE_USER'])) {
    $LINKS = array(
        array("href" => "?do=login",    "text" => "Login",),
        array("href" => "?do=register", "text" => "Register", ),
    );
} else {
    $name = hsc($INFO['userinfo']['name']).' ('.hsc($_SERVER['REMOTE_USER']). ')';
    $LINKS = array(
        array("href" => "?do=edit",     "text" => "Edit this page",),
        array("href" => "?do=admin",    "text" => "Admin",),
        array("href" => "?do=logout&sectok=" . urlencode(getSecurityToken()), "text" => "Logout",),
        array("href" => "?do=profile",  "text" => $name,),
    );
}
ob_start();
tpl_metaheaders();
$HEAD_WIKI = ob_get_clean();
$CSS = array("/styles/wiki.css");

include __DIR__ . "/../../../shared/templates/header.inc";
?>

<section id="breadcrumbs">
<nav>
<?php tpl_youarehere(' › '); ?>
</nav>
</section>
<section class="mainscreen">
    <!--[if lte IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->
    <div id="dokuwiki__site"><div id="dokuwiki__top" class="site <?php echo tpl_classes(); ?> <?php
        echo ($showSidebar) ? 'showSidebar' : ''; ?> <?php echo ($hasSidebar) ? 'hasSidebar' : ''; ?>">


        <div class="wrapper group">

            <?php if($showSidebar): ?>
                <!-- ********** ASIDE ********** -->
                <div id="dokuwiki__aside"><div class="pad include group">
                    <h3 class="toggle"><?php echo $lang['sidebar'] ?></h3>
                    <div class="content">
                        <?php tpl_flush() ?>
                        <?php tpl_includeFile('sidebarheader.html') ?>
                        <?php tpl_include_page($conf['sidebar'], 1, 1) ?>
                        <?php tpl_includeFile('sidebarfooter.html') ?>
                    </div>
                </div></div><!-- /aside -->
            <?php endif; ?>

            <!-- ********** CONTENT ********** -->
            <div id="dokuwiki__content"><div class="pad group">

                <div class="pageId"><span><?php echo hsc($ID) ?></span></div>

                <div class="page group">
                    <?php tpl_flush() ?>
                    <?php tpl_includeFile('pageheader.html') ?>
                    <!-- wikipage start -->
                    <?php html_msgarea() ?>
                    <?php tpl_content(false) ?>
                    <!-- wikipage stop -->
                    <?php tpl_includeFile('pagefooter.html') ?>
                </div>

                <div class="docInfo"><?php tpl_pageinfo() ?></div>

                <?php tpl_flush() ?>
            </div></div><!-- /content -->

            <hr class="a11y" />

            <!-- PAGE ACTIONS -->
            <div id="dokuwiki__pagetools">
                <h3 class="a11y"><?php echo $lang['page_tools']; ?></h3>
                <div class="tools">
                    <ul>
                        <?php
                            $data = array(
                                'view'  => 'main',
                                'items' => array(
                                    'edit'      => tpl_action('edit',      1, 'li', 1, '<span>', '</span>'),
                                    'revert'    => tpl_action('revert',    1, 'li', 1, '<span>', '</span>'),
                                    'revisions' => tpl_action('revisions', 1, 'li', 1, '<span>', '</span>'),
                                    'backlink'  => tpl_action('backlink',  1, 'li', 1, '<span>', '</span>'),
                                    'subscribe' => tpl_action('subscribe', 1, 'li', 1, '<span>', '</span>'),
                                    'top'       => tpl_action('top',       1, 'li', 1, '<span>', '</span>')
                                )
                            );

                            // the page tools can be amended through a custom plugin hook
                            $evt = new Doku_Event('TEMPLATE_PAGETOOLS_DISPLAY', $data);
                            if($evt->advise_before()){
                                foreach($evt->data['items'] as $k => $html) echo $html;
                            }
                            $evt->advise_after();
                            unset($data);
                            unset($evt);
                        ?>
                    </ul>
                </div>
            </div>
        </div><!-- /wrapper -->

<?php /*include('tpl_footer.php') */?>
    </div></div><!-- /site -->

    <div class="no"><?php tpl_indexerWebBug() /* provide DokuWiki housekeeping, required in all templates */ ?></div>
    <div id="screen__mode" class="no"></div><?php /* helper to detect CSS media query in script.js */ ?>
    <!--[if ( lte IE 7 | IE 8 ) ]></div><![endif]-->
</section>

<?php $SECONDSCREEN = tpl_toc(true);
$JS = false;
include __DIR__ . "/../../../shared/templates/footer.inc";
