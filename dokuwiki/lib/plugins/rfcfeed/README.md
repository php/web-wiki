# RFC Feed Plugin for DokuWiki

Enhanced RSS feeds with RFC-specific features for the PHP Wiki.

## Features

This plugin provides three distinct RSS feed modes with enhanced RFC tracking capabilities:

### Feed Modes

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

## Usage

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
    Voting has started!
    Voting deadline: 2025-02-15

    Change summary: Added implementation details and voting section.

    RFC Details:
    • Current Status: Voting
    • Author: John Doe
    • Version: 1.2
    • Target PHP Version: 8.4
    • Voting Deadline: 2025-02-15
  </description>
  <category>rfc-status-change</category>
  <category>rfc-voting-start</category>
</item>
```

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

## Technical Details

### Implementation

This plugin uses DokuWiki's event system to extend RSS feed functionality without modifying core files:

- **FEED_MODE_UNKNOWN** - Handles custom feed modes (`rfc-only`, `non-rfc`)
- **FEED_ITEM_ADD** - Enhances RFC items with metadata
- **FEED_OPTS_POSTPROCESS** - Adds RFC-specific options

### Files

- `action.php` - Main plugin file with event handlers
- `RFCFeedItemProcessor.php` - RFC metadata extraction and processing
- `plugin.info.txt` - Plugin metadata
- `README.md` - This file

### RFC Detection

The plugin identifies RFC pages using multiple methods:

1. **Namespace-based** - Pages starting with `rfc:`
2. **ACL-based** - Permission checking for RFC namespace
3. **Discussion pages** - Patterns like `rfc:*_talk`, `discussion:rfc:*`

### Backward Compatibility

- ✅ All existing feeds continue to work unchanged
- ✅ Default behavior remains identical
- ✅ No breaking changes to existing URLs
- ✅ Existing caching system preserved and enhanced
- ✅ All feed formats (RSS, Atom) supported

## Installation

This plugin is already installed as part of the PHP Wiki customization. It will persist through DokuWiki upgrades since it's in the `lib/plugins/` directory.

## Upgrade Safety

Unlike the previous implementation which modified DokuWiki core files, this plugin:

- ✅ Survives DokuWiki upgrades without modification
- ✅ Requires no cherry-picking of commits after upgrades
- ✅ Follows DokuWiki plugin best practices
- ✅ Can be easily disabled/enabled without code changes

## License

GPL 2 http://www.gnu.org/licenses/gpl-2.0.html

## Author

Christopher Miller <christophercarlmiller@outlook.com>

## History

This plugin was created to refactor RSS feed enhancements that were previously implemented by directly modifying DokuWiki core files (commit 08c6e393). The plugin approach ensures these features survive DokuWiki upgrades.
