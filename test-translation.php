<?php
/**
 * Temporary file to test translations
 * Add this code to the end of your functions.php file or run as a quick test
 * Remember to remove this file after testing
 */

// Test if we can load the translation domain manually
function test_meinturnierplan_translations() {
    // Force load the textdomain
    $loaded = load_plugin_textdomain(
        'meinturnierplan',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );

    echo "<h3>Translation Test Results:</h3>";
    echo "<p><strong>Textdomain loaded:</strong> " . ($loaded ? "YES" : "NO") . "</p>";
    echo "<p><strong>Current locale:</strong> " . get_locale() . "</p>";
    echo "<p><strong>WordPress language:</strong> " . get_bloginfo('language') . "</p>";

    // Test a specific translation
    $original = "-- Select Table --";
    $translated = __($original, 'meinturnierplan');
    echo "<p><strong>Original text:</strong> " . $original . "</p>";
    echo "<p><strong>Translated text:</strong> " . $translated . "</p>";
    echo "<p><strong>Translation working:</strong> " . ($original !== $translated ? "YES" : "NO") . "</p>";

    // Check if MO file exists and is readable
    $mo_file = plugin_dir_path(__FILE__) . 'languages/meinturnierplan-' . get_locale() . '.mo';
    echo "<p><strong>MO file path:</strong> " . $mo_file . "</p>";
    echo "<p><strong>MO file exists:</strong> " . (file_exists($mo_file) ? "YES" : "NO") . "</p>";
    echo "<p><strong>MO file readable:</strong> " . (is_readable($mo_file) ? "YES" : "NO") . "</p>";

    // Also check for just 'de' locale
    $mo_file_de = plugin_dir_path(__FILE__) . 'languages/meinturnierplan-de.mo';
    echo "<p><strong>DE MO file exists:</strong> " . (file_exists($mo_file_de) ? "YES" : "NO") . "</p>";
}

// Uncomment the line below to run the test (add to functions.php temporarily)
// add_action('admin_notices', 'test_meinturnierplan_translations');
