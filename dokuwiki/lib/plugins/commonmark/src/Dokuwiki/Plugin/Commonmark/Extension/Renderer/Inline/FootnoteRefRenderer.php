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

declare(strict_types=1);

namespace DokuWiki\Plugin\Commonmark\Extension\Renderer\Inline;

use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

final class FootnoteRefRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    /** @var ConfigurationInterface */
    private ConfigurationInterface $config;

    public function render(Node $node, ChildNodeRendererInterface $DWRenderer)
    {
        FootnoteRef::assertInstanceOf($node);

        $attrs = $node->data->getData('attributes');

        # get parents iteratively until get top-level document
        $document = $node->parent()->parent();
        while (get_class($document)!='League\CommonMark\Node\Block\Document'){
            $document = $document->parent();
        }
        $walker = $document->walker();
        $title = $node->getReference()->getLabel();

        while ($event = $walker->next()) {
            $node = $event->getNode();
            if ($node instanceof Footnote && $title == $node->getReference()->getLabel()) {
                $text = $DWRenderer->renderNode($node->children()[0]);
                break;
            }
        }

        $result = '(('. $text. '))';
        return $result;

    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
