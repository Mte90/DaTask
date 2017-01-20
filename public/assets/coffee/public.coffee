(($) ->
  'use strict'
  jQuery(document).ready ->
    jQuery('.dt-buttons .complete').on 'click', ->
      if !jQuery(this).hasClass('disabled')
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass 'animate'
        jQuery.ajax
          type: 'GET'
          data:
            action: 'dt_complete_task'
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val()
            ID: jQuery('#complete-task').attr('data-complete')
          url: dt_js_vars.ajaxurl
          success: (value) ->
            jQuery('html, body').animate { scrollTop: jQuery('#respond').offset().top }, 'slow', ->
              jQuery('#respond').animate {
                'margin-left': '-=30px'
                'margin-right': '+=30px'
              }, 150, ->
                jQuery('#respond').animate {
                  'margin-left': '+=60px'
                  'margin-right': '-=30px'
                }, 150, ->
                  jQuery('#respond').animate {
                    'margin-left': '-=30px'
                    'margin-right': '+=30px'
                  }, 150
                  return
                return
              return
            jQuery('.dt-buttons .complete').find('.fa-exclamation-circle').hide()
            jQuery('.dt-buttons .complete').find('.fa-refresh').removeClass('animate').hide()
            jQuery('.dt-buttons .complete').find('.fa-check').show()
            jQuery('.dt-buttons .save-later').removeClass 'disabled'
            jQuery('.dt-buttons .remove').removeClass 'disabled'
          error: (value) ->
            jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-refresh').removeClass 'animate'
    
    jQuery('.dt-buttons .save-later:not(.disabled)').on 'click', ->
      if !jQuery(this).hasClass('disabled')
        jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass 'animate'
        jQuery.ajax
          type: 'GET'
          data:
            action: 'dt_task_later'
            _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val()
            ID: jQuery('#save-for-later').attr('data-save-later')
          url: dt_js_vars.ajaxurl
          success: (value) ->
            jQuery('.dt-buttons .save-later').find('.fa-refresh').removeClass('animate').hide()
            jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide()
          error: (value) ->
            jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-refresh').removeClass 'animate'
    
    jQuery('.dt-buttons .remove').on 'click', ->
      jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass 'animate'
      jQuery.ajax
        type: 'GET'
        data:
          action: 'dt_remove_task'
          _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val()
          ID: jQuery('#remove-task').attr('data-remove')
        url: dt_js_vars.ajaxurl
        success: (value) ->
          jQuery('.dt-buttons .remove').find('.fa-refresh').removeClass('animate').removeClass('animate').removeClass('fa-refresh').addClass 'fa-check'
          jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide()
        error: (value) ->
          jQuery('.dt-buttons .remove').removeClass('disabled').find('.fa-refresh').removeClass 'animate'
    
    jQuery('#user-contact-form button').on 'click', ->
      jQuery(this).addClass 'disabled'
      jQuery.ajax
        type: 'POST'
        data:
          action: 'dt_contact_user'
          _wpnonce: jQuery('#user-contact-form #dt_user_nonce').val()
          content: jQuery('textarea[name="datask-email-subject"]').val()
          user_id: jQuery('#user-contact-form button').data('user')
        url: dt_js_vars.ajaxurl
        success: (value) ->
          jQuery('textarea[name="datask-email-subject"]').addClass 'disabled'
        error: (value) ->
          jQuery('#user-contact-form button').removeClass 'disabled'
    
    DT_Ajax_Filter = (opts) ->
      @init opts
    
    DT_Ajax_Filter.prototype =
      selected: ->
        arr =  @loop(jQuery('.' + @selected_filters + ':selected, .' + @selected_filters + ' input:checked'))
        # Join the array with an "&" so we can break it later.
        arr.join '&'
      loop: (node) ->
        # Return an array of selected navigation classes.
        arr = []
        arr.push 'search=' + jQuery('#searcher').val()
        node.each ->
          arr.push jQuery(this).data('slug') + '=' + jQuery(this).data('tax')
        return arr
      filter: (arr) ->
        self = this
        # Return all the relevant posts...
        jQuery.ajax
          url: dt_js_search_vars.ajaxurl
          data:
            'action': 'dt-ajax-search'
            'filters': arr
            'postsperpage': jQuery('#ajax-filtered-section').attr('data-postsperpage')
            'paged': dt_js_search_vars.thisPage
            '_ajax_nonce': dt_js_search_vars.nonce
          beforeSend: ->
            self.section.animate { 'opacity': .0 }, 'slow'
            jQuery('.ajax-filter .pagination').hide 'slow'
            # show pagination 
          success: (html) ->
            self.section.empty()
            self.section.append html
          complete: ->
            jQuery('html, body').animate { scrollTop: jQuery(self.section).offset().top - 120 }, 500
            self.section.animate { 'opacity': 1 }, 'slow'
            jQuery('.pagination').show 'slow'
            # show pagination 
            self.running = false
        return
      clicker: ->
        self = this
        jQuery('#ajax-filters, #ajax-content').on 'click', @links, (e) ->
          if self.running == false
            # Set to true to stop function chaining.
            self.running = true
            # Cache some of the DOM elements for re-use later in the method.
            link = jQuery(this)
            parent = link.parent('li')
            if parent.length > 0
              dt_js_search_vars.thisPage = 1
            if jQuery(this).attr('rel')
              dt_js_search_vars.thisPage = jQuery(this).attr('rel')
            self.filter self.selected()
          e.preventDefault()
        jQuery('#ajax-filters, #ajax-content').on 'change', @select, (e) ->
          if self.running == false
            # Set to true to stop function chaining.
            self.running = true
            dt_js_search_vars.thisPage = 1
            self.filter self.selected()
          e.preventDefault()
      reset: ->
        # remove all other ".no-results" 
        jQuery('.no-results').remove()
        jQuery('#ajax-content #ajax-filtered-section').append '<p class=\'no-results\'>' + dt_js_search_vars.on_load_text + '</p>'
        # add msg 
        jQuery('#ajax-content .ajax-pagination, #ajax-content .ajax-loaded').hide()
        # hide pagination 
      init: (opts) ->
        # Set up the properties
        @opts = opts
        @running = false
        @section = jQuery(@opts['section'])
        @links = @opts['links']
        @selected_filters = @opts['selected_filters']
        # Run the methods.
        @clicker()
    
    af_filter = new DT_Ajax_Filter(
      'section': '#ajax-filtered-section'
      'links': '.pagelink, #go'
      'selected_filters': 'filter-selected')
    
    # toggle placeholder text on search input 
    placeholder_search = jQuery('#searcher').attr('placeholder')
    jQuery('#searcher').focus ->
      jQuery(this).attr 'placeholder', ''
    jQuery('#searcher').focusout ->
      jQuery(this).attr 'placeholder', placeholder_search
    
    # reset search 
    jQuery('.reset').click (e) ->
      # stop default action 
      e.preventDefault()
      # empty search 
      jQuery('input#searcher').val ''
      # reset all forms 
      jQuery('#ajax-filters select').each ->
        jQuery(this).find('option:first').attr 'selected', 'selected'
        # select first option 
        jQuery(this).find('option').show()
        # show all options 
        jQuery(this).prop 'selectedIndex', 0
      # back to basics 
      af_filter.reset()

    jQuery('input#searcher').keypress (event) ->
      if event.which == 13
        event.preventDefault()
        jQuery('#go')[0].click()
        
) jQuery
