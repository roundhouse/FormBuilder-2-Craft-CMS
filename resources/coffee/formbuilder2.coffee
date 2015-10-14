class App
  init: =>

    # Copy Text Function
    clipboard = new Clipboard('.copy')
    clipboard.on 'success', (e) ->
      e.clearSelection()


  newForm: =>
    if $('.fb-new-form').length > 0
      # New Form Tabs
      newFormActiveTab = Cookies.get 'newform-active-tab'
      $('.menu-tabs a').click (event) ->
        event.preventDefault()
        $(@).parent().addClass 'current'
        $(@).parent().siblings().removeClass 'current'
        tab = $(@).attr('href')
        Cookies.set 'newform-active-tab', tab, expires: 7
        $('.tab-content').not(tab).css 'display', 'none'
        $(tab).fadeIn()

      # Redirect
      if $('#customRedirect').is(':checked')
        $('.method-redirect .checkbox-toggle').addClass 'selected'
        $('.method-redirect .checkbox-extra').show()

      # Ajax
      if $('#ajaxSubmit').is(':checked')
        $('.method-ajax .checkbox-toggle').addClass 'selected'

      # Spam Protection
      if $('#spamTimeMethod').is(':checked')
        $('.method-time .checkbox-toggle').addClass 'selected'
        $('.method-time .checkbox-extra').show()
      if $('#spamHoneypotMethod').is(':checked')
        $('.method-honeypot .checkbox-toggle').addClass 'selected'
        $('.method-honeypot .checkbox-extra').show()
      
      # Notificationss
      if $('#notifySubmission').is(':checked')
        $('.method-notify .checkbox-toggle').addClass 'selected'
        $('.method-notify .checkbox-extra').show()

      $('.checkbox-toggle').on 'click', ->
        toggle = $(@).data 'checkbox'
        $(@).toggleClass 'selected'
        if $(@).hasClass('selected')
          console.log 'has class selected'
          $('#'+toggle).prop 'checked', true
          $(@).next('.checkbox-extra').stop().slideDown()
        else
          $('#'+toggle).prop 'checked', false
          $(@).next('.checkbox-extra').stop().slideUp()



$(document).ready ->
  Application = new App()
  Application.init()
  Application.newForm()