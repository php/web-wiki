<?php
/*
 * This file is part of the clockoon/dokuwiki-commonmark-plugin package.
 *
 * (c) Sungbin Jeon <clockoon@gmail.com>
 *
 * Original code based on the followings:
 * - CommonMark JS reference parser (https://bitly.com/commonmark-js) (c) John MacFarlane
 * - league/commonmark (https://github.com/thephpleague/commonmark) (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 require_once __DIR__.'/src/bootstrap.php';

 use Dokuwiki\Plugin\Commonmark\Commonmark;

 if(!defined('DOKU_INC')) die();

 class action_plugin_commonmark extends DokuWiki_Action_Plugin {
    /**
     * pass text to Commonmark parser before DW parser
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'BEFORE', $this,
                                   '_commonmarkparse');        
    }

    public function _commonmarkparse(Doku_Event $event, $param) {
        // check force_commonmark option; if 1, ignore doctype
        if ($this->getConf('force_commonmark')) {
            $markdown = ltrim($event->data);
            $event->data = Commonmark::RendtoDW($markdown, $this->getConf('frontmatter_tag'));
        }
        elseif (preg_match('/\A<!DOCTYPE markdown>/',$event->data)) {
            $markdown = preg_replace('/\A<!DOCTYPE markdown>\n/','',$event->data);
            $markdown = ltrim($markdown);
            $event->data = Commonmark::RendtoDW($markdown, $this->getConf('frontmatter_tag'));
        }
    }
}
