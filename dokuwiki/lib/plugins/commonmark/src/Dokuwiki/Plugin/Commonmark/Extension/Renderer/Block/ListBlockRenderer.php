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

use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;

final class ListBlockRenderer implements NodeRendererInterface
{
    /**
     * @param ListBlock                $block
     * @param ChildNodeRendererInterface $DWRenderer
     * @param bool                     $inTightList
     *
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $DWRenderer): string
    {
        ListBlock::assertInstanceOf($node);

        $listData = $node->getListData();
        $tag = $listData->type === ListBlock::TYPE_BULLET ? "* " : "- ";

        $attrs = $node->data->get('attributes');

        if ($listData->start !== null && $listData->start !== 1) {
            $attrs['start'] = (string) $listData->start;
        }

        $result = 
                $DWRenderer->renderNodes(
                    $node->children(),
                    $node->isTight()
                );

        $result = preg_replace("/\n/", "\n  ", $result); # add two-space indentation
        $result = preg_replace("/\n(\s\s)+\n/", "\n", $result); # remove unwanted newline
        $result = preg_replace("/<li>/", $tag, $result); # add DW list bullet
        return "  " . $result;

    }
}
