$(document).ready(function() {
  var notificationContainer, theForm;
  notificationContainer = $('.formbuilder2 .notifications');
  theForm = $('form.formbuilder2');
  return theForm.submit(function(e) {
    var data, redirect, redirectUrl, url;
    notificationContainer.html('');
    e.preventDefault();
    url = '/actions/' + $(this).children('[name=action]').attr('value');
    redirect = $(this).children('[name=formRedirect]').attr('data-custom-redirect');
    redirectUrl = $(this).children('[name=formRedirect]').attr('value');
    data = $(this).serialize();
    notificationContainer.html('<p>Sending...</p>');
    return $.post(url, data, function(response) {
      var errorsContainer;
      if (response.success) {
        if (redirect === '1') {
          return window.location.href = redirectUrl;
        } else {
          notificationContainer.html('<p class="success-message">' + response.customSuccessMessage + '</p>');
          return theForm[0].reset();
        }
      } else {
        notificationContainer.html('<p class="error-message">' + response.customErrorMessage + '</p>');
        errorsContainer = $('.notifications').append('<ul class="errors"></ul>').find('ul.errors');
        return $.each(response.validationErrors, function(index, value) {
          return errorsContainer.append('<li>' + value + '</li>');
        });
      }
    });
  });
});
