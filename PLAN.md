# Plan: Refactor RSS Feed Enhancements to Plugin Architecture

## Overview
The recent RSS feed enhancements (commit 08c6e393) directly modified DokuWiki core files, which will be overwritten during DokuWiki upgrades. This plan outlines how to refactor these changes into a plugin-based architecture using DokuWiki's native event system.

## Problem Statement
The following DokuWiki core files were modified and will be lost during upgrades:
- `dokuwiki/feed.php` - Documentation updates
- `dokuwiki/inc/Feed/FeedCreatorOptions.php` - Added RFC enhancement options
- `dokuwiki/inc/Feed/FeedCreator.php` - Added RFC filtering, feed modes, and processing logic
- `dokuwiki/inc/Feed/RFCFeedItemProcessor.php` - NEW FILE with RFC-specific processing

## Solution: Plugin-based Architecture

### DokuWiki's Feed Plugin System
DokuWiki already provides a plugin system for feeds via events:
- `FEED_MODE_UNKNOWN` - Triggered when an unknown feed mode is requested (line 218 in FeedCreator.php)
- `FEED_ITEM_ADD` - Triggered when adding items to feed (line 80 in FeedCreator.php)
- `FEED_OPTS_POSTPROCESS` - Triggered after options are processed (FeedCreatorOptions.php)

### Proposed Plugin Structure
Create a new action plugin: `dokuwiki/lib/plugins/rfcfeed/`

```
dokuwiki/lib/plugins/rfcfeed/
├── action.php                  # Main plugin file with event handlers
├── plugin.info.txt             # Plugin metadata
├── lang/                       # Language files (optional)
│   └── en/
│       └── settings.php
├── conf/                       # Configuration (optional)
│   ├── default.php
│   └── metadata.php
└── RFCFeedItemProcessor.php    # Moved from dokuwiki/inc/Feed/
```

## Implementation Steps

### Phase 1: Create Plugin Structure
1. **Create plugin directory structure**
   - Create `dokuwiki/lib/plugins/rfcfeed/` directory
   - Create `plugin.info.txt` with metadata (name, author, description, URL, date)

2. **Move RFC processor class**
   - Move `dokuwiki/inc/Feed/RFCFeedItemProcessor.php` to plugin directory
   - Update namespace from `dokuwiki\Feed` to plugin namespace
   - Adjust any class references and autoloading

3. **Create main action plugin file**
   - Create `action.php` extending `ActionPlugin`
   - Implement `register()` method to hook into events
   - Implement event handlers for:
     - `FEED_MODE_UNKNOWN` - Handle `rfc-only` and `non-rfc` modes
     - `FEED_ITEM_ADD` - Enhance RFC items with metadata
     - `FEED_OPTS_POSTPROCESS` - Add RFC-specific options

4. **Add configuration support (optional)**
   - Create `conf/default.php` with default settings:
     - `rfc_enhanced` (default: true)
     - `rfc_status_detection` (default: true)
     - `rfc_discussion_tracking` (default: true)
   - Create `conf/metadata.php` for admin UI

### Phase 2: Implement Event Handlers

#### Handler 1: Custom Feed Modes (`FEED_MODE_UNKNOWN`)
```php
public function handleFeedMode(Event $event, $param) {
    $mode = $event->data['opt']['feed_mode'];

    if ($mode === 'rfc-only') {
        $event->preventDefault();
        $event->data['data'] = $this->fetchRFCItems($event->data['opt']);
    } elseif ($mode === 'non-rfc') {
        $event->preventDefault();
        $event->data['data'] = $this->fetchNonRFCItems($event->data['opt']);
    }
}
```

#### Handler 2: RFC Item Enhancement (`FEED_ITEM_ADD`)
```php
public function handleFeedItem(Event $event, $param) {
    $data = $event->data['data'];

    if ($this->isRFCItem($data) && $this->getConf('rfc_enhanced')) {
        $processor = new RFCFeedItemProcessor($data);
        $enhanced = $processor->processRFCItem($data);
        $event->data['data'] = $enhanced;
    }
}
```

#### Handler 3: Add RFC Options (`FEED_OPTS_POSTPROCESS`)
```php
public function handleFeedOptions(Event $event, $param) {
    global $INPUT;

    $event->data['options']['rfc_enhanced'] =
        $INPUT->bool('rfc_enhanced', $this->getConf('rfc_enhanced'));
    $event->data['options']['rfc_status_detection'] =
        $INPUT->bool('rfc_status', $this->getConf('rfc_status_detection'));
    $event->data['options']['rfc_discussion_tracking'] =
        $INPUT->bool('rfc_discussions', $this->getConf('rfc_discussion_tracking'));

    // Adjust title based on feed mode
    if ($event->data['options']['feed_mode'] === 'rfc-only') {
        $event->data['options']['title'] .= ' - RFC Changes';
    } elseif ($event->data['options']['feed_mode'] === 'non-rfc') {
        $event->data['options']['title'] .= ' - Non-RFC Changes';
    }
}
```

### Phase 3: Revert Core Changes

1. **Revert modified core files**
   ```bash
   git revert 08c6e393
   ```
   This will cleanly remove all changes from:
   - `dokuwiki/feed.php`
   - `dokuwiki/inc/Feed/FeedCreatorOptions.php`
   - `dokuwiki/inc/Feed/FeedCreator.php`
   - `dokuwiki/inc/Feed/RFCFeedItemProcessor.php` (delete)

2. **Verify clean revert**
   ```bash
   git diff master -- dokuwiki/feed.php
   git diff master -- dokuwiki/inc/Feed/
   ```
   Should show no differences in core files.

3. **Test plugin functionality**
   - Test `?mode=rfc-only` works via plugin
   - Test `?mode=non-rfc` works via plugin
   - Test RFC enhancement features
   - Verify backward compatibility with `?mode=recent`

### Phase 4: Documentation

1. **Update README.rst**
   - Add section about the RFC Feed plugin
   - Document that it's a custom plugin (not from DokuWiki repo)
   - Note it will persist through DokuWiki upgrades
   - Remove the need to cherry-pick RFC feed commits after upgrades

2. **Add plugin documentation**
   - Create `dokuwiki/lib/plugins/rfcfeed/README.md`
   - Document feed URLs and parameters
   - Document configuration options
   - Include examples from DESCRIPTION.md

3. **Update DESCRIPTION.md** (if needed)
   - Note implementation is now via plugin
   - Update any file paths in documentation

## Benefits of Plugin Approach

### Upgrade Safety
- ✅ Plugin files are never touched by DokuWiki upgrades
- ✅ No need to cherry-pick or reapply changes after upgrades
- ✅ No need to remember which commits to reapply
- ✅ Cleaner separation of custom code from core

### Maintainability
- ✅ All RFC feed logic in one directory
- ✅ Can be disabled/enabled without code changes
- ✅ Easy to share with other DokuWiki installations
- ✅ Clear ownership and versioning

### DokuWiki Best Practices
- ✅ Uses official plugin API and event system
- ✅ Follows DokuWiki plugin conventions
- ✅ No modifications to core code
- ✅ Can be managed via Extension Manager (if published)

## Testing Plan

1. **Functionality Testing**
   - [ ] Test `?mode=recent` - should work as before
   - [ ] Test `?mode=rfc-only` - should show only RFC changes
   - [ ] Test `?mode=non-rfc` - should show only non-RFC changes
   - [ ] Test RFC enhancement features (status detection, metadata)
   - [ ] Test configuration options work
   - [ ] Test discussion page tracking

2. **Upgrade Simulation**
   - [ ] Note current DokuWiki version
   - [ ] Simulate upgrade (unpack newer DokuWiki version)
   - [ ] Verify plugin still works after upgrade
   - [ ] Verify core files are unmodified

3. **Regression Testing**
   - [ ] Verify existing feed URLs still work
   - [ ] Verify feed readers can still consume feeds
   - [ ] Check for PHP errors in logs
   - [ ] Test with different feed types (RSS, Atom)

## Migration Path

### For Development/Testing
1. Create plugin on feature branch
2. Test plugin works correctly
3. Revert core changes on same branch
4. Verify everything still works
5. Merge to master

### For Production
1. Deploy plugin to production (copy plugin directory)
2. Verify feeds work with both implementations
3. Revert core changes (deploy reverted code)
4. Monitor for any issues
5. Update documentation

## File Checklist

### Files to Create (Plugin)
- [ ] `dokuwiki/lib/plugins/rfcfeed/plugin.info.txt`
- [ ] `dokuwiki/lib/plugins/rfcfeed/action.php`
- [ ] `dokuwiki/lib/plugins/rfcfeed/RFCFeedItemProcessor.php` (moved)
- [ ] `dokuwiki/lib/plugins/rfcfeed/README.md`
- [ ] `dokuwiki/lib/plugins/rfcfeed/conf/default.php` (optional)
- [ ] `dokuwiki/lib/plugins/rfcfeed/conf/metadata.php` (optional)

### Files to Revert (Core)
- [ ] `dokuwiki/feed.php`
- [ ] `dokuwiki/inc/Feed/FeedCreatorOptions.php`
- [ ] `dokuwiki/inc/Feed/FeedCreator.php`

### Files to Delete (Core)
- [ ] `dokuwiki/inc/Feed/RFCFeedItemProcessor.php`

### Files to Update (Documentation)
- [ ] `README.rst` - Remove RFC feed commit from cherry-pick list
- [ ] `DESCRIPTION.md` - Note plugin-based implementation

## Estimated Effort
- Plugin creation: 2-3 hours
- Testing: 1-2 hours
- Documentation: 1 hour
- **Total: 4-6 hours**

## Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Plugin event handling differs from direct implementation | Medium | Thorough testing of all feed modes |
| Event priorities conflict with other plugins | Low | Use appropriate event priorities |
| Autoloading issues with moved RFCFeedItemProcessor | Medium | Follow DokuWiki plugin autoloading conventions |
| Configuration not loaded properly | Low | Use `$this->getConf()` helper from ActionPlugin |

## Success Criteria
- ✅ All RSS feed modes work identically to current implementation
- ✅ No core DokuWiki files are modified
- ✅ Plugin can survive DokuWiki upgrades
- ✅ No cherry-pick commands needed in README.rst
- ✅ Clean git history with revert commit
- ✅ Documentation updated to reflect plugin approach

## Next Steps
1. Review and approve this plan
2. Create feature branch for implementation
3. Implement plugin (Phase 1-2)
4. Test thoroughly (Phase 3)
5. Revert core changes and verify
6. Update documentation
7. Deploy to production
