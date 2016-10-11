(($) ->
  'use strict'
  $ ->
    $('#tabs').tabs()
    $('.report-tab .menu li a').each () ->
      if $($(this).attr('href')).length == 0
        $(this).unbind('click')
        $(this).click ->
            window.location.href = $(this).attr('data-link') + $(this).attr('href')
  return
) jQuery
