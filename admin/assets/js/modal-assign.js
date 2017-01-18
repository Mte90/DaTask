jQuery(document).ready(function($) {
  var SearchViewDaTaskUsers, openModalAssign;
  SearchViewDaTaskUsers = window.Backbone.View.extend({
    el: '#find-datask-tax',
    overlay: false,
    open: function() {
      this.$response.html('');
      this.$el.show();
      this.$input.focus();
      if (!this.$overlay.length) {
        $('body').append('<div id="find-datask-tax-ui-find-overlay" class="ui-find-overlay"></div>');
        this.$overlay = $('#find-datask-tax-ui-find-overlay');
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
          user: window.dataskuserid,
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
    escClose: function(evt) {
      if (evt.which && 27 === evt.which) {
        return this.close();
      }
    },
    maybeStartSearch: function(evt) {
      return this.send();
    },
    selectPost: function(evt) {
      var checked, label, search;
      search = this;
      evt.preventDefault();
      this.$checked = $('#find-datask-tax-response input[name="found_tax_task"]:checked');
      checked = this.$checked.map(function() {
        return this.value;
      }).get();
      if (!checked.length) {
        this.close();
        return;
      }
      label = [];
      $.each(checked, function(index, value) {
        label.push($('#find-datask-tax-response input#found-' + value).attr('value'));
      });
      $.ajax(ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: {
          taxs: label.join(', '),
          user: window.dataskuserid,
          action: 'add_datask_tax',
          _ajax_nonce: $('#find-datask-tax #_ajax_nonce').val()
        }
      }).always(function() {
        search.$spinner.hide();
      }).fail(function() {
        search.$response.text('Error');
      });
      return this.close();
    },
    events: function() {
      return {
        'keypress #find-datask-tax-input': 'maybeStartSearch',
        'keyup #find-datask-tax-input': 'escClose',
        'click #find-datask-tax-submit': 'selectPost',
        'click #find-datask-tax-search': 'send',
        'click #find-datask-tax-close': 'close'
      };
    },
    initialize: function() {
      this.$response = this.$el.find('#find-datask-tax-response');
      this.$overlay = $('#find-datask-tax-ui-find-overlay');
      this.$input = this.$el.find('#find-datask-tax-input');
      this.$spinner = this.$el.find('.find-datask-tax .spinner');
      this.listenTo(this, 'open', this.open);
      return this.listenTo(this, 'close', this.close);
    }
  });
  openModalAssign = function(e) {
    var searchdataskusers;
    window.dataskuserid = jQuery(this).data('user-id');
    searchdataskusers = new SearchViewDaTaskUsers();
    return searchdataskusers.trigger('open');
  };
  return $('.modal-datask-assign').on('click', openModalAssign);
});
