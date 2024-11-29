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

namespace DokuWiki\Plugin\Commonmark\Extension\Renderer\Inline;

use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Util\Xml;

final class TextRenderer implements NodeRendererInterface
{
    /**
     * @param Text                     $inline
     * @param ChildNodeRendererInterface $DWRenderer
     *
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $DWRenderer)
    {
        Text::assertInstanceOf($node);

        //return Xml::escape($inline->getContent());
        return $node->getLiteral();
    }
}
