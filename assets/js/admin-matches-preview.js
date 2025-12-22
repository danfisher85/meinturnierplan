/**
 * Admin Matches Preview JavaScript
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 */

(function($) {
  'use strict';

  // Function to check tournament data and show/hide conditional fields
  function checkConditionalFields(tournamentId) {
    var config = window.mtpMatchesPreviewConfig || {};
    var nonce = config.checkOptionNonce || '';

    if (!tournamentId) {
      // Hide all conditional fields if no tournament ID
      $('#mtp_sf_row').hide();
      $('#mtp_sg_row').hide();
      $('#mtp_sr_row').hide();
      $('#mtp_se_row').hide();
      $('#mtp_sp_row').hide();
      $('#mtp_sh_row').hide();
      return;
    }

    // Fetch tournament data for showCourts via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtp_check_tournament_option',
        tournament_id: tournamentId,
        option_name: 'showCourts',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showCourtsValue = response.data.value;

          // Show/hide Suppress Court field based on showCourts
          if (showCourtsValue === true) {
            $('#mtp_sf_row').show();
          } else {
            $('#mtp_sf_row').hide();
          }
        } else {
          $('#mtp_sf_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtp_sf_row').hide();
      }
    });

    // Fetch tournament data for showGroups via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtp_check_tournament_option',
        tournament_id: tournamentId,
        option_name: 'showGroups',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showGroupsValue = response.data.value;

          // Show/hide Suppress Group field based on showGroups
          if (showGroupsValue === true) {
            $('#mtp_sg_row').show();
          } else {
            $('#mtp_sg_row').hide();
          }
        } else {
          $('#mtp_sg_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtp_sg_row').hide();
      }
    });

    // Fetch tournament data for showReferees via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtp_check_tournament_option',
        tournament_id: tournamentId,
        option_name: 'showReferees',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showRefereesValue = response.data.value;

          // Show/hide Suppress Referee field based on showReferees
          if (showRefereesValue === true) {
            $('#mtp_sr_row').show();
          } else {
            $('#mtp_sr_row').hide();
          }
        } else {
          $('#mtp_sr_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtp_sr_row').hide();
      }
    });

    // Fetch tournament data for finalMatches via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtp_check_tournament_option',
        tournament_id: tournamentId,
        option_name: 'finalMatches',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var finalMatchesValue = response.data.value;

          // Show Suppress Extra Time, Suppress Penalties, and Suppress Headlines fields if finalMatches exists
          if (finalMatchesValue !== null && finalMatchesValue !== undefined) {
            $('#mtp_se_row').show();
            $('#mtp_sp_row').show();
            $('#mtp_sh_row').show();
          } else {
            $('#mtp_se_row').hide();
            $('#mtp_sp_row').hide();
            $('#mtp_sh_row').hide();
          }
        } else {
          $('#mtp_se_row').hide();
          $('#mtp_sp_row').hide();
          $('#mtp_sh_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the fields
        $('#mtp_se_row').hide();
        $('#mtp_sp_row').hide();
        $('#mtp_sh_row').hide();
      }
    });
  }

  // Function to update preview
  function updatePreview() {
    var config = window.mtpMatchesPreviewConfig || {};
    var postId = config.postId || 0;
    var previewNonce = config.previewNonce || '';

    // Get all field values
    var data = {
      post_id: postId,
      tournament_id: $("#mtp_tournament_id").val(),
      font_size: $("#mtp_font_size").val(),
      header_font_size: $("#mtp_header_font_size").val(),
      bsizeh: $("#mtp_bsizeh").val(),
      bsizev: $("#mtp_bsizev").val(),
      bsizeoh: $("#mtp_bsizeoh").val(),
      bsizeov: $("#mtp_bsizeov").val(),
      bbsize: $("#mtp_bbsize").val(),
      ehrsize: $("#mtp_ehrsize").val(),
      ehrtop: $("#mtp_ehrtop").val(),
      ehrbottom: $("#mtp_ehrbottom").val(),
      table_padding: $("#mtp_table_padding").val(),
      inner_padding: $("#mtp_inner_padding").val(),
      text_color: $("#mtp_text_color").val().replace("#", ""),
      main_color: $("#mtp_main_color").val().replace("#", ""),
      bg_color: $("#mtp_bg_color").val().replace("#", ""),
      bg_opacity: $("#mtp_bg_opacity").val(),
      border_color: $("#mtp_border_color").val().replace("#", ""),
      head_bottom_border_color: $("#mtp_head_bottom_border_color").val().replace("#", ""),
      even_bg_color: $("#mtp_even_bg_color").val().replace("#", ""),
      even_bg_opacity: $("#mtp_even_bg_opacity").val(),
      odd_bg_color: $("#mtp_odd_bg_color").val().replace("#", ""),
      odd_bg_opacity: $("#mtp_odd_bg_opacity").val(),
      hover_bg_color: $("#mtp_hover_bg_color").val().replace("#", ""),
      hover_bg_opacity: $("#mtp_hover_bg_opacity").val(),
      head_bg_color: $("#mtp_head_bg_color").val().replace("#", ""),
      head_bg_opacity: $("#mtp_head_bg_opacity").val(),
      projector_presentation: $("#mtp_projector_presentation").is(":checked") ? "1" : "0",
      si: $("#mtp_si").is(":checked") ? "1" : "0",
      sf: ($("#mtp_sf").length && $("#mtp_sf").is(":visible") && $("#mtp_sf").is(":checked")) ? "1" : "0",
      st: $("#mtp_st").is(":checked") ? "1" : "0",
      sg: ($("#mtp_sg").length && $("#mtp_sg").is(":visible") && $("#mtp_sg").is(":checked")) ? "1" : "0",
      sr: ($("#mtp_sr").length && $("#mtp_sr").is(":visible") && $("#mtp_sr").is(":checked")) ? "1" : "0",
      se: ($("#mtp_se").length && $("#mtp_se").is(":visible") && $("#mtp_se").is(":checked")) ? "1" : "0",
      sp: ($("#mtp_sp").length && $("#mtp_sp").is(":visible") && $("#mtp_sp").is(":checked")) ? "1" : "0",
      sh: ($("#mtp_sh").length && $("#mtp_sh").is(":visible") && $("#mtp_sh").is(":checked")) ? "1" : "0",
      language: $("#mtp_language").val(),
      group: $("#mtp_group").val(),
      participant: $("#mtp_participant").val(),
      match_number: $("#mtp_match_number").val(),
      action: "mtp_preview_matches",
      nonce: previewNonce
    };

    // Convert opacity to hex and combine with colors
    data.bg_color = data.bg_color + Math.round((data.bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.even_bg_color = data.even_bg_color + Math.round((data.even_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.odd_bg_color = data.odd_bg_color + Math.round((data.odd_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.hover_bg_color = data.hover_bg_color + Math.round((data.hover_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.head_bg_color = data.head_bg_color + Math.round((data.head_bg_opacity / 100) * 255).toString(16).padStart(2, "0");

    $.post(ajaxurl, data, function(response) {
      if (response.success) {
        $("#mtp-preview").html(response.data);
      }
    });
  }

  // Initialize on document ready
  $(document).ready(function() {
    // Initialize reusable utilities with preview update callback
    MTPAdminUtils.initColorPickers(updatePreview);
    MTPAdminUtils.initOpacitySliders(updatePreview);
    MTPAdminUtils.initFormFieldListeners('mtp_', updatePreview);

    // Initialize tournament ID field with group and team loading
    MTPAdminUtils.initTournamentIdField('#mtp_tournament_id', updatePreview, function(tournamentId) {
      MTPAdminUtils.loadTournamentGroups(tournamentId, {context: 'matches'});
      MTPAdminUtils.loadTournamentTeams(tournamentId);
      // Check tournament data for conditional fields
      checkConditionalFields(tournamentId);
    });

    // Initialize group refresh button
    MTPAdminUtils.initGroupRefreshButton('#mtp_refresh_groups', '#mtp_tournament_id', function(tournamentId, options) {
      options = options || {};
      options.context = 'matches';
      MTPAdminUtils.loadTournamentGroups(tournamentId, options);
    });

    // Initialize participant refresh button
    MTPAdminUtils.initParticipantRefreshButton('#mtp_refresh_participants', '#mtp_tournament_id', function(tournamentId, options) {
      MTPAdminUtils.loadTournamentTeams(tournamentId, options);
    });

    // Load groups and teams on page load if tournament ID exists
    var initialTournamentId = $("#mtp_tournament_id").val();

    if (initialTournamentId) {
      MTPAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false, context: 'matches'});
      MTPAdminUtils.loadTournamentTeams(initialTournamentId, {preserveSelection: false});
      checkConditionalFields(initialTournamentId);
    } else {
      // Hide conditional fields if no tournament ID on load
      $('#mtp_sf_row').hide();
      $('#mtp_sg_row').hide();
      $('#mtp_sr_row').hide();
      $('#mtp_se_row').hide();
      $('#mtp_sp_row').hide();
      $('#mtp_sh_row').hide();
    }

    // Additional explicit listeners for checkboxes to ensure they work even when dynamically shown/hidden
    // Use event delegation to handle dynamically shown fields
    $(document).on('change', 'input[type="checkbox"][id^="mtp_"]', function() {
      updatePreview();
    });
  });

})(jQuery);
