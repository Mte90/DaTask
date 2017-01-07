jQuery(document).ready ($) ->
    SearchViewDaTaskUsers = window.Backbone.View.extend(
      el: '#find-datask-tax',
      overlaySet: false,
      overlay: false,
      open: ->
        @$response.html ''
        @$el.show()
        @$input.focus()
        if !@$overlay.length
          $('body').append '<div id="find-posts-ui-find-overlay" class="ui-find-overlay"></div>'
          @$overlay = $('#find-posts-ui-find-overlay')
        @$overlay.show()
        # Pull some results up by default
        @send()
      send: ->
        search = this
        search.$spinner.show()
        $.ajax(ajaxurl,
          type: 'POST'
          dataType: 'json'
          data:
            ps: search.$input.val()
            action: 'find_datask_tax'
            _ajax_nonce: $('#find-datask-tax #_ajax_nonce').val()).always(->
          search.$spinner.hide()
          return
        ).done((response) ->
          if !response.success
            search.$response.text 'Error'
          data = response.data
          if 'checkbox' == search.selectType
            data = data.replace(/type="radio"/gi, 'type="checkbox"')
          search.$response.html data
          return
        ).fail ->
          search.$response.text 'Error'
          return
      close: ->
        @$overlay.hide()
        @$el.hide()
      escClose: ->
        if evt.which and 27 == evt.which
          @close()
      maybeStartSearch: ->
        if 13 == evt.which
          @send()
      events: ->
        {
          'keypress .find-box-search :input': 'maybeStartSearch'
          'keyup #find-datask-tax-input': 'escClose'
#          'click #find-datask-tax-submit': 'selectPost'
          'click #find-datask-tax-search': 'send'
          'click #find-datask-tax-close': 'close'
        }
      initialize: ->
        @$spinner = @$el.find('.find-box-search .spinner')
        @$input = @$el.find('#find-datask-tax-input')
        @$response = @$el.find('#find-datask-tax-response')
        @$overlay = $('#find-datask-tax-ui-find-overlay')
        @listenTo this, 'open', @open
        @listenTo this, 'close', @close
    )
    
    openModalAssign = (e) ->
      window.searchdataskusers = new SearchViewDaTaskUsers()
#      search.$idInput = $(evt.currentTarget).parents('.cmb-type-post-search-text.cmb2-id-').find('.cmb-td input[type="text"]')
#      search.postType = search.$idInput.data('posttype')
#      search.selectType = if 'radio' == search.$idInput.data('selecttype') then 'radio' else 'checkbox'
      window.searchdataskusers.trigger 'open'

    $('.modal-datask-assign').on 'click', openModalAssign

