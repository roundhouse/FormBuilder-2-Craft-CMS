class App
  init: =>

    # Copy Text Function
    clipboard = new Clipboard('.copy')
    clipboard.on 'success', (e) ->
      e.clearSelection()


    # New Form Tabs
    if $('.fb-new-form').length > 0
      newFormActiveTab = Cookies.get 'newform-active-tab'
      $('.menu-tabs a').click (event) ->
        event.preventDefault()
        $(this).parent().addClass 'current'
        $(this).parent().siblings().removeClass 'current'
        tab = $(this).attr('href')
        Cookies.set 'newform-active-tab', tab, expires: 7
        $('.tab-content').not(tab).css 'display', 'none'
        $(tab).fadeIn()


$(document).ready ->
  Application = new App()
  Application.init()