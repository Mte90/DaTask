(function($) {
  'use strict';
  return $(function() {
    $('#tabs').tabs();
    $('.report-tab .menu li a').each(function() {
      if ($($(this).attr('href')).length === 0) {
        $(this).unbind('click');
        return $(this).click(function() {
          return window.location.href = $(this).attr('data-link') + $(this).attr('href');
        });
      }
    });
    jQuery('.dt-approve-task').on('click', function() {
      var button;
      button = this;
      return jQuery.ajax({
        type: 'GET',
        data: {
          action: 'dt_approval',
          _wpnonce: jQuery('#tabs-approval #dt-task-admin-nonce').val(),
          ID: jQuery(this).attr('data-id')
        },
        url: ajaxurl,
        success: function(value) {
          return jQuery(button).parent().parent().remove();
        },
        error: function(value) {}
      });
    });
    return jQuery('.dt-remove-task').on('click', function() {
      var button;
      button = this;
      return jQuery.ajax({
        type: 'GET',
        data: {
          action: 'dt_remove_approval',
          _wpnonce: jQuery('#tabs-approval #dt-task-admin-nonce').val(),
          ID: jQuery(this).attr('data-id')
        },
        url: ajaxurl,
        success: function(value) {
          return jQuery(button).parent().parent().remove();
        },
        error: function(value) {}
      });
    });
  });
})(jQuery);
