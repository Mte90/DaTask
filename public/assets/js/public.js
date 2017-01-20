(function($) {
  'use strict';
  return jQuery(document).ready(function() {
    var DT_Ajax_Filter, af_filter, placeholder_search;
    jQuery('.dt-buttons .complete').on('click', function() {
      if (!jQuery(this).hasClass('disabled')) {
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
        return jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_complete_task',
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
            ID: jQuery('#complete-task').attr('data-complete')
          },
          url: dt_js_vars.ajaxurl,
          success: function(value) {
            jQuery('html, body').animate({
              scrollTop: jQuery('#respond').offset().top
            }, 'slow', function() {
              jQuery('#respond').animate({
                'margin-left': '-=30px',
                'margin-right': '+=30px'
              }, 150, function() {
                jQuery('#respond').animate({
                  'margin-left': '+=60px',
                  'margin-right': '-=30px'
                }, 150, function() {
                  jQuery('#respond').animate({
                    'margin-left': '-=30px',
                    'margin-right': '+=30px'
                  }, 150);
                });
              });
            });
            jQuery('.dt-buttons .complete').find('.fa-exclamation-circle').hide();
            jQuery('.dt-buttons .complete').find('.fa-refresh').removeClass('animate').hide();
            jQuery('.dt-buttons .complete').find('.fa-check').show();
            jQuery('.dt-buttons .save-later').removeClass('disabled');
            return jQuery('.dt-buttons .remove').removeClass('disabled');
          },
          error: function(value) {
            return jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-refresh').removeClass('animate');
          }
        });
      }
    });
    jQuery('.dt-buttons .save-later:not(.disabled)').on('click', function() {
      if (!jQuery(this).hasClass('disabled')) {
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
        return jQuery.ajax({
          type: 'GET',
          data: {
            action: 'dt_task_later',
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
            ID: jQuery('#save-for-later').attr('data-save-later')
          },
          url: dt_js_vars.ajaxurl,
          success: function(value) {
            jQuery('.dt-buttons .save-later').find('.fa-refresh').removeClass('animate').hide();
            return jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide();
          },
          error: function(value) {
            return jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-refresh').removeClass('animate');
          }
        });
      }
    });
    jQuery('.dt-buttons .remove').on('click', function() {
      jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
      return jQuery.ajax({
        type: 'GET',
        data: {
          action: 'dt_remove_task',
          _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
          ID: jQuery('#remove-task').attr('data-remove')
        },
        url: dt_js_vars.ajaxurl,
        success: function(value) {
          jQuery('.dt-buttons .remove').find('.fa-refresh').removeClass('animate').removeClass('animate').removeClass('fa-refresh').addClass('fa-check');
          return jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide();
        },
        error: function(value) {
          return jQuery('.dt-buttons .remove').removeClass('disabled').find('.fa-refresh').removeClass('animate');
        }
      });
    });
    jQuery('#user-contact-form button').on('click', function() {
      jQuery(this).addClass('disabled');
      return jQuery.ajax({
        type: 'POST',
        data: {
          action: 'dt_contact_user',
          _wpnonce: jQuery('#user-contact-form #dt_user_nonce').val(),
          content: jQuery('textarea[name="datask-email-subject"]').val(),
          user_id: jQuery('#user-contact-form button').data('user')
        },
        url: dt_js_vars.ajaxurl,
        success: function(value) {
          return jQuery('textarea[name="datask-email-subject"]').addClass('disabled');
        },
        error: function(value) {
          return jQuery('#user-contact-form button').removeClass('disabled');
        }
      });
    });
    DT_Ajax_Filter = function(opts) {
      return this.init(opts);
    };
    DT_Ajax_Filter.prototype = {
      selected: function() {
        var arr;
        arr = this.loop(jQuery('.' + this.selected_filters + ':selected, .' + this.selected_filters + ' input:checked'));
        return arr.join('&');
      },
      loop: function(node) {
        var arr;
        arr = [];
        arr.push('search=' + jQuery('#searcher').val());
        node.each(function() {
          return arr.push(jQuery(this).data('slug') + '=' + jQuery(this).data('tax'));
        });
        return arr;
      },
      filter: function(arr) {
        var self;
        self = this;
        jQuery.ajax({
          url: dt_js_search_vars.ajaxurl,
          data: {
            'action': 'dt-ajax-search',
            'filters': arr,
            'postsperpage': jQuery('#ajax-filtered-section').attr('data-postsperpage'),
            'paged': dt_js_search_vars.thisPage,
            '_ajax_nonce': dt_js_search_vars.nonce
          },
          beforeSend: function() {
            self.section.animate({
              'opacity': .0
            }, 'slow');
            return jQuery('.ajax-filter .pagination').hide('slow');
          },
          success: function(html) {
            self.section.empty();
            return self.section.append(html);
          },
          complete: function() {
            jQuery('html, body').animate({
              scrollTop: jQuery(self.section).offset().top - 120
            }, 500);
            self.section.animate({
              'opacity': 1
            }, 'slow');
            jQuery('.pagination').show('slow');
            return self.running = false;
          }
        });
      },
      clicker: function() {
        var self;
        self = this;
        jQuery('#ajax-filters, #ajax-content').on('click', this.links, function(e) {
          var link, parent;
          if (self.running === false) {
            self.running = true;
            link = jQuery(this);
            parent = link.parent('li');
            if (parent.length > 0) {
              dt_js_search_vars.thisPage = 1;
            }
            if (jQuery(this).attr('rel')) {
              dt_js_search_vars.thisPage = jQuery(this).attr('rel');
            }
            self.filter(self.selected());
          }
          return e.preventDefault();
        });
        return jQuery('#ajax-filters, #ajax-content').on('change', this.select, function(e) {
          if (self.running === false) {
            self.running = true;
            dt_js_search_vars.thisPage = 1;
            self.filter(self.selected());
          }
          return e.preventDefault();
        });
      },
      reset: function() {
        jQuery('.no-results').remove();
        jQuery('#ajax-content #ajax-filtered-section').append('<p class=\'no-results\'>' + dt_js_search_vars.on_load_text + '</p>');
        return jQuery('#ajax-content .ajax-pagination, #ajax-content .ajax-loaded').hide();
      },
      init: function(opts) {
        this.opts = opts;
        this.running = false;
        this.section = jQuery(this.opts['section']);
        this.links = this.opts['links'];
        this.selected_filters = this.opts['selected_filters'];
        return this.clicker();
      }
    };
    af_filter = new DT_Ajax_Filter({
      'section': '#ajax-filtered-section',
      'links': '.pagelink, #go',
      'selected_filters': 'filter-selected'
    });
    placeholder_search = jQuery('#searcher').attr('placeholder');
    jQuery('#searcher').focus(function() {
      return jQuery(this).attr('placeholder', '');
    });
    jQuery('#searcher').focusout(function() {
      return jQuery(this).attr('placeholder', placeholder_search);
    });
    jQuery('.reset').click(function(e) {
      e.preventDefault();
      jQuery('input#searcher').val('');
      jQuery('#ajax-filters select').each(function() {
        jQuery(this).find('option:first').attr('selected', 'selected');
        jQuery(this).find('option').show();
        return jQuery(this).prop('selectedIndex', 0);
      });
      return af_filter.reset();
    });
    return jQuery('input#searcher').keypress(function(event) {
      if (event.which === 13) {
        event.preventDefault();
        return jQuery('#go')[0].click();
      }
    });
  });
})(jQuery);
