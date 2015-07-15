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
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
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
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
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
    
    jQuery(document).ready(function () {
      jQuery(".remove").on("click", function () {
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display','inherit').addClass('animate');
        jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_remove_task',
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
            ID: jQuery("#remove-task").attr('data-remove')
          },
          url: dt_js_vars.ajaxurl,
          success: function (value) {
            jQuery(".remove").find('.fa-refresh').removeClass('animate').removeClass('animate').removeClass('fa-refresh').addClass('fa-check');
            jQuery(".complete").removeClass('disabled');
          }, 
          error: function (value) {
            jQuery(".remove").removeClass('disabled').find('.fa-refresh').removeClass('animate');
          }
        });
      });
    });

  });

}(jQuery));