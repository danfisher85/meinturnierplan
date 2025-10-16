# Changelog

All notable changes to the MeinTurnierplan WordPress plugin will be documented in this file.

## [0.3.1] - 2025-10-16

### Added
- **Auto-populate Single CPT Pages**: New `MTP_Single_Content_Filter` class that automatically displays tournament tables and match lists on their single post pages
- Content filter hooks into WordPress's `the_content` filter
- Automatically retrieves all saved settings and renders appropriate content
- Works for both `mtp_table` and `mtp_match_list` custom post types

### Changed
- Updated plugin version to 0.3.1
- Enhanced README with documentation about automatic content display
- Updated file structure documentation

### Fixed
- Corrected meta key mappings from shortcode attributes to actual database field names (e.g., `_mtp_tournament_id` instead of `_mtp_external_id`)
- Fixed all styling and display option meta key references to match actual field names saved in database
- Added proper color-with-opacity conversion helper method for background colors

### Technical Details
- New file: `includes/class-mtp-single-content-filter.php`
- Uses `is_singular()`, `in_the_loop()`, and `is_main_query()` checks to ensure proper execution
- Retrieves external ID and all style/display settings from post meta
- Passes settings to appropriate renderer (table or matches)

### How It Works
When a user visits a single tournament table or match list page (e.g., `/tournament-table/my-tournament/`):

1. The content filter checks if we're on a single CPT page
2. Retrieves the external ID from post meta (`_mtp_external_id`)
3. Gathers all saved shortcode attributes from post meta
4. Calls the appropriate renderer with these settings
5. Prepends the rendered content to any existing post content

No manual shortcode insertion needed on single CPT pages!

## [0.3.0] - 2025-10-15

### Added
- Gutenberg block support for both tables and matches
- Enhanced admin interface with better organization
- Improved AJAX preview functionality

## [0.2.0] - 2025-10-10

### Added
- Matches custom post type (`mtp_match_list`)
- Matches Gutenberg block
- Matches widget
- Matches shortcode (`[mtp-matches]`)
- Extensive styling customization options
- AJAX-powered live preview
- Multi-language support

## [0.1.0] - 2025-10-01

### Added
- Initial release
- Tournament Tables custom post type (`mtp_table`)
- Basic shortcode (`[mtp-table]`) and widget support
- External API integration
- Customization options
