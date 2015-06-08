(function ($) {
  "use strict";

  $(function () {

    jQuery(document).ready(function () {
      jQuery(".complete").on("click", function () {
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'wo_complete_task',
            _wpnonce: jQuery('.wo-button #wo-task-nonce').val(),
            ID: jQuery("#complete-task").attr('data-complete')
          },
          url: wo_js_vars.ajaxurl,
          success: function (value) {
            console.log(value);
          }
        });
      });
    });

    // Place your public-facing JavaScript here

  });

}(jQuery));