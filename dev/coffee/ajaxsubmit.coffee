class FormBuilder2
  constructor: (el) ->
    @$form = $(el)
    $notificationContainer = null

  init: () =>
    @$notificationContainer = @$form.find('.notifications')
    $emailField = @$form.find("input[type='email']")
    self = @
    @$form.on 'submit', (e) =>
      e.preventDefault()
      if $emailField.length > 0
        email = $emailField.val()
        if self.validateEmail(email)
          self.submitForm(e)
        else
          self.$notificationContainer.html '<p class="error-message flash-inline error">Invalid email, please try again.</p>'
      else
        self.submitForm(e)

  validateEmail: (email) ->
    re = /\S+@\S+\.\S+/
    return re.test email

  submitForm: (e) ->
    self = @
    data = @$form.serialize()
    url = '/actions/formBuilder2/entry/submitEntry'
    errorsContainer = @$notificationContainer.append('<ul class="errors"></ul>').find('ul.errors')
    $.post url, data, (response) ->
        if window.CustomEvent
            responseEvent = new CustomEvent('formbuilder:submit',
                detail: 
                    'response': response
                    'class': e.currentTarget.className
                    'handle': e.currentTarget.id
                bubbles: true
                cancelable: true)
            e.currentTarget.dispatchEvent responseEvent
        if response.success
            self.$notificationContainer.html '<p class="success-message flash-inline">' + response.customSuccessMessage + '</p>'
            self.$form[0].reset()
        else
            self.$notificationContainer.html '<p class="error-message flash-inline error">' + response.customErrorMessage + '</p>'
            $.each response.validationErrors, (index, value) ->
                label = $('input[name="'+index+'"]')
                console.log label
                if label.length
                    label.addClass('has-error').find('span').remove()
                    label.parent().parent().addClass 'has-error'