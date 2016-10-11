(($) ->
  'use strict'
  $ ->
    $('#tabs').tabs()
    $('.report-tab .menu li a').each () ->
      if $($(this).attr('href')).length == 0
        $(this).unbind('click')
        $(this).click ->
          window.location.href = $(this).attr('data-link') + $(this).attr('href')
            
    jQuery('.dt-approve-task').on 'click', ->
      button = this
      jQuery.ajax
        type: 'GET'
        data:
          action: 'dt_approval'
          _wpnonce: jQuery('#tabs-approval #dt-task-admin-nonce').val()
          ID: jQuery(this).attr('data-id')
        url: ajaxurl
        success: (value) ->
          jQuery(button).parent().parent().remove()
    
    jQuery('.dt-remove-task').on 'click', ->
      button = this
      jQuery.ajax
        type: 'GET'
        data:
          action: 'dt_remove_approval'
          _wpnonce: jQuery('#tabs-approval #dt-task-admin-nonce').val()
          ID: jQuery(this).attr('data-id')
        url: ajaxurl
        success: (value) ->
          jQuery(button).parent().parent().remove()
            
    jQuery('.dt-remove-log-task').unbind('click').on 'click', (e)->
      button = this
      jQuery.ajax
        type: 'GET'
        data:
          action: 'dt_remove_log'
          ID: jQuery(this).attr('data-id')
        url: ajaxurl
        success: (value) ->
          jQuery(button).parent().parent().find('.button').remove()
      e.preventDefault()
      event.stopPropagation()
                
    jQuery('.dt-mark-remove-task').unbind('click').on 'click', (e)->
      button = this
      jQuery.ajax
        type: 'GET'
        data:
          action: 'dt_mark_remove'
          ID: jQuery(this).attr('data-id')
        url: ajaxurl
        success: (value) ->
          jQuery(button).parent().parent().find('.button').remove()
      e.preventDefault()
      event.stopPropagation()
) jQuery
