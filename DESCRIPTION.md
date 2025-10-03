# Enhanced RSS Feeds with RFC-Specific Features

## Summary

This PR adds three new RSS feed modes to the PHP wiki with enhanced RFC tracking capabilities, providing the PHP community with comprehensive feeds for staying informed about RFC developments, voting activities, and general wiki changes.

## Background

Currently, the PHP wiki provides a single RSS feed that combines all wiki changes. This makes it difficult for community members to:
- Track only RFC-related activities
- Monitor RFC status changes (Discussion → Voting → Implemented)
- Follow voting processes and deadlines
- Filter out non-RFC content when focusing on language development

This enhancement was discussed on a live podcast and addresses the community's need for better RFC change tracking.

## Features Added

### Three Distinct Feed Modes
1. **All wiki changes** (`?mode=recent`) - Enhanced existing feed with RFC metadata
2. **RFC-only changes** (`?mode=rfc-only`) - New feed focused exclusively on RFC activities
3. **Non-RFC changes** (`?mode=non-rfc`) - New feed for all non-RFC wiki content

### Enhanced RFC Processing
- **Status Change Detection** - Automatically detects and highlights RFC status transitions
- **Rich Metadata Extraction** - Parses RFC author, version, voting deadlines, target PHP version
- **Enhanced Titles** - Includes status indicators like "[Status Changed: Discussion → Voting]"
- **Detailed Descriptions** - Provides context with RFC metadata and change summaries
- **Smart Categorization** - Categories like `rfc-status-change`, `rfc-voting-start`, `rfc-new`
- **Discussion Page Tracking** - Monitors RFC-related discussion pages
- **Comment Detection** - Identifies likely comment additions

### Feed URLs
```
https://wiki.php.net/feed.php?mode=recent     # All changes (default, enhanced)
https://wiki.php.net/feed.php?mode=rfc-only   # RFC changes only
https://wiki.php.net/feed.php?mode=non-rfc    # Non-RFC changes only
```

### RFC Enhancement Controls
```
https://wiki.php.net/feed.php?mode=rfc-only&rfc_enhanced=1     # Enhanced features (default)
https://wiki.php.net/feed.php?mode=rfc-only&rfc_status=1       # Status change detection
https://wiki.php.net/feed.php?mode=rfc-only&rfc_discussions=1  # Discussion tracking
```

## Example Enhanced Feed Content

### RFC Status Change
```xml
<item>
  <title>RFC: Add new array functions [Status Changed: Discussion → Voting]</title>
  <description>
    RFC status changed from Discussion to Voting.
    🗳️ Voting has started!
    Voting deadline: 2024-02-15

    Change summary: Added implementation details and voting section.

    RFC Details:
    • Current Status: Voting
    • Author: John Doe
    • Version: 1.2
    • Target PHP Version: 8.4
    • Voting Deadline: 2024-02-15
  </description>
  <category>rfc-status-change</category>
  <category>rfc-voting-start</category>
</item>
```

## Technical Implementation

### Files Modified
- `dokuwiki/inc/Feed/FeedCreatorOptions.php` - Added RFC enhancement options and new modes
- `dokuwiki/inc/Feed/FeedCreator.php` - Added RFC filtering and processing logic
- `dokuwiki/feed.php` - Updated documentation for new parameters

### Files Added
- `dokuwiki/inc/Feed/RFCFeedItemProcessor.php` - RFC-specific processing and metadata extraction

### Backward Compatibility
- ✅ All existing feeds continue to work unchanged
- ✅ Default behavior remains identical
- ✅ No breaking changes to existing URLs
- ✅ Existing caching system preserved and enhanced
- ✅ All feed formats (RSS, Atom) supported

## Benefits

### For RFC Authors
- Get notified when RFCs receive comments
- Track status changes and voting progress
- Monitor discussion activity across related pages

### For PHP Community
- Stay informed about RFC developments without noise
- Follow voting processes in real-time
- Track implementation progress with rich context

### For Tools and Aggregators
- Rich categorization enables intelligent filtering
- Enhanced metadata supports better presentation
- Separate feeds allow targeted monitoring

## Testing

The implementation includes comprehensive RFC detection and processing:
- Namespace-based RFC identification (`rfc:*` pages)
- ACL-based permission checking
- Discussion page pattern matching
- Content parsing for status and metadata
- All existing DokuWiki functionality preserved

## Impact

This enhancement provides the PHP community with the RSS feed functionality discussed in recent podcasts, enabling better tracking of RFC activities and more informed participation in PHP's development process.

The implementation is production-ready and follows DokuWiki's architectural patterns while adding no external dependencies.