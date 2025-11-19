<?php
/**
 * Table Renderer Class
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Table Renderer Class
 */
class MTP_Table_Renderer {

  /**
   * Constructor
   */
  public function __construct() {
    // Constructor can be used for any initialization if needed
  }

  /**
   * Render table HTML
   */
  public function render_table_html($table_id, $atts = array()) {
    // Get tournament ID from attributes or post meta
    $tournament_id = '';
    if (!empty($atts['id'])) {
      $tournament_id = $atts['id'];
    } elseif (!empty($table_id)) {
      $tournament_id = get_post_meta($table_id, '_mtp_tournament_id', true);
    }

    // If no tournament ID, show empty static table
    if (empty($tournament_id)) {
      return $this->render_empty_table($atts);
    }

    // Fetch tournament data from API
    $tournament_data = $this->fetch_tournament_data($tournament_id);

    // If no data available, show error message
    if (empty($tournament_data)) {
      /* translators: %s is the tournament ID */
      return $this->render_error_message(sprintf(__('Unable to fetch tournament data for ID: %s', 'meinturnierplan'), $tournament_id));
    }

    // Get group filter if specified
    $group_filter = '';
    if (!empty($atts['group'])) {
      $group_filter = $atts['group'];
    } elseif ($table_id) {
      $group_filter = get_post_meta($table_id, '_mtp_group', true);
    }

    // Render the tournament table
    return $this->render_tournament_table($tournament_data, $group_filter, $atts);
  }

  /**
   * Build URL parameters for the iframe
   */
  private function build_url_params($tournament_id, $table_id, $atts) {
    $params = array();
    $params['id'] = $tournament_id;

    // Get styling parameters
    $styling_params = $this->get_styling_parameters($table_id, $atts);

    // Map shortcode styling parameters to URL parameters
    foreach ($styling_params as $key => $value) {
      if (!empty($value)) {
        $params['s[' . $key . ']'] = $value;
      }
    }

    // Add wrap=false parameter
    $params['s[wrap]'] = 'false';

    // Add sw parameter if suppress_wins is enabled
    $suppress_wins = '';
    if (isset($atts['sw'])) {
      $suppress_wins = $atts['sw'];
    } elseif ($table_id) {
      $suppress_wins = get_post_meta($table_id, '_mtp_suppress_wins', true);
    }

    if (!empty($suppress_wins) && $suppress_wins === '1') {
      $params['sw'] = '';
    }

    // Add sl parameter if suppress_logos is enabled
    $suppress_logos = '';
    if (isset($atts['sl'])) {
      $suppress_logos = $atts['sl'];
    } elseif ($table_id) {
      $suppress_logos = get_post_meta($table_id, '_mtp_suppress_logos', true);
    }

    if (!empty($suppress_logos) && $suppress_logos === '1') {
      $params['sl'] = '';
    }

    // Add sn parameter if suppress_num_matches is enabled
    $suppress_num_matches = '';
    if (isset($atts['sn'])) {
      $suppress_num_matches = $atts['sn'];
    } elseif ($table_id) {
      $suppress_num_matches = get_post_meta($table_id, '_mtp_suppress_num_matches', true);
    }

    if (!empty($suppress_num_matches) && $suppress_num_matches === '1') {
      $params['sn'] = '';
    }

    // Add bm parameter if projector_presentation is enabled
    $projector_presentation = '';
    if (isset($atts['bm'])) {
      $projector_presentation = $atts['bm'];
    } elseif ($table_id) {
      $projector_presentation = get_post_meta($table_id, '_mtp_projector_presentation', true);
    }

    if (!empty($projector_presentation) && $projector_presentation === '1') {
      $params['bm'] = '';
    }

    // Add nav parameter if navigation_for_groups is enabled
    $navigation_for_groups = '';
    if (isset($atts['nav'])) {
      $navigation_for_groups = $atts['nav'];
    } elseif ($table_id) {
      $navigation_for_groups = get_post_meta($table_id, '_mtp_navigation_for_groups', true);
    }

    if (!empty($navigation_for_groups) && $navigation_for_groups === '1') {
      $params['nav'] = '';
    }

    // Add setlang parameter if language is specified
    $language = '';
    if (!empty($atts['setlang'])) {
      $language = $atts['setlang'];
    } elseif ($table_id) {
      $language = get_post_meta($table_id, '_mtp_language', true);
    }

    if (!empty($language) && $language !== 'en') {
      $params['setlang'] = $language;
    }

    // Add gr parameter if group is specified
    $group = '';
    if (!empty($atts['group'])) {
      $group = $atts['group'];
    } elseif ($table_id) {
      $group = get_post_meta($table_id, '_mtp_group', true);
    }

    if (!empty($group)) {
      $params['gr'] = $group;
    }

    return $params;
  }

  /**
   * Get styling parameters from post meta or attributes
   */
  private function get_styling_parameters($table_id, $atts) {
    $params = array();

    // Define parameter mapping and defaults
    $param_mapping = array(
      'size' => array('attr' => 's-size', 'meta' => '_mtp_font_size', 'default' => '9'),
      'sizeheader' => array('attr' => 's-sizeheader', 'meta' => '_mtp_header_font_size', 'default' => '10'),
      'color' => array('attr' => 's-color', 'meta' => '_mtp_text_color', 'default' => '000000'),
      'maincolor' => array('attr' => 's-maincolor', 'meta' => '_mtp_main_color', 'default' => '173f75'),
      'padding' => array('attr' => 's-padding', 'meta' => '_mtp_table_padding', 'default' => '2'),
      'innerpadding' => array('attr' => 's-innerpadding', 'meta' => '_mtp_inner_padding', 'default' => '5'),
      'bgcolor' => array('attr' => 's-bgcolor', 'meta' => '_mtp_bg_color', 'default' => '00000000'),
      'logosize' => array('attr' => 's-logosize', 'meta' => '_mtp_logo_size', 'default' => '20'),
      'bcolor' => array('attr' => 's-bcolor', 'meta' => '_mtp_border_color', 'default' => 'bbbbbb'),
      'bsizeh' => array('attr' => 's-bsizeh', 'meta' => '_mtp_bsizeh', 'default' => '1'),
      'bsizev' => array('attr' => 's-bsizev', 'meta' => '_mtp_bsizev', 'default' => '1'),
      'bsizeoh' => array('attr' => 's-bsizeoh', 'meta' => '_mtp_bsizeoh', 'default' => '1'),
      'bsizeov' => array('attr' => 's-bsizeov', 'meta' => '_mtp_bsizeov', 'default' => '1'),
      'bbcolor' => array('attr' => 's-bbcolor', 'meta' => '_mtp_head_bottom_border_color', 'default' => 'bbbbbb'),
      'bbsize' => array('attr' => 's-bbsize', 'meta' => '_mtp_bbsize', 'default' => '2'),
      'bgeven' => array('attr' => 's-bgeven', 'meta' => '_mtp_even_bg_color', 'default' => 'f0f8ffb0'),
      'bgodd' => array('attr' => 's-bgodd', 'meta' => '_mtp_odd_bg_color', 'default' => 'ffffffb0'),
      'bgover' => array('attr' => 's-bgover', 'meta' => '_mtp_hover_bg_color', 'default' => 'eeeeffb0'),
      'bghead' => array('attr' => 's-bghead', 'meta' => '_mtp_head_bg_color', 'default' => 'eeeeffff'),
    );

    foreach ($param_mapping as $url_param => $config) {
      $value = '';

      // Check if value is provided in shortcode attributes
      if (!empty($atts[$config['attr']])) {
        $value = $atts[$config['attr']];
      } elseif ($table_id) {
        // Get from post meta
        $meta_value = get_post_meta($table_id, $config['meta'], true);
        if (!empty($meta_value)) {
          $value = $meta_value;
        }

        // Handle special cases for colors with opacity
        if (in_array($url_param, array('bgcolor', 'bgeven', 'bgodd', 'bgover', 'bghead'))) {
          $value = MTP_Admin_Utilities::get_bg_color_with_opacity($table_id, $config['meta']);
        }
      }

      // Use default if no value found
      if (empty($value)) {
        $value = $config['default'];
      }

      $params[$url_param] = $value;
    }

    return $params;
  }

  /**
   * Render empty static table when no tournament ID is provided
   */
  private function render_empty_table($atts = array()) {
    // Simple placeholder message with auto-sizing
    $html = '<div class="mtp-empty-preview">';
    $html .= '<svg class="mtp-empty-preview__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" /></svg>';
    $html .= '<strong>' . __('Tournament Table Preview', 'meinturnierplan') . '</strong>';
    $html .= __('Enter a Tournament ID above to display live tournament data.', 'meinturnierplan');
    $html .= '</div>';

    return $html;
  }

  /**
   * Fetch tournament data from external API
   *
   * @param string $tournament_id The tournament ID
   * @return array|null Tournament data or null on failure
   */
  private function fetch_tournament_data($tournament_id) {
    if (empty($tournament_id)) {
      return null;
    }

    $cache_key = 'mtp_tournament_data_' . $tournament_id;
    $cache_expiry = 15 * MINUTE_IN_SECONDS; // Cache for 15 minutes

    // Try to get cached data first
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false && is_array($cached_data) && !empty($cached_data)) {
      // Validate cached data has proper structure
      if (isset($cached_data['teams']) || isset($cached_data['groups']) || isset($cached_data['groupRankTables']) || isset($cached_data['rankTable'])) {
        return $cached_data;
      }
      // Invalid cache structure, delete it
      delete_transient($cache_key);
    }

    // Fetch fresh data from API
    $url = 'https://tournej.com/json/json.php?id=' . urlencode($tournament_id);
    $response = wp_remote_get($url, array(
      'timeout' => 10,
      'sslverify' => true
    ));

    // Check for errors
    if (is_wp_error($response)) {
      return null;
    }

    $body = wp_remote_retrieve_body($response);
    
    // Check if body is empty
    if (empty($body)) {
      return null;
    }

    $data = json_decode($body, true);

    // Validate data structure - must have at least one of these keys
    if (!is_array($data) || empty($data)) {
      return null;
    }

    // Validate that we have meaningful data
    if (!isset($data['teams']) && !isset($data['groups']) && !isset($data['groupRankTables']) && !isset($data['rankTable'])) {
      return null;
    }

    // Cache the result
    set_transient($cache_key, $data, $cache_expiry);

    return $data;
  }

  /**
   * Render tournament table from fetched data
   *
   * @param array $tournament_data Tournament data from API
   * @param string $group_filter Group number to filter (empty for all groups or final round)
   * @param array $atts Shortcode attributes
   * @return string HTML output
   */
  private function render_tournament_table($tournament_data, $group_filter = '', $atts = array()) {
    // Handle group filtering
    $table_data = null;
    
    // Check if group_filter is '90' (final round)
    if ($group_filter === '90') {
      // Display final ranking table
      if (!empty($tournament_data['finalRankTable']) && is_array($tournament_data['finalRankTable'])) {
        return $this->render_final_ranking_table($tournament_data, $atts);
      } else {
        return $this->render_error_message(__('Final round data not available.', 'meinturnierplan'));
      }
    } elseif (!empty($group_filter) && !empty($tournament_data['groupRankTables'])) {
      // Display specific group
      $group_index = intval($group_filter) - 1;
      if (isset($tournament_data['groupRankTables'][$group_index])) {
        $group_info = isset($tournament_data['groups'][$group_index]) ? $tournament_data['groups'][$group_index] : null;
        return $this->render_group_table($tournament_data['groupRankTables'][$group_index], $tournament_data, $atts, $group_info);
      } else {
        return $this->render_error_message(__('Selected group not found.', 'meinturnierplan'));
      }
    } elseif (!empty($tournament_data['groupRankTables']) && is_array($tournament_data['groupRankTables'])) {
      // Display first group by default
      $group_info = isset($tournament_data['groups'][0]) ? $tournament_data['groups'][0] : null;
      return $this->render_group_table($tournament_data['groupRankTables'][0], $tournament_data, $atts, $group_info);
    } elseif (!empty($tournament_data['rankTable']) && is_array($tournament_data['rankTable'])) {
      // Tournament without groups - display main ranking table
      return $this->render_single_ranking_table($tournament_data, $atts);
    }

    return $this->render_error_message(__('No tournament data available.', 'meinturnierplan'));
  }

  /**
   * Render group table
   *
   * @param array $rank_table Array of ranking entries for the group
   * @param array $tournament_data Full tournament data
   * @param array $atts Shortcode attributes
   * @param array|null $group_info Group info with displayId
   * @return string HTML output
   */
  private function render_group_table($rank_table, $tournament_data, $atts = array(), $group_info = null) {
    if (empty($rank_table) || !is_array($rank_table)) {
      return $this->render_error_message(__('No ranking data available for this group.', 'meinturnierplan'));
    }

    // Build teams lookup array - support both displayId and index-based lookup
    $teams = array();
    $teams_by_index = array();
    if (!empty($tournament_data['teams']) && is_array($tournament_data['teams'])) {
      foreach ($tournament_data['teams'] as $idx => $team) {
        // Store by displayId for direct lookup
        if (isset($team['displayId'])) {
          $teams[$team['displayId']] = $team;
        }
        // Also store by array index for tournaments where teamId = array index
        $teams_by_index[$idx] = $team;
      }
    }

    // Start building HTML
    $html = '<div id="widgetBox">';
    $html .= '<table class="width100 centered" name="RankTable">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th title="' . esc_attr__('Rank in Group', 'meinturnierplan') . '">' . esc_html__('Pl', 'meinturnierplan') . '</th>';
    $html .= '<th>' . esc_html__('Participant', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Matches', 'meinturnierplan') . '">' . esc_html__('M', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Wins', 'meinturnierplan') . '">' . esc_html__('W', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Draws', 'meinturnierplan') . '">' . esc_html__('D', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Loss', 'meinturnierplan') . '">' . esc_html__('L', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Goals', 'meinturnierplan') . '">' . esc_html__('G', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Goal Difference', 'meinturnierplan') . '">' . esc_html__('GD', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Points', 'meinturnierplan') . '">' . esc_html__('Pts', 'meinturnierplan') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    // Render each team row
    foreach ($rank_table as $index => $rank_entry) {
      $team_id = isset($rank_entry['teamId']) ? $rank_entry['teamId'] : '';
      
      // Try to find team by displayId first (string match)
      $team = isset($teams[strval($team_id)]) ? $teams[strval($team_id)] : array();
      
      // If not found, try by array index (for tournaments where teamId is 0-based index)
      if (empty($team) && isset($teams_by_index[$team_id])) {
        $team = $teams_by_index[$team_id];
      }
      
      $team_name = isset($team['name']) ? $team['name'] : __('Unknown Team', 'meinturnierplan');
      
      $rank = isset($rank_entry['rank']) ? $rank_entry['rank'] : ($index + 1);
      $matches = isset($rank_entry['numMatches']) ? $rank_entry['numMatches'] : 0;
      $wins = isset($rank_entry['numWins']) ? $rank_entry['numWins'] : 0;
      $draws = isset($rank_entry['numDraws']) ? $rank_entry['numDraws'] : 0;
      $losses = isset($rank_entry['numLosts']) ? $rank_entry['numLosts'] : 0;
      $goals_for = isset($rank_entry['ownGoals']) ? $rank_entry['ownGoals'] : 0;
      $goals_against = isset($rank_entry['otherGoals']) ? $rank_entry['otherGoals'] : 0;
      $goal_diff = isset($rank_entry['goalDiff']) ? $rank_entry['goalDiff'] : 0;
      $points = isset($rank_entry['points']) ? $rank_entry['points'] : 0;

      // Get team logo
      $logo_url = '';
      if (isset($team['logo']['lx32'])) {
        $logo_url = $team['logo']['lx32'];
      } elseif (isset($team['logo']['lx32w'])) {
        $logo_url = $team['logo']['lx32w'];
      }

      $html .= '<tr>';
      $html .= '<td class="tdRank">' . esc_html($rank) . '</td>';
      
      // Team name with logo
      $html .= '<td class="tdRankTeamName">';
      $html .= '<div class="rankicons">';
      if (!empty($logo_url)) {
        $html .= '<div class="icon"><img alt="Logo" src="' . esc_url($logo_url) . '"></div>';
      }
      $html .= '<div class="iconAside">' . esc_html($team_name) . '</div>';
      $html .= '</div>';
      $html .= '</td>';
      
      $html .= '<td class="tdNumGames">' . esc_html($matches) . '</td>';
      $html .= '<td class="tdNumWins">' . esc_html($wins) . '</td>';
      $html .= '<td class="tdNumDraws">' . esc_html($draws) . '</td>';
      $html .= '<td class="tdNumLosts">' . esc_html($losses) . '</td>';
      $html .= '<td class="tdGoals">' . esc_html($goals_for . ':' . $goals_against) . '</td>';
      $html .= '<td class="tdGoalDiff">' . esc_html($goal_diff) . '</td>';
      $html .= '<td class="tdPoints">' . esc_html($points) . '</td>';
      $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    
    // Add tournament link if we have tournament ID
    if (!empty($atts['id'])) {
      $html .= '<font class="small"><a target="_blank" href="https://www.meinturnierplan.de/showit.php?id=' . esc_attr($atts['id']) . '">' . esc_html__('Show Full Tournament', 'meinturnierplan') . '</a></font>';
    }
    
    $html .= '</div>';

    return $html;
  }

  /**
   * Render single ranking table (for tournaments without groups)
   *
   * @param array $tournament_data Full tournament data
   * @param array $atts Shortcode attributes
   * @return string HTML output
   */
  private function render_single_ranking_table($tournament_data, $atts = array()) {
    if (empty($tournament_data['rankTable']) || !is_array($tournament_data['rankTable'])) {
      return $this->render_error_message(__('No ranking data available.', 'meinturnierplan'));
    }

    // Build teams lookup array - support both displayId and index-based lookup
    $teams = array();
    $teams_by_index = array();
    if (!empty($tournament_data['teams']) && is_array($tournament_data['teams'])) {
      foreach ($tournament_data['teams'] as $idx => $team) {
        // Store by displayId for direct lookup
        if (isset($team['displayId'])) {
          $teams[$team['displayId']] = $team;
        }
        // Also store by array index for tournaments where teamId = array index
        $teams_by_index[$idx] = $team;
      }
    }

    // Start building HTML
    $html = '<div id="widgetBox">';
    $html .= '<table class="width100 centered" name="RankTable">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th title="' . esc_attr__('Rank in Group', 'meinturnierplan') . '">' . esc_html__('Pl', 'meinturnierplan') . '</th>';
    $html .= '<th>' . esc_html__('Participant', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Matches', 'meinturnierplan') . '">' . esc_html__('M', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Wins', 'meinturnierplan') . '">' . esc_html__('W', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Draws', 'meinturnierplan') . '">' . esc_html__('D', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Loss', 'meinturnierplan') . '">' . esc_html__('L', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Goals', 'meinturnierplan') . '">' . esc_html__('G', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Goal Difference', 'meinturnierplan') . '">' . esc_html__('GD', 'meinturnierplan') . '</th>';
    $html .= '<th title="' . esc_attr__('Points', 'meinturnierplan') . '">' . esc_html__('Pts', 'meinturnierplan') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    // Render each team row
    foreach ($tournament_data['rankTable'] as $index => $rank_entry) {
      $team_id = isset($rank_entry['teamId']) ? $rank_entry['teamId'] : '';
      
      // Try to find team by displayId first (string match)
      $team = isset($teams[strval($team_id)]) ? $teams[strval($team_id)] : array();
      
      // If not found, try by array index (for tournaments where teamId is 0-based index)
      if (empty($team) && isset($teams_by_index[$team_id])) {
        $team = $teams_by_index[$team_id];
      }
      
      $team_name = isset($team['name']) ? $team['name'] : __('Unknown Team', 'meinturnierplan');
      
      $rank = isset($rank_entry['rank']) ? $rank_entry['rank'] : ($index + 1);
      $matches = isset($rank_entry['numMatches']) ? $rank_entry['numMatches'] : 0;
      $wins = isset($rank_entry['numWins']) ? $rank_entry['numWins'] : 0;
      $draws = isset($rank_entry['numDraws']) ? $rank_entry['numDraws'] : 0;
      $losses = isset($rank_entry['numLosts']) ? $rank_entry['numLosts'] : 0;
      $goals_for = isset($rank_entry['ownGoals']) ? $rank_entry['ownGoals'] : 0;
      $goals_against = isset($rank_entry['otherGoals']) ? $rank_entry['otherGoals'] : 0;
      $goal_diff = isset($rank_entry['goalDiff']) ? $rank_entry['goalDiff'] : 0;
      $points = isset($rank_entry['points']) ? $rank_entry['points'] : 0;

      // Get team logo
      $logo_url = '';
      if (isset($team['logo']['lx32'])) {
        $logo_url = $team['logo']['lx32'];
      } elseif (isset($team['logo']['lx32w'])) {
        $logo_url = $team['logo']['lx32w'];
      }

      $html .= '<tr>';
      $html .= '<td class="tdRank">' . esc_html($rank) . '</td>';
      
      // Team name with logo
      $html .= '<td class="tdRankTeamName">';
      $html .= '<div class="rankicons">';
      if (!empty($logo_url)) {
        $html .= '<div class="icon"><img alt="Logo" src="' . esc_url($logo_url) . '"></div>';
      }
      $html .= '<div class="iconAside">' . esc_html($team_name) . '</div>';
      $html .= '</div>';
      $html .= '</td>';
      
      $html .= '<td class="tdNumGames">' . esc_html($matches) . '</td>';
      $html .= '<td class="tdNumWins">' . esc_html($wins) . '</td>';
      $html .= '<td class="tdNumDraws">' . esc_html($draws) . '</td>';
      $html .= '<td class="tdNumLosts">' . esc_html($losses) . '</td>';
      $html .= '<td class="tdGoals">' . esc_html($goals_for . ':' . $goals_against) . '</td>';
      $html .= '<td class="tdGoalDiff">' . esc_html($goal_diff) . '</td>';
      $html .= '<td class="tdPoints">' . esc_html($points) . '</td>';
      $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    
    // Add tournament link if we have tournament ID
    if (!empty($atts['id'])) {
      $html .= '<font class="small"><a target="_blank" href="https://www.meinturnierplan.de/showit.php?id=' . esc_attr($atts['id']) . '">' . esc_html__('Show Full Tournament', 'meinturnierplan') . '</a></font>';
    }
    
    $html .= '</div>';

    return $html;
  }

  /**
   * Render final ranking table
   *
   * @param array $tournament_data Full tournament data
   * @param array $atts Shortcode attributes
   * @return string HTML output
   */
  private function render_final_ranking_table($tournament_data, $atts = array()) {
    if (empty($tournament_data['finalRankTable']) || !is_array($tournament_data['finalRankTable'])) {
      return $this->render_error_message(__('No final ranking data available.', 'meinturnierplan'));
    }

    // Build teams lookup array - support both displayId and index-based lookup
    $teams = array();
    $teams_by_index = array();
    if (!empty($tournament_data['teams']) && is_array($tournament_data['teams'])) {
      foreach ($tournament_data['teams'] as $idx => $team) {
        // Store by displayId for direct lookup
        if (isset($team['displayId'])) {
          $teams[$team['displayId']] = $team;
        }
        // Also store by array index for tournaments where teamId = array index
        $teams_by_index[$idx] = $team;
      }
    }

    // Start building HTML
    $html = '<div id="widgetBox">';
    $html .= '<table class="width100 centered" name="RankTable">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th title="' . esc_attr__('Rank', 'meinturnierplan') . '">' . esc_html__('Pl', 'meinturnierplan') . '</th>';
    $html .= '<th>' . esc_html__('Participant', 'meinturnierplan') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    // Render each team row
    foreach ($tournament_data['finalRankTable'] as $index => $rank_entry) {
      $team_id = isset($rank_entry['teamId']) ? $rank_entry['teamId'] : '';
      
      // Try to find team by displayId first (string match)
      $team = isset($teams[strval($team_id)]) ? $teams[strval($team_id)] : array();
      
      // If not found, try by array index (for tournaments where teamId is 0-based index)
      if (empty($team) && isset($teams_by_index[$team_id])) {
        $team = $teams_by_index[$team_id];
      }
      
      $team_name = isset($team['name']) ? $team['name'] : __('Unknown Team', 'meinturnierplan');
      
      $rank = isset($rank_entry['rank']) ? $rank_entry['rank'] : ($index + 1);

      // Get team logo
      $logo_url = '';
      if (isset($team['logo']['lx32'])) {
        $logo_url = $team['logo']['lx32'];
      } elseif (isset($team['logo']['lx32w'])) {
        $logo_url = $team['logo']['lx32w'];
      }

      $html .= '<tr>';
      $html .= '<td class="tdRank">' . esc_html($rank) . '</td>';
      
      // Team name with logo
      $html .= '<td class="tdRankTeamName">';
      $html .= '<div class="rankicons">';
      if (!empty($logo_url)) {
        $html .= '<div class="icon"><img alt="Logo" src="' . esc_url($logo_url) . '"></div>';
      }
      $html .= '<div class="iconAside">' . esc_html($team_name) . '</div>';
      $html .= '</div>';
      $html .= '</td>';
      $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    
    // Add tournament link if we have tournament ID
    if (!empty($atts['id'])) {
      $html .= '<font class="small"><a target="_blank" href="https://www.meinturnierplan.de/showit.php?id=' . esc_attr($atts['id']) . '">' . esc_html__('Show Full Tournament', 'meinturnierplan') . '</a></font>';
    }
    
    $html .= '</div>';

    return $html;
  }

  /**
   * Render error message
   *
   * @param string $message Error message to display
   * @return string HTML output
   */
  private function render_error_message($message) {
    $html = '<div class="mtp-error-message">';
    $html .= '<p>' . esc_html($message) . '</p>';
    $html .= '</div>';
    return $html;
  }

  /**
   * Build custom query string to handle parameters without values
   */
  private function build_query_string($params) {
    $query_parts = array();

    // Parameters that should appear without values when enabled
    $no_value_params = array('bm', 'sn', 'sw', 'sl', 'nav');

    foreach ($params as $key => $value) {
      if (in_array($key, $no_value_params) && $value === '') {
        // Special case: just add parameter name without equals sign
        $query_parts[] = urlencode($key);
      } else {
        // Normal parameter with value
        $query_parts[] = urlencode($key) . '=' . urlencode($value);
      }
    }

    return implode('&', $query_parts);
  }
}
