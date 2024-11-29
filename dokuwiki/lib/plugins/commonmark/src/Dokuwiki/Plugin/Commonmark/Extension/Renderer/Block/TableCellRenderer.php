<?php

declare(strict_types=1);

/*
 * This file is part of the clockoon/dokuwiki-commonmark-plugin package.
 *
 * (c) Sungbin Jeon <clockoon@gmail.com>
 *
 * Original code based on the followings:
 * - CommonMark JS reference parser (https://bitly.com/commonmark-js) (c) John MacFarlane
 * - league/commonmark (https://github.com/thephpleague/commonmark) (c) Colin O'Dell <colinodell@gmail.com>
 * - Commonmark Table extension  (c) Martin Hasoň <martin.hason@gmail.com>, Webuni s.r.o. <info@webuni.cz>, Colin O'Dell <colinodell@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DokuWiki\Plugin\Commonmark\Extension\Renderer\Block;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Extension\Table\TableCell;

final class TableCellRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $DWRenderer): string
    {
        TableCell::assertInstanceOf($node);

        # block type indicator on DW
        $separator = $node->getType() === TableCell::TYPE_HEADER ? '^' : '|';

        # align indicator on DW
        $lmargin = ' ';
        $rmargin = ' ';
        switch($node->getAlign()) {
            case "right":
                $lmargin = '  ';
                break;
            case "center":
                $lmargin = '  ';
                $rmargin = '  ';
                break;
        }

        $result = $separator . $lmargin . $DWRenderer->renderNodes($node->children()) . $rmargin;
        return $result;

    }
}
