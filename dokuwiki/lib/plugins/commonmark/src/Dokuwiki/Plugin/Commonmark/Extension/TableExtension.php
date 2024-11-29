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

namespace DokuWiki\Plugin\Commonmark\Extension;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Table\TableStartParser;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use Dokuwiki\Plugin\Commonmark\Extension\Renderer\Block\TableRenderer;
use Dokuwiki\Plugin\Commonmark\Extension\Renderer\Block\TableSectionRenderer;
use Dokuwiki\Plugin\Commonmark\Extension\Renderer\Block\TableRowRenderer;
use Dokuwiki\Plugin\Commonmark\Extension\Renderer\Block\TableCellRenderer;

final class TableExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(new TableStartParser())

            ->addRenderer(Table::class, new TableRenderer())
            ->addRenderer(TableSection::class, new TableSectionRenderer())
            ->addRenderer(TableRow::class, new TableRowRenderer())
            ->addRenderer(TableCell::class, new TableCellRenderer())
        ;
    }
}
