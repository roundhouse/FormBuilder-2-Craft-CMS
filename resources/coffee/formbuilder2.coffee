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
      
      # Email Notifications & Templates
      $('.notification-tabs a').click (event) ->
        event.preventDefault()
        $(@).parent().addClass 'current'
        $(@).parent().siblings().removeClass 'current'
        tab = $(@).attr('href')
        $('.email-tab-content').not(tab).css 'display', 'none'
        $(tab).fadeIn()


      $('.menu-tabs a').click (event) ->
        event.preventDefault()
        $(@).parent().addClass 'current'
        $(@).parent().siblings().removeClass 'current'
        tab = $(@).attr('href')
        Cookies.set 'newform-active-tab', tab, expires: 7
        $('.tab-content').not(tab).css 'display', 'none'
        $(tab).fadeIn()

      # Errors
      if $('#form-settings').find('.errors').length > 0
        $('.tab-toggle-form-settings').addClass 'has-errors'
      if $('#spam-protection').find('.errors').length > 0
        $('.tab-toggle-spam-protection').addClass 'has-errors'
      if $('#messages').find('.errors').length > 0
        $('.tab-toggle-messages').addClass 'has-errors' 
      if $('#notify').find('.errors').length > 0
        $('.tab-toggle-notify').addClass 'has-errors' 

      if $('.has-errors').length > 0
        $('.menu-tabs h2').removeClass 'current'
        $('.has-errors').first().addClass('current').find('a').trigger('click')

      # Save Submissions To Database
      if $('#saveSubmissionsToDatabase').is(':checked')
        $('.method-database .checkbox-toggle').addClass 'selected'
        $('.method-database .checkbox-extra').show()

      # File Uplodas
      if $('#hasFileUploads').is(':checked')
        $('.method-files .checkbox-toggle').addClass 'selected'
        $('.method-files .checkbox-extra').show()

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
      
      # Notifications
      if $('#notifySubmission').is(':checked')
        $('.method-notify .checkbox-toggle').addClass 'selected'
        $('.method-notify .checkbox-extra').show()

      $('.checkbox-toggle').on 'click', ->
        toggle = $(@).data 'checkbox'
        $(@).toggleClass 'selected'
        if $(@).hasClass('selected')
          $('#'+toggle).prop 'checked', true
          $(@).next('.checkbox-extra').stop().slideDown()
        else
          $('#'+toggle).prop 'checked', false
          $(@).next('.checkbox-extra').stop().slideUp()



$(document).ready ->
  Application = new App()
  Application.init()
  Application.newForm()