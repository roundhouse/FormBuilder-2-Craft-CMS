$(document).ready(function() {
  var notificationContainer, theForm;
  notificationContainer = $('.notifications');
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
      if (response.success) {
        if (redirect === '1') {
          return window.location.href = redirectUrl;
        } else {
          notificationContainer.html('<p class="success-message">' + response.message + '</p>');
          return theForm[0].reset();
        }
      } else {
        return notificationContainer.html('<p class="error-message">' + response.message + '</p>');
      }
    });
  });
});
