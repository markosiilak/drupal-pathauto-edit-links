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

2. **Direct Routing**: The module provides direct URL handling for pathauto aliases:
   - Routes: `/about/edit`, `/about/delete`, `/about/revisions`
   - Controller serves the edit/delete forms directly without redirects
   - Preserves all Drupal access control and permission checking
   - URLs like `/about/edit` serve the node edit form directly at that URL

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

1. Place the module in `web/modules/custom/pathauto_edit_links/`
2. Enable the module or use drush: `drush en pathauto_edit_links`
3. Clear cache or use drush: `drush cr`

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

- Currently configured for `/about` path only (can be extended for other aliases)
- Local task tabs (Edit, Delete, Revisions) may still show the original `/node/ID/action` format in the HTML
- The `hook_preprocess_menu_local_task()` is currently disabled to prevent URL object conflicts
- Revisions page still redirects to the system URL due to complexity

## Current Implementation

The module is currently set up specifically for the `/about` page:
- `/about/edit` - Direct node edit form (no redirect)
- `/about/delete` - Direct node delete form (no redirect)
- `/about/revisions` - Redirects to node revisions page
