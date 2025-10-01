<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://github.com/danfisher85/meinturnierplan-wp
 * Description: A WordPress plugin to display tournament tables using custom post types, shortcodes, and widgets.
 * Version: 1.0.0
 * Author: Dan Fisher
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan-wp
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants
define('MTP_PLUGIN_FILE', __FILE__);
define('MTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MTP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MTP_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-plugin.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-post-type.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-table-renderer.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-shortcode.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-admin-meta-boxes.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-ajax-handler.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-assets.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-table-widget.php';

/**
 * Legacy Main Plugin Class (for backward compatibility)
 * This class is maintained for backward compatibility with existing code.
 * All functionality has been moved to specialized classes in the includes/ directory.
 */
class MeinTurnierplanWP {
  
  /**
   * Reference to the main plugin instance
   */
  private $plugin;
  
  /**
   * Constructor
   */
  public function __construct() {
    // Initialize the main plugin system
    $this->plugin = MTP_Plugin::instance();
    
    // Backward compatibility hooks
    add_action('init', array($this, 'init'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    add_action('save_post', array($this, 'save_meta_boxes'));
    add_action('wp_ajax_mtp_preview_table', array($this, 'ajax_preview_table'));
    
    // Activation and deactivation hooks
    register_activation_hook(__FILE__, array($this, 'activate'));
    register_deactivation_hook(__FILE__, array($this, 'deactivate'));
  }
  
  /**
   * Initialize the plugin (legacy compatibility)
   */
  public function init() {
    // The new plugin system handles initialization automatically
    // This method is kept for backward compatibility
  }
  
  /**
   * Register post type (delegated to MTP_Post_Type)
   */
  public function register_post_type() {
    if ($this->plugin && $this->plugin->post_type) {
      return $this->plugin->post_type->register_post_type();
    }
  }
  
  /**
   * Add meta boxes (delegated to MTP_Admin_Meta_Boxes)
   */
  public function add_meta_boxes() {
    if ($this->plugin && $this->plugin->admin_meta_boxes) {
      return $this->plugin->admin_meta_boxes->add_meta_boxes();
    }
  }
  
  /**
   * Meta box callback (delegated to MTP_Admin_Meta_Boxes)
   */
  public function meta_box_callback($post) {
    if ($this->plugin && $this->plugin->admin_meta_boxes) {
      return $this->plugin->admin_meta_boxes->meta_box_callback($post);
    }
  }
  
  /**
   * Shortcode meta box callback (delegated to MTP_Admin_Meta_Boxes)
   */
  public function shortcode_meta_box_callback($post) {
    if ($this->plugin && $this->plugin->admin_meta_boxes) {
      return $this->plugin->admin_meta_boxes->shortcode_meta_box_callback($post);
    }
  }
  
  /**
   * Save meta boxes (delegated to MTP_Admin_Meta_Boxes)
   */
  public function save_meta_boxes($post_id) {
    if ($this->plugin && $this->plugin->admin_meta_boxes) {
      return $this->plugin->admin_meta_boxes->save_meta_boxes($post_id);
    }
  }
  
  /**
   * Initialize shortcode (delegated to MTP_Shortcode)
   */
  public function init_shortcode() {
    if ($this->plugin && $this->plugin->shortcode) {
      return $this->plugin->shortcode->init_shortcode();
    }
  }
  
  /**
   * Shortcode callback (delegated to MTP_Shortcode)
   */
  public function shortcode_callback($atts) {
    if ($this->plugin && $this->plugin->shortcode) {
      return $this->plugin->shortcode->shortcode_callback($atts);
    }
    return '';
  }
  
  /**
   * Initialize widget (delegated to widget system)
   */
  public function init_widget() {
    // Widget registration is handled automatically by WordPress
  }
  
  /**
   * AJAX preview table (delegated to MTP_Ajax_Handler)
   */
  public function ajax_preview_table() {
    if ($this->plugin && $this->plugin->ajax_handler) {
      return $this->plugin->ajax_handler->ajax_preview_table();
    }
    wp_die('AJAX handler not available');
  }
  
  /**
   * Render table HTML (delegated to MTP_Table_Renderer)
   */
  public function render_table_html($table_id, $atts = array()) {
    if ($this->plugin && $this->plugin->table_renderer) {
      return $this->plugin->table_renderer->render_table_html($table_id, $atts);
    }
    return '';
  }
  
  /**
   * Enqueue styles (delegated to MTP_Assets)
   */
  public function enqueue_styles() {
    if ($this->plugin && $this->plugin->assets) {
      return $this->plugin->assets->enqueue_styles();
    }
  }
  
  /**
   * Enqueue admin scripts (delegated to MTP_Assets)
   */
  public function enqueue_admin_scripts($hook) {
    if ($this->plugin && $this->plugin->assets) {
      return $this->plugin->assets->enqueue_admin_scripts($hook);
    }
  }
  
  /**
   * Activate plugin (delegated to MTP_Installer)
   */
  public function activate() {
    if (class_exists('MTP_Installer')) {
      MTP_Installer::activate();
    }
  }
  
  /**
   * Deactivate plugin (delegated to MTP_Installer)
   */
  public function deactivate() {
    if (class_exists('MTP_Installer')) {
      MTP_Installer::deactivate();
    }
  }
}

// Initialize the plugin
new MeinTurnierplanWP();
