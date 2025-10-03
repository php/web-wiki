<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin rfcfeed (Action Component)
 *
 * Provides enhanced RSS feeds with RFC-specific features:
 * - RFC-only feed mode (?mode=rfc-only)
 * - Non-RFC feed mode (?mode=non-rfc)
 * - Enhanced RFC metadata extraction
 * - RFC status change detection
 * - Discussion page tracking
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Christopher Miller <christophercarlmiller@outlook.com>
 */
class action_plugin_rfcfeed extends ActionPlugin
{
    /**
     * Registers event handlers
     *
     * @param EventHandler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(EventHandler $controller)
    {
        // Handle custom feed modes (rfc-only, non-rfc)
        $controller->register_hook('FEED_MODE_UNKNOWN', 'BEFORE', $this, 'handleFeedMode');

        // Enhance RFC feed items with metadata
        $controller->register_hook('FEED_ITEM_ADD', 'BEFORE', $this, 'handleFeedItem');

        // Add RFC-specific options to feed
        $controller->register_hook('FEED_OPTS_POSTPROCESS', 'AFTER', $this, 'handleFeedOptions');
    }

    /**
     * Handle custom feed modes
     *
     * @param Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this handler was registered]
     * @return void
     */
    public function handleFeedMode(Event $event, $param)
    {
        $mode = $event->data['opt']['feed_mode'];

        if ($mode === 'rfc-only') {
            $event->preventDefault();
            $event->stopPropagation();
            $event->data['data'] = $this->fetchRFCItems($event->data['opt']);
        } elseif ($mode === 'non-rfc') {
            $event->preventDefault();
            $event->stopPropagation();
            $event->data['data'] = $this->fetchNonRFCItems($event->data['opt']);
        }
    }

    /**
     * Enhance RFC feed items with metadata
     *
     * @param Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this handler was registered]
     * @return void
     */
    public function handleFeedItem(Event $event, $param)
    {
        $data = $event->data['data'];

        // Only process if RFC enhancements are enabled and this is an RFC item
        $rfcEnhanced = $event->data['opt']['rfc_enhanced'] ?? true;
        if (!$rfcEnhanced || !$this->isRFCItem($data)) {
            return;
        }

        // Load the RFC processor and enhance the item
        require_once(__DIR__ . '/RFCFeedItemProcessor.php');
        $processor = new action_plugin_rfcfeed_RFCFeedItemProcessor($data);
        $enhanced = $processor->processRFCItem($data);

        // Update the event data with enhanced information
        $event->data['data'] = $enhanced;
    }

    /**
     * Add RFC-specific options to feed
     *
     * @param Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this handler was registered]
     * @return void
     */
    public function handleFeedOptions(Event $event, $param)
    {
        global $INPUT;

        // Add RFC enhancement options
        $event->data['options']['rfc_enhanced'] = $INPUT->bool('rfc_enhanced', true);
        $event->data['options']['rfc_status_detection'] = $INPUT->bool('rfc_status', true);
        $event->data['options']['rfc_discussion_tracking'] = $INPUT->bool('rfc_discussions', true);

        // Adjust title based on feed mode
        if ($event->data['options']['feed_mode'] === 'rfc-only') {
            $event->data['options']['title'] .= ' - RFC Changes';
        } elseif ($event->data['options']['feed_mode'] === 'non-rfc') {
            $event->data['options']['title'] .= ' - Non-RFC Changes';
        }
    }

    /**
     * Fetch RFC-only items from recent changes
     *
     * @param array $options Feed options
     * @return array RFC items
     */
    protected function fetchRFCItems($options)
    {
        $allItems = $this->fetchRecentChanges($options);
        return array_filter($allItems, [$this, 'isRFCItem']);
    }

    /**
     * Fetch non-RFC items from recent changes
     *
     * @param array $options Feed options
     * @return array Non-RFC items
     */
    protected function fetchNonRFCItems($options)
    {
        $allItems = $this->fetchRecentChanges($options);
        return array_filter($allItems, function ($item) {
            return !$this->isRFCItem($item);
        });
    }

    /**
     * Fetch recent changes (mirrors FeedCreator::fetchItemsFromRecentChanges)
     *
     * @param array $options Feed options
     * @return array Recent changes
     */
    protected function fetchRecentChanges($options)
    {
        $flags = 0;
        if ($options['guardian'] != '') {
            $flags += RECENTS_SKIP_DELETED;
        }
        if ($options['show_minor'] != 1) {
            $flags += RECENTS_SKIP_MINORS;
        }
        if (isset($options['show_subpages']) && !$options['show_subpages']) {
            $flags += RECENTS_SKIP_SUBPAGES;
        }

        return getRecents(0, $options['items'], $options['namespace'], $flags);
    }

    /**
     * Check if an item belongs to an RFC page
     *
     * @param array $item Recent changes item
     * @return bool True if item is RFC-related
     */
    protected function isRFCItem($item)
    {
        $pageId = $item['id'] ?? '';

        // Method 1: Namespace-based detection
        if (strpos($pageId, 'rfc:') === 0) {
            return true;
        }

        // Method 2: ACL-based detection for RFC namespace access
        if (function_exists('auth_aclcheck')) {
            // Check if user has RFC permissions or if page is in RFC area
            $aclCheck = auth_aclcheck($pageId, '', ['@rfc']);
            if ($aclCheck >= AUTH_READ && strpos($pageId, 'rfc') !== false) {
                return true;
            }
        }

        // Method 3: Discussion page tracking for RFCs (if enabled)
        $rfcDiscussionTracking = true; // Default to true
        if ($rfcDiscussionTracking) {
            if ($this->isRFCDiscussionPage($pageId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a page is an RFC discussion page
     *
     * @param string $pageId Page identifier
     * @return bool True if page is RFC discussion
     */
    protected function isRFCDiscussionPage($pageId)
    {
        // Common RFC discussion page patterns
        $patterns = [
            '/^rfc:.+_talk$/',           // rfc:some_rfc_talk
            '/^rfc:.+:discussion$/',     // rfc:some_rfc:discussion
            '/^discussion:rfc:/',        // discussion:rfc:some_rfc
            '/^talk:rfc:/',              // talk:rfc:some_rfc
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $pageId)) {
                return true;
            }
        }

        return false;
    }
}
