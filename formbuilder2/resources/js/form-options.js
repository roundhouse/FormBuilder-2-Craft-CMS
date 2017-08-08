var FormOption, FormOptionModal;

if ($ && window.Garnish) {
  FormOption = Garnish.Base.extend({
    $container: null,
    $enableFormOption: null,
    $formOptionInput: null,
    $formOptionResultHtml: null,
    $toggleBtn: null,
    $editFormOption: null,
    modal: null,
    editing: false,
    init: function(container) {
      this.$container = $(container);
      this.$enableFormOption = this.$container.find('.enable-form-option');
      this.$formOptionInput = this.$container.find('.form-option-input');
      this.$formOptionResultHtml = this.$container.find('.option-result');
      this.$editFormOption = this.$container.find('.option-edit');
      this.$toggleBtn = this.$container.find('.toggle-option');
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
      if (!this.modal) {
        return this.modal = new FormOptionModal(this);
      } else {
        this.modal.show();
        return this.modal.$formOptionModalInput.removeClass('error');
      }
    },
    edit: function(e) {
      this.editing = false;
      e.preventDefault();
      if (this.$container.hasClass('option-enabled')) {
        this.$editFormOption.addClass('hidden');
        this.$container.removeClass('option-enabled');
        this.$enableFormOption.val('');
        this.$enableFormOption.prop('checked', false);
        this.$formOptionInput.val('');
        this.$formOptionResultHtml.addClass('hidden');
        if (this.$container.data('modal') && this.modal) {
          this.modal.$formOptionModalInput.val('');
        }
        return this.$toggleBtn.html('ENABLE');
      } else {
        this.$editFormOption.removeClass('hidden');
        this.$container.addClass('option-enabled');
        this.$enableFormOption.val('1');
        this.$enableFormOption.prop('checked', true);
        this.$toggleBtn.html('DISABLE');
        if (this.$container.data('modal')) {
          if (!this.modal) {
            return this.modal = new FormOptionModal(this);
          } else {
            return this.modal.show();
          }
        }
      }
    },
    updateHtmlFromModal: function() {
      var formOptionResultText;
      formOptionResultText = this.modal.$formOptionModalInput.val();
      this.$formOptionInput.val(formOptionResultText);
      this.$formOptionResultHtml.removeClass('hidden');
      return this.$formOptionResultHtml.find('code').html(formOptionResultText);
    }
  });
  FormOptionModal = Garnish.Modal.extend({
    option: null,
    $formOptionModalInput: null,
    init: function(option) {
      var $input, body, inputOptions;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$container.data('modal-title'), '</span>', '<div class="instructions">', option.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="path-text">', option.$container.data('input-hint'), '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
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
      this.$form.find('.body').append($input);
      this.show();
      this.$formOptionModalInput = body.find('.form-option-modal-input');
      this.$formOptionModalInput.val(this.option.$formOptionInput.val());
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
        this.option.$formOptionInput.val('');
        this.option.$formOptionResultHtml.addClass('hidden');
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
        this.$formOptionModalInput.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.closeModal();
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
