(function ($) {
  "use strict";

  $(function () {

    jQuery(document).ready(function () {
      jQuery(".complete").on("click", function () {
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display','inherit').addClass('animate');
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_complete_task',
            _wpnonce: jQuery('.dt-button #dt-task-nonce').val(),
            ID: jQuery("#complete-task").attr('data-complete')
          },
          url: dt_js_vars.ajaxurl,
          success: function (value) {
            jQuery(".complete").find('.fa-refresh').removeClass('animate').removeClass('fa-refresh').addClass('fa-check');
          }, 
          error: function (value) {
            jQuery(".complete").removeClass('disabled').find('.fa-refresh').removeClass('animate');
          }
        });
      });
    });
    
    jQuery(document).ready(function () {
      jQuery(".save-later").on("click", function () {
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display','inherit').addClass('animate');
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_task_later',
            _wpnonce: jQuery('.dt-button #dt-task-nonce').val(),
            ID: jQuery("#save-for-later").attr('data-save-later')
          },
          url: dt_js_vars.ajaxurl,
          success: function (value) {
            jQuery(".save-later").find('.fa-refresh').removeClass('animate').removeClass('animate').removeClass('fa-refresh').addClass('fa-check');
          }, 
          error: function (value) {
            jQuery(".complete").removeClass('disabled').find('.fa-refresh').removeClass('animate');
          }
        });
      });
    });

    // Place your public-facing JavaScript here

  });

}(jQuery));