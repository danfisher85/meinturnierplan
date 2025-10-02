<?php
/**
 * Gutenberg Block Handler Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Gutenberg Block Handler Class
 */
class MTP_Gutenberg_Block {
  
  /**
   * Table renderer instance
   */
  private $table_renderer;
  
  /**
   * Constructor
   */
  public function __construct($table_renderer) {
    $this->table_renderer = $table_renderer;
    $this->init();
  }
  
  /**
   * Initialize Gutenberg block
   */
  public function init() {
    add_action('init', array($this, 'register_block'));
    add_action('wp_ajax_mtp_get_tables', array($this, 'get_tables_ajax'));
    add_action('wp_ajax_nopriv_mtp_get_tables', array($this, 'get_tables_ajax'));
  }
  
  /**
   * Register the Gutenberg block
   */
  public function register_block() {
    // Only register if Gutenberg is available
    if (!function_exists('register_block_type')) {
      return;
    }
    
    wp_register_script(
      'mtp-tournament-table-block',
      MTP_PLUGIN_URL . 'assets/js/tournament-table-block.js',
      array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-data', 'wp-api-fetch'),
      MTP_PLUGIN_VERSION,
      true
    );
    
    wp_localize_script('mtp-tournament-table-block', 'mtpBlock', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('mtp_block_nonce')
    ));
    
    register_block_type(MTP_PLUGIN_PATH . 'blocks/tournament-table/block.json', array(
      'render_callback' => array($this, 'render_block')
    ));
  }
  
  /**
   * Render the block on the frontend
   */
  public function render_block($attributes) {
    $table_id = isset($attributes['tableId']) ? $attributes['tableId'] : '';
    
    if (empty($table_id)) {
      return '<div class="mtp-block-placeholder">' . __('Please select a Tournament Table.', 'meinturnierplan-wp') . '</div>';
    }
    
    // Get saved width and height from post meta
    $width = get_post_meta($table_id, '_mtp_width', true);
    $height = get_post_meta($table_id, '_mtp_height', true);
    
    // Prepare shortcode attributes
    $shortcode_atts = array('post_id' => $table_id);
    
    // Add width and height if they exist
    if (!empty($width)) {
      $shortcode_atts['width'] = $width;
    }
    if (!empty($height)) {
      $shortcode_atts['height'] = $height;
    }
    
    // Use the existing shortcode functionality
    $shortcode = new MTP_Shortcode($this->table_renderer);
    return $shortcode->shortcode_callback($shortcode_atts);
  }
  
  /**
   * AJAX handler to get tournament tables
   */
  public function get_tables_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_block_nonce')) {
      wp_die(__('Security check failed', 'meinturnierplan-wp'));
    }
    
    $tables = get_posts(array(
      'post_type' => 'mtp_table',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    ));
    
    $options = array();
    $options[] = array(
      'value' => '',
      'label' => __('Select a Tournament Table', 'meinturnierplan-wp')
    );
    
    foreach ($tables as $table) {
      $options[] = array(
        'value' => $table->ID,
        'label' => $table->post_title
      );
    }
    
    wp_send_json_success($options);
  }
}
