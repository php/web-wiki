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
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

use League\CommonMark\Util\RegexHelper;

final class ImageRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @param Image                    $inline
     * @param ChildNodeRendererInterface $DWRenderer
     *
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $DWRenderer): string
    {
        Image::assertInstanceOf($node);

        $attrs = $node->data->get('attributes');

        $forbidUnsafeLinks = !$this->config->get('allow_unsafe_links');
        if ($forbidUnsafeLinks && RegexHelper::isLinkPotentiallyUnsafe($inline->getUrl())) {
            $attrs['src'] = '';
        } else {
            $attrs['src'] = $node->getUrl();
        }

        $alt = $DWRenderer->renderNodes($node->children());
        $alt = \preg_replace('/\<[^>]*alt="([^"]*)"[^>]*\>/', '$1', $alt);
        $attrs['alt'] = \preg_replace('/\<[^>]*\>/', '', $alt);

        if (isset($node->data['title'])) {
            $attrs['title'] = $node->data['title'];
        }

        $result = '{{' . $attrs['src'];
        $attrs['alt'] ? $result.= '|' . $attrs['alt'] . '}}' : $result.= '}}';

        return $result;
    }   

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
