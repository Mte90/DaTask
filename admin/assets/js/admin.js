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
        }
      });
    });
    jQuery('.dt-remove-task').on('click', function() {
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
        }
      });
    });
    jQuery('.dt-remove-log-task').unbind('click').on('click', function(e) {
      var button;
      button = this;
      jQuery.ajax({
        type: 'GET',
        data: {
          action: 'dt_remove_log',
          ID: jQuery(this).attr('data-id')
        },
        url: ajaxurl,
        success: function(value) {
          return jQuery(button).parent().parent().find('.button').remove();
        }
      });
      e.preventDefault();
      return event.stopPropagation();
    });
    return jQuery('.dt-mark-remove-task').unbind('click').on('click', function(e) {
      var button;
      button = this;
      jQuery.ajax({
        type: 'GET',
        data: {
          action: 'dt_mark_remove',
          ID: jQuery(this).attr('data-id')
        },
        url: ajaxurl,
        success: function(value) {
          return jQuery(button).parent().parent().find('.button').remove();
        }
      });
      e.preventDefault();
      return event.stopPropagation();
    });
  });
})(jQuery);

//# sourceMappingURL=admin.js.map
