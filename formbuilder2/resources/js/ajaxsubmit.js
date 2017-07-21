var FormBuilder2,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

FormBuilder2 = (function() {
  function FormBuilder2(el) {
    this.init = bind(this.init, this);
    var $notificationContainer;
    this.$form = $(el);
    $notificationContainer = null;
  }

  FormBuilder2.prototype.init = function() {
    var $emailField, self;
    this.$notificationContainer = this.$form.find('.notifications');
    $emailField = this.$form.find("input[type='email']");
    self = this;
    return this.$form.on('submit', (function(_this) {
      return function(e) {
        var email;
        e.preventDefault();
        if ($emailField.length > 0) {
          email = $emailField.val();
          if (self.validateEmail(email)) {
            return self.submitForm(e);
          } else {
            return self.$notificationContainer.html('<p class="error-message flash-inline error">Invalid email, please try again.</p>');
          }
        } else {
          return self.submitForm(e);
        }
      };
    })(this));
  };

  FormBuilder2.prototype.validateEmail = function(email) {
    var re;
    re = /\S+@\S+\.\S+/;
    return re.test(email);
  };

  FormBuilder2.prototype.submitForm = function(e) {
    var data, errorsContainer, self, url;
    self = this;
    data = this.$form.serialize();
    url = '/actions/formBuilder2/entry/submitEntry';
    errorsContainer = this.$notificationContainer.append('<ul class="errors"></ul>').find('ul.errors');
    return $.post(url, data, function(response) {
      var responseEvent;
      if (window.CustomEvent) {
        responseEvent = new CustomEvent('formbuilder:submit', {
          detail: {
            'response': response,
            'class': e.currentTarget.className,
            'handle': e.currentTarget.id
          },
          bubbles: true,
          cancelable: true
        });
        e.currentTarget.dispatchEvent(responseEvent);
      }
      if (response.success) {
        self.$notificationContainer.html('<p class="success-message flash-inline">' + response.customSuccessMessage + '</p>');
        return self.$form[0].reset();
      } else {
        self.$notificationContainer.html('<p class="error-message flash-inline error">' + response.customErrorMessage + '</p>');
        return $.each(response.validationErrors, function(index, value) {
          var label;
          label = $('input[name="' + index + '"]');
          console.log(label);
          if (label.length) {
            label.addClass('has-error').find('span').remove();
            return label.parent().parent().addClass('has-error');
          }
        });
      }
    });
  };

  return FormBuilder2;

})();
