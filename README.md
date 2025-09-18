# Drupal Pathauto Edit Links

This module enables node edit links to use pathauto aliases instead of the default `/node/ID/edit` format.

## Features

- Converts edit links from `/node/12/edit` to `/about/edit` (where `/about` is the pathauto alias)
- Works with entity operations, node links, and other link contexts
- Handles routing to properly display the node edit form when accessing pathauto edit URLs
- Maintains all existing permissions and access controls

## How it works

1. **Link Alteration**: The module uses several Drupal hooks to alter edit links:
   - `hook_entity_operation_alter()` - Alters edit links in entity operation lists
   - `hook_preprocess_links()` - Alters edit links in rendered link lists
   - `hook_node_links_alter()` - Alters edit links in node link contexts
   - `hook_menu_local_tasks_alter()` - Alters local task tabs (Edit, Delete, Revisions)
   - `hook_preprocess_menu_local_task()` - Ensures theme-level local task URLs use aliases

2. **Event-Based Redirects**: The module provides URL handling for pathauto aliases:
   - Intercepts: `/any-alias/edit`, `/any-alias/delete`, `/any-alias/revisions`
   - Event subscriber redirects to appropriate node URLs
   - Preserves all Drupal access control and permission checking
   - Works with all existing pathauto aliases automatically

## Usage

Once enabled, all node edit links will automatically use pathauto aliases when available.

For example:
- Before: `https://example.com/node/12/edit`
- After: `https://example.com/about/edit` (if the node has alias `/about`)

The module also handles other node operations directly:
- Edit: `/about/edit` serves the node edit form directly
- Delete: `/about/delete` serves the node delete form directly  
- Revisions: `/about/revisions` serves the node revisions page directly

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

- Uses redirects instead of serving forms directly at pathauto URLs (for compatibility)
- Local task tabs (Edit, Delete, Revisions) may still show the original `/node/ID/action` format in the HTML
- The `hook_preprocess_menu_local_task()` is currently disabled to prevent URL object conflicts
- Admin paths and system paths are excluded to prevent conflicts

## Current Implementation

The module now works with **all pathauto aliases**:
- `/about/edit` - Redirects to `/node/12/edit`
- `/podcast/episode-name/edit` - Redirects to `/node/XX/edit`
- `/any-alias/delete` - Redirects to `/node/XX/delete`
- `/any-alias/revisions` - Redirects to `/node/XX/revisions`

The module uses an event subscriber to intercept requests and redirect them to the appropriate node URLs while preserving all access controls.
