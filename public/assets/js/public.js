(function ($) {
  "use strict";

  $(function () {

    jQuery(document).ready(function () {
      jQuery(".complete").on("click", function () {
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_complete_task',
            _wpnonce: jQuery('.dt-button #dt-task-nonce').val(),
            ID: jQuery("#complete-task").attr('data-complete')
          },
          url: dt_js_vars.ajaxurl,
          success: function (value) {
            alert('Done!');
          }
        });
      });
    });
    
    jQuery(document).ready(function () {
      jQuery(".save-later").on("click", function () {
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_task_later',
            _wpnonce: jQuery('.dt-button #dt-task-nonce').val(),
            ID: jQuery("#save-for-later").attr('data-save-later')
          },
          url: dt_js_vars.ajaxurl,
          success: function (value) {
            alert('Done!');
          }
        });
      });
    });

    // Place your public-facing JavaScript here

  });

}(jQuery));