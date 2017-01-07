jQuery(document).ready(function($) {
  var SearchViewDaTaskUsers, openModalAssign;
  SearchViewDaTaskUsers = window.Backbone.View.extend({
    el: '#find-datask-tax',
    overlaySet: false,
    overlay: false,
    open: function() {
      this.$response.html('');
      this.$el.show();
      this.$input.focus();
      if (!this.$overlay.length) {
        $('body').append('<div id="find-posts-ui-find-overlay" class="ui-find-overlay"></div>');
        this.$overlay = $('#find-posts-ui-find-overlay');
      }
      this.$overlay.show();
      return this.send();
    },
    send: function() {
      var search;
      search = this;
      search.$spinner.show();
      return $.ajax(ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: {
          ps: search.$input.val(),
          action: 'find_datask_tax',
          _ajax_nonce: $('#find-datask-tax #_ajax_nonce').val()
        }
      }).always(function() {
        search.$spinner.hide();
      }).done(function(response) {
        var data;
        if (!response.success) {
          search.$response.text('Error');
        }
        data = response.data;
        if ('checkbox' === search.selectType) {
          data = data.replace(/type="radio"/gi, 'type="checkbox"');
        }
        search.$response.html(data);
      }).fail(function() {
        search.$response.text('Error');
      });
    },
    close: function() {
      this.$overlay.hide();
      return this.$el.hide();
    },
    escClose: function() {
      if (evt.which && 27 === evt.which) {
        return this.close();
      }
    },
    maybeStartSearch: function() {
      if (13 === evt.which) {
        return this.send();
      }
    },
    events: function() {
      return {
        'keypress .find-box-search :input': 'maybeStartSearch',
        'keyup #find-datask-tax-input': 'escClose',
        'click #find-datask-tax-search': 'send',
        'click #find-datask-tax-close': 'close'
      };
    },
    initialize: function() {
      this.$spinner = this.$el.find('.find-box-search .spinner');
      this.$input = this.$el.find('#find-datask-tax-input');
      this.$response = this.$el.find('#find-datask-tax-response');
      this.$overlay = $('#find-datask-tax-ui-find-overlay');
      this.listenTo(this, 'open', this.open);
      return this.listenTo(this, 'close', this.close);
    }
  });
  openModalAssign = function(e) {
    window.searchdataskusers = new SearchViewDaTaskUsers();
    return window.searchdataskusers.trigger('open');
  };
  return $('.modal-datask-assign').on('click', openModalAssign);
});

//# sourceMappingURL=modal-assign.js.map
