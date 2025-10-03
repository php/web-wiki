<?php

/**
 * RFC-specific feed item processor for the rfcfeed plugin
 *
 * Provides enhanced processing for RFC pages including:
 * - Status change detection (Discussion → Voting → Implemented)
 * - RFC metadata extraction (author, version, deadlines)
 * - Enhanced titles and descriptions
 * - Categorization by change type
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Christopher Miller <christophercarlmiller@outlook.com>
 */
class action_plugin_rfcfeed_RFCFeedItemProcessor
{
    /** @var array Feed item data */
    protected $data;

    /**
     * Constructor
     *
     * @param array $data Feed item data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Process an RFC feed item with enhancements
     *
     * @param array $data Feed item data
     * @return array Enhanced feed item data
     */
    public function processRFCItem($data)
    {
        // Get current and previous content for comparison
        $pageId = $data['id'] ?? '';
        $currentContent = $this->getPageContent($pageId);
        $previousContent = $this->getPreviousPageContent($pageId, $data['date'] ?? time());

        // Extract RFC metadata
        $rfcMeta = $this->extractRFCMetadata($currentContent);
        $previousMeta = $this->extractRFCMetadata($previousContent);

        // Detect status change
        $statusChange = $this->detectStatusChange($previousMeta, $rfcMeta, $data['sum'] ?? '');

        // Enhance feed item data
        $data['rfc_meta'] = $rfcMeta;
        $data['status_change'] = $statusChange;
        $data['enhanced_title'] = $this->enhanceRFCTitle($data, $rfcMeta, $statusChange);
        $data['enhanced_description'] = $this->enhanceRFCDescription($data, $rfcMeta, $statusChange);
        $data['categories'] = $this->determineRFCCategories($data, $rfcMeta, $statusChange);

        return $data;
    }

    /**
     * Extract RFC metadata from page content
     *
     * @param string $content Page content
     * @return array RFC metadata
     */
    protected function extractRFCMetadata($content)
    {
        $meta = [
            'status' => 'Unknown',
            'author' => '',
            'version' => '',
            'voting_deadline' => '',
            'target_version' => '',
            'implementation_status' => ''
        ];

        if (empty($content)) {
            return $meta;
        }

        // Extract status - supports multiple formats
        $statusPatterns = [
            '/Status:\s*([^\n\r]+)/i',
            '/\*\*Status:\*\*\s*([^\n\r]+)/i',
            '/==+\s*Status\s*==+\s*([^\n\r]+)/i',
        ];

        foreach ($statusPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $meta['status'] = trim(strip_tags($matches[1]));
                break;
            }
        }

        // Extract author
        $authorPatterns = [
            '/Author:\s*([^\n\r]+)/i',
            '/\*\*Author:\*\*\s*([^\n\r]+)/i',
            '/==+\s*Author\s*==+\s*([^\n\r]+)/i',
        ];

        foreach ($authorPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $meta['author'] = trim(strip_tags($matches[1]));
                break;
            }
        }

        // Extract version
        if (preg_match('/Version:\s*([^\n\r]+)/i', $content, $matches)) {
            $meta['version'] = trim(strip_tags($matches[1]));
        }

        // Extract voting deadline
        $deadlinePatterns = [
            '/Voting deadline:\s*([^\n\r]+)/i',
            '/Deadline:\s*([^\n\r]+)/i',
            '/Voting ends?:\s*([^\n\r]+)/i',
        ];

        foreach ($deadlinePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $meta['voting_deadline'] = trim(strip_tags($matches[1]));
                break;
            }
        }

        // Extract target PHP version
        if (preg_match('/Target.*(?:PHP|version):\s*([^\n\r]+)/i', $content, $matches)) {
            $meta['target_version'] = trim(strip_tags($matches[1]));
        }

        return $meta;
    }

    /**
     * Detect RFC status changes between versions
     *
     * @param array $oldMeta Previous RFC metadata
     * @param array $newMeta Current RFC metadata
     * @param string $summary Edit summary
     * @return array|null Status change information
     */
    protected function detectStatusChange($oldMeta, $newMeta, $summary = '')
    {
        $oldStatus = $oldMeta['status'] ?? 'Unknown';
        $newStatus = $newMeta['status'] ?? 'Unknown';

        if ($oldStatus !== $newStatus && $newStatus !== 'Unknown') {
            return [
                'type' => 'status_change',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'summary' => $summary ?: "Status changed from {$oldStatus} to {$newStatus}",
                'is_voting_start' => $this->isVotingStart($oldStatus, $newStatus),
                'is_voting_end' => $this->isVotingEnd($oldStatus, $newStatus),
            ];
        }

        return null;
    }

    /**
     * Check if status change represents voting start
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    protected function isVotingStart($oldStatus, $newStatus)
    {
        $votingStatuses = ['voting', 'vote', 'under vote'];
        return !in_array(strtolower($oldStatus), $votingStatuses) &&
               in_array(strtolower($newStatus), $votingStatuses);
    }

    /**
     * Check if status change represents voting end
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    protected function isVotingEnd($oldStatus, $newStatus)
    {
        $votingStatuses = ['voting', 'vote', 'under vote'];
        $endStatuses = ['accepted', 'declined', 'rejected', 'implemented', 'withdrawn'];
        return in_array(strtolower($oldStatus), $votingStatuses) &&
               in_array(strtolower($newStatus), $endStatuses);
    }

    /**
     * Enhance RFC title with status indicators
     *
     * @param array $data Feed item data
     * @param array $rfcMeta RFC metadata
     * @param array|null $statusChange Status change information
     * @return string Enhanced title
     */
    protected function enhanceRFCTitle($data, $rfcMeta, $statusChange)
    {
        // Get title from data or construct it
        global $conf;
        $pageId = $data['id'] ?? '';
        if ($conf['useheading']) {
            $title = p_get_first_heading($pageId);
        } else {
            $title = noNS($pageId);
        }

        if ($statusChange) {
            $title .= " [Status Changed: {$statusChange['old_status']} → {$statusChange['new_status']}]";
        } elseif (!empty($rfcMeta['status']) && $rfcMeta['status'] !== 'Unknown') {
            $title .= " [{$rfcMeta['status']}]";
        }

        return $title;
    }

    /**
     * Enhance RFC description with metadata and context
     *
     * @param array $data Feed item data
     * @param array $rfcMeta RFC metadata
     * @param array|null $statusChange Status change information
     * @return string Enhanced description
     */
    protected function enhanceRFCDescription($data, $rfcMeta, $statusChange)
    {
        $description = '';

        // Status change information
        if ($statusChange) {
            $description .= "RFC status changed from {$statusChange['old_status']} to {$statusChange['new_status']}.\n";
            if ($statusChange['is_voting_start']) {
                $description .= "Voting has started!\n";
            } elseif ($statusChange['is_voting_end']) {
                $description .= "Voting has ended.\n";
            }
            if (!empty($rfcMeta['voting_deadline'])) {
                $description .= "Voting deadline: {$rfcMeta['voting_deadline']}\n";
            }
            $description .= "\n";
        }

        // Edit summary
        if (!empty($data['sum'])) {
            $description .= "Change summary: " . $data['sum'] . "\n\n";
        }

        // RFC metadata
        $description .= "RFC Details:\n";
        if (!empty($rfcMeta['status'])) {
            $description .= "• Current Status: {$rfcMeta['status']}\n";
        }
        if (!empty($rfcMeta['author'])) {
            $description .= "• Author: {$rfcMeta['author']}\n";
        }
        if (!empty($rfcMeta['version'])) {
            $description .= "• Version: {$rfcMeta['version']}\n";
        }
        if (!empty($rfcMeta['target_version'])) {
            $description .= "• Target PHP Version: {$rfcMeta['target_version']}\n";
        }
        if (!empty($rfcMeta['voting_deadline'])) {
            $description .= "• Voting Deadline: {$rfcMeta['voting_deadline']}\n";
        }

        return $description;
    }

    /**
     * Determine RFC-specific categories
     *
     * @param array $data Feed item data
     * @param array $rfcMeta RFC metadata
     * @param array|null $statusChange Status change information
     * @return array Categories
     */
    protected function determineRFCCategories($data, $rfcMeta, $statusChange)
    {
        $categories = ['rfc'];

        // Change type detection
        $changeType = $data['type'] ?? '';
        if ($changeType === DOKU_CHANGE_TYPE_CREATE) {
            $categories[] = 'rfc-new';
        } elseif ($statusChange) {
            $categories[] = 'rfc-status-change';
            if ($statusChange['is_voting_start']) {
                $categories[] = 'rfc-voting-start';
            } elseif ($statusChange['is_voting_end']) {
                $categories[] = 'rfc-voting-end';
            }
        } else {
            $categories[] = 'rfc-content-update';
        }

        // Detect comment additions (heuristic based on summary and size change)
        if ($this->isLikelyComment($data)) {
            $categories[] = 'rfc-comment';
        }

        return $categories;
    }

    /**
     * Heuristic to detect if change is likely a comment
     *
     * @param array $data Feed item data
     * @return bool
     */
    protected function isLikelyComment($data)
    {
        $summary = strtolower($data['sum'] ?? '');
        $sizeChange = $data['sizechange'] ?? 0;

        // Check for comment-related keywords in summary
        $commentKeywords = ['comment', 'reply', 'response', 'note', 'feedback'];
        foreach ($commentKeywords as $keyword) {
            if (strpos($summary, $keyword) !== false) {
                return true;
            }
        }

        // Small positive size changes might be comments
        if ($sizeChange > 0 && $sizeChange < 500) {
            return true;
        }

        return false;
    }

    /**
     * Get page content for a specific page
     *
     * @param string $pageId Page identifier
     * @return string Page content
     */
    protected function getPageContent($pageId)
    {
        if (empty($pageId)) return '';

        $file = wikiFN($pageId);
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    /**
     * Get previous version of page content
     *
     * @param string $pageId Page identifier
     * @param int $timestamp Current revision timestamp
     * @return string Previous page content
     */
    protected function getPreviousPageContent($pageId, $timestamp)
    {
        if (empty($pageId)) return '';

        // Get the revision before the current one
        $changelog = new \dokuwiki\ChangeLog\PageChangeLog($pageId);
        $revisions = $changelog->getRevisions(0, 2);

        if (count($revisions) >= 2) {
            $previousRev = $revisions[1];
            $file = wikiFN($pageId, $previousRev);
            if (file_exists($file)) {
                return file_get_contents($file);
            }
        }

        return '';
    }
}
