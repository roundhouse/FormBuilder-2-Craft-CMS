$(document).ready(function() {
  var notificationContainer, theForm;
  notificationContainer = $('.formbuilder2 .notifications');
  theForm = $('form.formbuilder2');
  return theForm.submit(function(e) {
    var data, redirectUrl, url;
    notificationContainer.html('');
    $(this).find('label > span').remove();
    e.preventDefault();
    url = '/actions/' + $(this).children('[name=action]').attr('value');
    redirectUrl = $(this).children('[name=formRedirect]').attr('value');
    data = $(this).serialize();
    notificationContainer.html('<p>Sending...</p>');
    return $.post(url, data, function(response) {
      var errorsContainer;
      if (response.success) {
        if (redirectUrl) {
          return window.location.href = redirectUrl;
        } else {
          notificationContainer.html('<p class="success-message">' + response.customSuccessMessage + '</p>');
          return theForm[0].reset();
        }
      } else {
        notificationContainer.html('<p class="error-message">' + response.customErrorMessage + '</p>');
        errorsContainer = $('.notifications').append('<ul class="errors"></ul>').find('ul.errors');
        return $.each(response.validationErrors, function(index, value) {
          var label;
          label = $('label[for="' + $('[name="' + index + '"]').attr('id') + '"]');
          if (label.length) {
            label.find('span').remove();
            return label.append('<span>' + value + '</span>');
          } else {
            return errorsContainer.append('<li>' + value + '</li>');
          }
        });
      }
    });
  });
});
