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
            error: (value) ->
    
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
            error: (value) ->
) jQuery
