# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2024-09-18

### Added
- **Universal pathauto support**: Module now works with ALL pathauto aliases, not just `/about`
- Support for multi-segment paths like `/podcast/episode-name/edit`
- Comprehensive path filtering to exclude admin and system paths
- Improved error handling and redirect loop prevention

### Changed
- Switched from direct routing to event subscriber approach for better compatibility
- Enhanced path validation to prevent conflicts with Drupal core routes
- Updated documentation to reflect universal pathauto alias support

### Fixed
- Resolved 404 errors when accessing pathauto edit URLs
- Fixed redirect loops that could occur with certain path configurations
- Improved route priority handling to prevent conflicts

## [1.0.0] - 2024-09-18

### Added
- Initial release of Drupal Pathauto Edit Links module
- Direct serving of node edit forms at pathauto alias URLs (e.g., `/about/edit`)
- Direct serving of node delete forms at pathauto alias URLs (e.g., `/about/delete`)
- Redirect handling for node revisions at pathauto alias URLs (e.g., `/about/revisions`)
- Multiple hooks to alter edit links throughout Drupal UI
- Event subscriber for URL handling
- Controller for direct form serving
- Comprehensive documentation and troubleshooting guide

### Features
- No redirects for edit/delete operations - forms served directly at clean URLs
- Preserves all existing Drupal access controls and permissions
- Compatible with Drupal 10 and 11
- Works with existing pathauto module
- Handles edge cases and provides proper error handling

### Technical Implementation
- `hook_entity_operation_alter()` - Alters edit links in entity operation lists
- `hook_preprocess_links()` - Alters edit links in rendered link lists
- `hook_node_links_alter()` - Alters edit links in node link contexts
- `hook_menu_local_tasks_alter()` - Alters local task tabs
- Custom routing for direct form serving
- Event subscriber for URL processing
