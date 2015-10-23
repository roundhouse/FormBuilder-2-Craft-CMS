class AjaxSubmit
  init: =>
    $('form').submit (event) ->
      event.preventDefault()
      fd = new FormData(document.querySelector('form'))
      fd.append 'CustomField', 'This is some extra data'
      console.log fd


    # notificationContainer = $('.formbuilder-notification')
    # theForm = $('.formbuilder2-form')

    # # Parsleyjs
    # # theForm.parsley()

    # # AJAX Form Submit
    # theForm.submit (e) ->
    #   notificationContainer.html ''
    #   e.preventDefault()
    #   e.stopPropagation()
    #   url = '/actions/formBuilder2/entry/submitEntry'
    #   redirectUrl = $(@).children('[name=formRedirect]').attr('value')
      
    #   # files = $(this).find('[type=file]')
    #   # data = $(this).serialize()


    #   # $.each files, (key, value) ->
    #   #   file = $(value)[0].files
    #   #   fd.append key, file[0]
    #   #   console.log key
    #   #   console.log file
    #   # fd = new FormData()
    #   # fd.append('foo', 'foo')

    #   formData = new FormData
    #   formData.append 'name', 'value'
    #   formData.append 'a', 1
    #   formData.append 'b', 2

    #   console.log formData
      
      

    #   # formData = new FormData($(this)[0])
    #   # formData.append('data', data)
    #   # console.log data
    #   # console.log formData

    #   # Send it to the server
    #   # $.post url, formData, (response) ->
    #   #   if response.success
    #   #     if redirectUrl != '' 
    #   #       window.location.href = redirectUrl
    #   #     else
    #   #       notificationContainer.html '<p class="success-message">' + response.message + '</p>'
    #   #       theForm[0].reset()
    #   #   else
    #   #     notificationContainer.html '<p class="error-message">' + response.message + '</p>'


$(document).ready ->
  Application = new AjaxSubmit()
  Application.init()