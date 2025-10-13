# Suppress Referee Field Implementation

## Overview
Added a new conditional "Suppress Referee" field for Matches that appears only when the tournament's JSON data has `showReferees` set to `true`.

## Implementation Details

### 1. Admin Meta Boxes (`class-mtp-matches-admin-meta-boxes.php`)
- **Added conditional checkbox field** after the "Suppress Group" field in the Display Options section
- Field name: `mtp_sr`
- Label: "Suppress Referee"
- Description: "Enable suppression of referee information in the matches table."
- **Conditional display**: Only shown when `showReferees` is `true` in tournament JSON

#### Changes Made:
- Added `sr` to default meta values with default value `'0'`
- Added conditional checkbox field rendering
- Updated JavaScript `checkConditionalFields()` function to check `showReferees`
- Added AJAX call to fetch and check `showReferees` value
- Updated preview function to include `sr` parameter
- Added `sr` to shortcode generation
- Added `sr` to JavaScript `updateShortcode()` function
- Added `sr` to save_meta_boxes field list

### 2. AJAX Handler (`class-mtp-matches-ajax-handler.php`)
- Added `sr` parameter handling in `ajax_preview_matches()` method
- Added `sr` to sanitize_ajax_data() method with default value `'0'`

### 3. Matches Renderer (`class-mtp-matches-renderer.php`)
- Added `sr` parameter check and processing
- Added `sr` to `no_value_params` array for proper URL building

### 4. Shortcode Handler (`class-mtp-matches-shortcode.php`)
- Added `sr` attribute with default value `'0'`

## JavaScript Conditional Logic
The `checkConditionalFields()` function now:
- Checks the tournament JSON for `showReferees` property via AJAX
- Shows/hides the `#mtp_sr_row` element based on the `showReferees` value
- Hides the field by default if no tournament ID is present

## Parameter Flow
When the "Suppress Referee" checkbox is checked, the system adds `sr` parameter to:
- **Preview iframe**: Included in `build_preview_attributes()` method
- **Generated shortcode**: Added in `generate_shortcode()` method  
- **Frontend shortcode**: Processed in shortcode attributes with default value `'0'`
- **iframe URL**: Added as parameter without value (e.g., `&sr`)

## Usage

### In Admin
1. Create/edit a Match List post
2. Enter a Tournament ID
3. If the tournament has `showReferees: true` in its JSON data, the "Suppress Referee" checkbox will appear
4. Check the box to suppress referee information in the matches display

### In Shortcode
```
[mtp-matches id="1752429520" sr="1" ...]
```

### In iframe URL
The parameter is passed as:
```
https://www.meinturnierplan.de/displayMatches.php?id=1752429520&sr&...
```

## Files Modified
1. `/includes/class-mtp-matches-admin-meta-boxes.php`
2. `/includes/class-mtp-matches-ajax-handler.php`
3. `/includes/class-mtp-matches-renderer.php`
4. `/includes/class-mtp-matches-shortcode.php`

## Testing
To test the implementation:
1. Use tournament ID `1752429520` which has `showReferees: false` - field should NOT appear
2. Find a tournament that has `showReferees: true` in its JSON response - field should appear
3. Check the checkbox and verify the preview updates
4. Verify the generated shortcode includes `sr="1"`
5. Test the shortcode on a page to ensure referees are suppressed

## Date Implemented
October 13, 2025
