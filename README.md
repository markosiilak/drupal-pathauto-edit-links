# Drupal Pathauto Edit Links

This module enables node edit links to use pathauto aliases instead of the default `/node/ID/edit` format.

## Features

- Converts edit links from `/node/12/edit` to `/about/edit` (where `/about` is the pathauto alias)
- Works with entity operations, node links, and other link contexts
- Handles routing to properly display the node edit form when accessing pathauto edit URLs
- Maintains all existing permissions and access controls

## How it works

1. **Direct URL Handling**: The module intercepts pathauto edit URLs and serves forms directly:
   - Event subscriber intercepts requests to `/{alias}/edit`, `/{alias}/delete`, `/{alias}/revisions`
   - Internal sub-requests serve the appropriate node forms
   - Forms are served directly at clean pathauto URLs without redirects
   - Link alteration hooks are disabled to prevent conflicts with other modules

2. **Direct Form Serving**: The module provides direct URL handling for pathauto aliases:
   - Intercepts: `/any-alias/edit`, `/any-alias/delete`, `/any-alias/revisions`
   - Event subscriber serves forms directly using internal sub-requests
   - Preserves all Drupal access control and permission checking
   - Works with all existing pathauto aliases automatically
   - **No redirects** - forms are served directly at clean URLs

## Usage

Once enabled, all node edit links will automatically use pathauto aliases when available.

For example:
- Before: `https://example.com/node/12/edit`
- After: `https://example.com/about/edit` (if the node has alias `/about`)

The module handles node operations **directly at pathauto URLs**:
- Edit: `/about/edit` - Edit form served directly (no redirect!)
- Delete: `/about/delete` - Delete form served directly (no redirect!)
- Revisions: `/about/revisions` - Revisions page served directly (no redirect!)

**Note**: The module serves forms directly at clean pathauto URLs using internal sub-requests, maintaining the clean URL throughout the entire editing process.

## Requirements

- Drupal 10 or 11
- Pathauto module
- Node module
- Path Alias module

## Installation

### Option 1: Composer (Recommended)

```bash
composer require markosiilak/drupal-pathauto-edit-links
drush en pathauto_edit_links
drush cr
```

### Option 2: Manual Installation

1. Download the latest release from [GitHub](https://github.com/markosiilak/drupal-pathauto-edit-links/releases)
2. Extract to `web/modules/custom/pathauto_edit_links/`
3. Enable the module: `drush en pathauto_edit_links`
4. Clear cache: `drush cr`

### Option 3: Git Clone

```bash
cd web/modules/custom/
git clone https://github.com/markosiilak/drupal-pathauto-edit-links.git
drush en pathauto_edit_links
drush cr
```

## Configuration

No configuration is required. The module works automatically for all nodes with pathauto aliases.

## Technical Details

The module includes several safety checks:
- Only modifies URLs for nodes that have valid pathauto aliases
- Preserves all existing access controls and permissions
- Handles edge cases like empty aliases or root paths
- Uses proper Drupal interfaces for maximum compatibility

## Troubleshooting

If edit links aren't showing pathauto aliases:
1. Ensure the node has a pathauto alias configured
2. Clear the cache: `drush cr`
3. Check that the pathauto module is enabled and working

If pathauto edit URLs (like `/about/edit`) aren't working when logged in:
1. Clear all caches: `drush cr`
2. Verify you have permission to edit the node
3. Check that the redirect is working: the URL should redirect from `/about/edit` to `/node/12/edit`
4. If you see a redirect loop, the event subscriber may need adjustment
5. Test with a direct link: `drush user:login --name=USERNAME` to get a fresh login link

The module logs no errors and works transparently with existing Drupal functionality.

## Known Limitations

- **Link alteration hooks are disabled** to prevent conflicts with webform and other modules
- Local task tabs and edit links in the UI will show the original `/node/ID/action` format
- Only the direct pathauto URLs work (e.g., typing `/about/edit` in browser)
- Admin paths, system paths, and webform paths are excluded to prevent conflicts
- Form submissions may require additional handling for complex field types

## Current Focus

The module prioritizes **clean pathauto edit URLs** over UI link modifications to ensure:
- ✅ No conflicts with other modules (webform, etc.)
- ✅ No PHP deprecation warnings
- ✅ Direct form serving at pathauto URLs
- ✅ Stable, error-free operation

## Current Implementation

The module now works with **all pathauto aliases** serving forms directly:

### ✅ **Working Examples:**
- `/about/edit` - Edit form served directly at this URL ✅
- `/podcast/test-podcast/edit` - Edit form served directly at this URL ✅
- `/any-alias/delete` - Delete form served directly at this URL ✅
- `/any-alias/revisions` - Revisions page served directly at this URL ✅

### 🎯 **How It Works:**
1. User accesses clean pathauto URL (e.g., `/some-path/edit`)
2. Module resolves alias to node ID internally
3. Creates internal sub-request to maintain full page layout and admin theme
4. **Serves edit form directly at the pathauto URL** (no redirect!)
5. Replaces internal node URLs with pathauto URLs in the response
6. User sees properly formatted edit form with clean URL maintained throughout
7. Form submissions work properly at the same clean URL
8. All permissions and access controls preserved

The module uses an event subscriber to intercept requests and serve the appropriate node forms directly at the pathauto URLs while preserving all access controls.
