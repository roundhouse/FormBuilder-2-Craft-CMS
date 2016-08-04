$(document).ready ->
  notificationContainer = $('.formbuilder2 .notifications')
  theForm = $('form.formbuilder2')

  # AJAX Form Submit
  theForm.submit (e) ->
    notificationContainer.html ''
    $(@).find('label > span').remove();
    e.preventDefault()
    url = '/actions/' + $(@).children('[name=action]').attr('value')
    redirectUrl = $(@).children('[name=redirect]').attr('value')
    data = $(this).serialize()

    # Start Loading
    notificationContainer.html '<p>Sending...</p>'
    $.post url, data, (response) ->
      if response.success
        if redirectUrl
          window.location.href = redirectUrl
        else
          notificationContainer.html '<p class="success-message">' + response.customSuccessMessage + '</p>'
          theForm[0].reset()
      else
        notificationContainer.html '<p class="error-message">' + response.customErrorMessage + '</p>'
        errorsContainer = $('.notifications').append('<ul class="errors"></ul>').find('ul.errors')
        $.each response.validationErrors, (index, value) ->
          label = $('label[for="' + $('[name="' + index + '"]').attr('id') + '"]');

          if label.length
            label.find('span').remove()
            return label.append('<span>' + value + '</span>')
          else
            return errorsContainer.append('<li>' + value + '</li>');