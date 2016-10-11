(function($) {
  'use strict';
  $(function() {
    $('#tabs').tabs();
    return $('.report-tab .menu li a').each(function() {
      if ($($(this).attr('href')).length === 0) {
        $(this).unbind('click');
        return $(this).click(function() {
          return window.location.href = $(this).attr('data-link') + $(this).attr('href');
        });
      }
    });
  });
})(jQuery);
