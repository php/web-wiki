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

namespace Dokuwiki\Plugin\Commonmark;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;

/**
 * Renders a parsed AST to DW
 */
final class DWRenderer implements ChildNodeRendererInterface
{
    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * @param EnvironmentInterface $environment
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function getBlockSeparator(): string
    {
        return $this->environment->getConfiguration()->get('renderer/block_separator');
    }

    public function getInnerSeparator(): string
    {
        return $this->environment->getConfiguration()->get('renderer/inner_separator');
    }

    /**
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getOption(string $option, $default = null)
    {
        return $this->environment->getConfiguration()->get('renderer/' . $option, $default);
    }

    public function renderNodes(iterable $nodes): string
    {
        $output = '';

        $isFirstItem = true;

        foreach ($nodes as $node) {
            if (! $isFirstItem && $node instanceof AbstractBlock) {
                $output .= $this->getBlockSeparator();
            }

            $output .= $this->renderNode($node);

            $isFirstItem = false;
        }

        return $output;
    }

    public function renderNode(Node $node)
    {
        $renderers = $this->environment->getRenderersForClass(\get_class($node));

        foreach ($renderers as $renderer) {
            \assert($renderer instanceof NodeRendererInterface);
            if (($result = $renderer->render($node, $this)) !== null) {
                return $result;
            }
        }

        throw new \RuntimeException('Unable to find corresponding renderer for node type ' . \get_class($node));

    }


}
