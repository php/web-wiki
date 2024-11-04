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

namespace DokuWiki\Plugin\Commonmark\Extension\Renderer\Block;

use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\Xml;
use League\CommonMark\Xml\XmlNodeRendererInterface;

final class IndentedCodeRenderer implements NodeRendererInterface
{
    /**
     * @param IndentedCode             $block
     * @param ChildNodeRendererInterface $DWRenderer
     * @param bool                     $inTightList
     *
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $DWRenderer): string
    {
        IndentedCode::assertInstanceOf($node);

        # As in FencedCodeRenderer.php, do not escape code block
        # return "\n  " . Xml::escape($node->getStringContent());
        return "\n  " . preg_replace("/[\n\r]/", "\n  ", $node->getLiteral());
    }
}
