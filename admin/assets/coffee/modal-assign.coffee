jQuery(document).ready ($) ->
    SearchViewDaTaskUsers = window.Backbone.View.extend(
      el: '#find-datask-tax',
      overlay: false,
      open: ->
        @$response.html ''
        @$el.show()
        @$input.focus()
        if !@$overlay.length
          $('body').append '<div id="find-datask-tax-ui-find-overlay" class="ui-find-overlay"></div>'
          @$overlay = $('#find-datask-tax-ui-find-overlay')
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
            user: window.dataskuserid
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
      escClose: (evt)->
        if evt.which and 27 == evt.which
          @close()
      maybeStartSearch: (evt)->
        @send()
      selectPost: (evt)->
        search = this
        evt.preventDefault()
        @$checked = $('#find-datask-tax-response input[name="found_tax_task"]:checked')
        checked = @$checked.map(->
          @value
        ).get()
        if !checked.length
          @close()
          return
        label = []
        $.each checked, (index, value) ->
          label.push $('#find-datask-tax-response input#found-' + value).attr 'value'
          return
        $.ajax(ajaxurl,
          type: 'POST'
          dataType: 'json'
          data:
            taxs: label.join(', ')
            user: window.dataskuserid
            action: 'add_datask_tax'
            _ajax_nonce: $('#find-datask-tax #_ajax_nonce').val()).always(->
          search.$spinner.hide()
          return
        ).fail ->
          search.$response.text 'Error'
          return
        @close()
      events: ->
        {
          'keypress #find-datask-tax-input': 'maybeStartSearch'
          'keyup #find-datask-tax-input': 'escClose'
          'click #find-datask-tax-submit': 'selectPost'
          'click #find-datask-tax-search': 'send'
          'click #find-datask-tax-close': 'close'
        }
      initialize: ->
        @$response = @$el.find('#find-datask-tax-response')
        @$overlay = $('#find-datask-tax-ui-find-overlay')
        @$input = @$el.find('#find-datask-tax-input')
        @$spinner = @$el.find('.find-datask-tax .spinner');
        @listenTo this, 'open', @open
        @listenTo this, 'close', @close
    )
    
    openModalAssign = (e) ->
      window.dataskuserid = jQuery(this).data('user-id')
      searchdataskusers = new SearchViewDaTaskUsers()
      searchdataskusers.trigger 'open'

    $('.modal-datask-assign').on 'click', openModalAssign
