var FormOption, FormOptionModal, FormTermsOptionModal;

if ($ && window.Garnish) {
  FormOption = Garnish.Base.extend({
    $container: null,
    $enableFormOption: null,
    $formOptionInput: null,
    $formOptionInputTwo: null,
    $formOptionResultHtml: null,
    $toggleBtn: null,
    $editFormOption: null,
    modal: null,
    type: null,
    name: null,
    editing: false,
    init: function(container) {
      this.$container = $(container);
      this.$enableFormOption = this.$container.find('.enable-form-option');
      this.$formOptionInput = this.$container.find('.form-option-input');
      this.$formOptionInputTwo = this.$container.find('.form-option-textarea');
      this.$formOptionResultHtml = this.$container.find('.option-result');
      this.$editFormOption = this.$container.find('.option-edit');
      this.$toggleBtn = this.$container.find('.toggle-option');
      this.type = this.$container.data('type');
      this.name = this.$container.data('name');
      this.nameTwo = this.$container.data('name-two');
      this.addListener(this.$editFormOption, 'click', 'editFormOption');
      this.addListener(this.$toggleBtn, 'click', 'edit');
      if (this.$container.hasClass('option-enabled')) {
        if (this.$container.data('modal')) {
          return this.$editFormOption.removeClass('hidden');
        }
      }
    },
    editFormOption: function(e) {
      e.preventDefault();
      this.editing = true;
      if (this.type === 'terms') {
        if (!this.modal) {
          return this.modal = new FormTermsOptionModal(this);
        } else {
          if (this.$formOptionInput) {
            this.modal.$modalLabelInput.val(this.$formOptionInput.val());
          }
          if (this.$formOptionInputTwo) {
            this.modal.$modalCopyInput.val(this.$formOptionInputTwo.val());
          }
          return this.modal.show();
        }
      } else {
        if (!this.modal) {
          return this.modal = new FormOptionModal(this);
        } else {
          if (this.$formOptionInput) {
            this.modal.$formOptionModalInput.val(this.$formOptionInput.val());
          }
          this.modal.show();
          return this.modal.$formOptionModalField.removeClass('error');
        }
      }
    },
    edit: function(e) {
      this.editing = false;
      e.preventDefault();
      if (this.$container.hasClass('option-enabled')) {
        this.$editFormOption.addClass('hidden');
        this.$container.removeClass('option-enabled');
        this.$enableFormOption.val(false);
        this.$enableFormOption.prop('checked', false);
        this.$formOptionResultHtml.addClass('hidden');
        this.$formOptionResultHtml.find('.result-container').html('');
        if (this.$container.data('modal') && this.modal) {
          if (this.type === 'terms') {
            this.modal.$modalLabelInput.val('');
            this.modal.$modalCopyInput.val('');
          } else {
            this.modal.$formOptionModalInput.val('');
          }
        }
        return this.$toggleBtn.html('ENABLE');
      } else {
        this.$editFormOption.removeClass('hidden');
        this.$container.addClass('option-enabled');
        this.$enableFormOption.val(true);
        this.$enableFormOption.prop('checked', true);
        this.$toggleBtn.html('DISABLE');
        if (this.$container.data('modal')) {
          if (this.type === 'terms') {
            if (!this.modal) {
              return this.modal = new FormTermsOptionModal(this);
            } else {
              return this.modal.show();
            }
          } else {
            if (!this.modal) {
              return this.modal = new FormOptionModal(this);
            } else {
              this.modal.$formOptionModalField.removeClass('error');
              return this.modal.show();
            }
          }
        }
      }
    },
    updateHtmlFromModal: function() {
      if (this.type === 'terms') {
        return this.updateTermsHtmlFromModal();
      } else {
        return this.updateSingleHtmlFromModal();
      }
    },
    updateTermsHtmlFromModal: function() {
      var $code, $codeTwo, $input, $textarea, formCopyResult, formLabelResult;
      formLabelResult = this.modal.$modalLabelInput.val();
      formCopyResult = this.modal.$modalCopyInput.val();
      this.$formOptionResultHtml.removeClass('hidden');
      $code = "<code>Label: " + formLabelResult + "</code><br />";
      $input = "<input type='text' class='form-option-input hidden' name='" + this.name + "' value='" + formLabelResult + "' />";
      $codeTwo = "<code class='inline-copy'>Copy: " + formCopyResult + "</code>";
      $textarea = "<textarea type='text' class='form-option-textarea hidden' name='" + this.nameTwo + "'>" + formCopyResult + "</textarea>";
      this.$formOptionInput = $($input);
      this.$formOptionInputTwo = $($textarea);
      this.$formOptionInput.val(formLabelResult);
      this.$formOptionInputTwo.val(formCopyResult);
      return this.$formOptionResultHtml.find('.result-container').html($code + $input + $codeTwo + $textarea);
    },
    updateSingleHtmlFromModal: function() {
      var $code, $input, formOptionResultText;
      formOptionResultText = this.modal.$formOptionModalInput.val();
      this.$formOptionResultHtml.removeClass('hidden');
      $code = "<code>" + formOptionResultText + "</code>";
      $input = "<input type='text' class='form-option-input hidden' name='" + this.name + "' value='" + formOptionResultText + "' />";
      this.$formOptionInput = $($input);
      this.$formOptionInput.val(formOptionResultText);
      return this.$formOptionResultHtml.find('.result-container').html($code + $input);
    }
  });
  FormTermsOptionModal = Garnish.Modal.extend({
    option: null,
    $form: null,
    $modalLabelField: null,
    $modalCopyField: null,
    init: function(option) {
      var $input, $textarea, $textareaRedactor, body, self;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$container.data('modal-title'), '</span>', '<div class="instructions">', option.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field field-input">', '<div class="input-hint">', option.$container.data('input-hint-input'), '</div>', '</div>', '<div class="fb-field field-textarea">', '<div class="input-hint">', option.$container.data('input-hint-textarea'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input = '<input type="text" class="text code form-option-modal-input" size="50">';
      $textarea = '<textarea class="text form-option-modal-textarea" id="termsAndConditionsCopy" rows="10"></textarea>';
      this.$modalLabelField = this.$form.find('.field-input');
      this.$modalCopyField = this.$form.find('.field-textarea');
      this.$modalLabelField.append($input);
      this.$modalCopyField.append($textarea);
      self = this;
      $textareaRedactor = $('#termsAndConditionsCopy').redactor({
        maxHeight: 160,
        minHeight: 150,
        maxWidth: '400px',
        buttons: ['bold', 'italic', 'link', 'horizontalrule'],
        plugins: ['alignment', 'inlinestyle'],
        callbacks: {
          init: function() {
            if (self.option.$formOptionInputTwo) {
              return this.insert.set(self.option.$formOptionInputTwo.val());
            }
          }
        }
      });
      this.show();
      this.$modalLabelInput = body.find('.form-option-modal-input');
      this.$modalCopyInput = body.find('.form-option-modal-textarea');
      setTimeout($.proxy((function() {
        return this.$modalLabelInput.focus();
      }), this), 100);
      if (this.option.$formOptionInput) {
        this.$modalLabelInput.val(this.option.$formOptionInput.val());
      }
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      return this.addListener(this.$form, 'submit', 'save');
    },
    cancel: function() {
      if (!this.option.editing) {
        this.option.$editFormOption.addClass('hidden');
        this.option.$container.removeClass('option-enabled');
        this.option.$enableFormOption.val('');
        this.option.$enableFormOption.prop('checked', false);
        if (this.option.$formOptionInput) {
          this.option.$formOptionInput.val('');
        }
        this.option.$formOptionResultHtml.html('');
        this.option.$toggleBtn.html('ENABLE');
        return this.closeModal();
      } else {
        return this.closeModal();
      }
    },
    hide: function() {
      return this.cancel();
    },
    closeModal: function(ev) {
      this.disable();
      if (ev) {
        ev.stopPropagation();
      }
      if (this.$container) {
        this.$container.velocity('fadeOut', {
          duration: Garnish.FX_DURATION
        });
        this.$shade.velocity('fadeOut', {
          duration: Garnish.FX_DURATION,
          complete: $.proxy(this, 'onFadeOut')
        });
        if (this.settings.hideOnShadeClick) {
          this.removeListener(this.$shade, 'click');
        }
        this.removeListener(Garnish.$win, 'resize');
      }
      this.visible = false;
      Garnish.Modal.visibleModal = null;
      if (this.settings.hideOnEsc) {
        Garnish.escManager.unregister(this);
      }
      this.trigger('hide');
      return this.settings.onHide();
    },
    save: function(e) {
      var data;
      e.preventDefault();
      data = {
        labelResut: this.$modalLabelInput.val(),
        copyResult: this.$modalCopyInput.val()
      };
      console.log(data);
      if (!data.labelResut && !data.copyResult) {
        this.$modalLabelField.addClass('error');
        this.$modalCopyField.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.closeModal();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.option.$container.data('modal-success-message'));
      }
    }
  });
  FormOptionModal = Garnish.Modal.extend({
    option: null,
    $form: null,
    $formOptionModalInput: null,
    $formOptionModalField: null,
    init: function(option) {
      var $input, body, inputOptions;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$container.data('modal-title'), '</span>', '<div class="instructions">', option.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field">', '<div class="input-hint">', option.$container.data('input-hint'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input = '<input type="text" class="text code form-option-modal-input" size="50">';
      if (option.$container.data('input-type') === 'select') {
        $input = $('<div class="select"><select class="form-option-modal-input" /></div>');
        inputOptions = option.$container.data('input-options');
        $.each(inputOptions, function(i, item) {
          return $input.find('select').append($('<option>', {
            value: item.value,
            text: item.label
          }));
        });
      }
      this.$form.find('.fb-field').append($input);
      this.show();
      this.$formOptionModalInput = body.find('.form-option-modal-input');
      setTimeout($.proxy((function() {
        return this.$formOptionModalInput.focus();
      }), this), 100);
      if (this.option.$formOptionInput) {
        this.$formOptionModalInput.val(this.option.$formOptionInput.val());
      }
      this.$formOptionModalField = body.find('.fb-field');
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      return this.addListener(this.$form, 'submit', 'save');
    },
    cancel: function() {
      if (!this.option.editing) {
        this.option.$editFormOption.addClass('hidden');
        this.option.$container.removeClass('option-enabled');
        this.option.$enableFormOption.val('');
        this.option.$enableFormOption.prop('checked', false);
        if (this.option.$formOptionInput) {
          this.option.$formOptionInput.val('');
        }
        this.option.$formOptionResultHtml.html('');
        this.option.$toggleBtn.html('ENABLE');
        return this.closeModal();
      } else {
        return this.closeModal();
      }
    },
    hide: function() {
      return this.cancel();
    },
    closeModal: function(ev) {
      this.disable();
      if (ev) {
        ev.stopPropagation();
      }
      if (this.$container) {
        this.$container.velocity('fadeOut', {
          duration: Garnish.FX_DURATION
        });
        this.$shade.velocity('fadeOut', {
          duration: Garnish.FX_DURATION,
          complete: $.proxy(this, 'onFadeOut')
        });
        if (this.settings.hideOnShadeClick) {
          this.removeListener(this.$shade, 'click');
        }
        this.removeListener(Garnish.$win, 'resize');
      }
      this.visible = false;
      Garnish.Modal.visibleModal = null;
      if (this.settings.hideOnEsc) {
        Garnish.escManager.unregister(this);
      }
      this.trigger('hide');
      return this.settings.onHide();
    },
    save: function(e) {
      var data;
      e.preventDefault();
      data = {
        formOptionResultText: this.$formOptionModalInput.val()
      };
      if (!data.formOptionResultText) {
        this.$formOptionModalField.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.closeModal();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.option.$container.data('modal-success-message'));
      }
    }
  });
}

$(document).ready(function() {
  return $('.option-item').each(function(i, el) {
    return new FormOption(el);
  });
});
