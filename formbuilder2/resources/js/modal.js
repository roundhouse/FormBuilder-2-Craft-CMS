var Modal;

if ($ && window.Garnish) {
  Modal = Garnish.Modal.extend({
    option: null,
    $form: null,
    $modalInputs: null,
    $redactor: null,
    init: function(option) {
      var body, fields, self;
      self = this;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$data.title, '</span>', '<div class="instructions">', option.$data.instructions, '</div>', '</header>', '<div class="body"></div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $.each(option.$inputs, function(i, item) {
        var $input, className;
        if (item.type !== 'checkbox') {
          className = item.name.replace(/[_\W]+/g, "-").slice(0, -1);
          if (item.type === 'text') {
            $input = "<input type='" + item.type + "' class='" + className + "' value='" + item.value + "' data-hint='" + item.hint + "' data-name='" + item.name + "' />";
          }
          if (item.type === 'textarea') {
            $input = "<textarea class='" + className + "' value='" + item.value + "' data-hint='" + item.hint + "' data-name='" + item.name + "' /></textarea>";
          }
          if (item.type === 'select') {
            $input = $.parseJSON(item.options);
          }
          return self.renderInputs($input, item.value, item.type, item.name, item.hint, className);
        }
      });
      if (this.option.$container.hasClass('has-fields')) {
        fields = new Fields(this.option, this.$form);
      }
      this.$modalInputs = this.$form.find('.body').find('input, textarea, select');
      this.show();
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      return this.addListener(this.$form, 'submit', 'save');
    },
    renderInputs: function(el, value, type, name, hint, className) {
      var $input;
      if (type === 'select') {
        $input = $('<div class="fb-field">' + '<div class="input-hint">' + hint + '</div>' + '<div class="select"><select class=' + className + ' data-hint=' + hint + ' data-name=' + name + ' /></div>' + '</div>');
        $.each(el, function(i, item) {
          return $input.find('select').append($('<option>', {
            value: item.value,
            text: item.label
          }));
        });
        $input.find('select').val(value);
      } else {
        $input = $('<div class="fb-field">' + '<div class="input-hint">' + hint + '</div>' + el + '</div>');
      }
      this.$form.find('.body').append($input);
      if (type === 'textarea') {
        return this.initRedactor(el);
      }
    },
    initRedactor: function(item) {
      var className, el;
      className = $(item)[0].className;
      el = this.$form.find("." + className);
      el.redactor({
        maxHeight: 160,
        minHeight: 150,
        maxWidth: '400px',
        buttons: ['bold', 'italic', 'link', 'horizontalrule'],
        plugins: ['fontfamily', 'fontsize', 'alignment', 'fontcolor'],
        callbacks: {
          init: function() {
            return console.log('init');
          }
        }
      });
      return this.$redactor = el.redactor('core.object');
    },
    cancel: function() {
      if (!this.option.editing) {
        this.option.$edit.addClass('hidden');
        this.option.$container.removeClass('option-enabled');
        this.option.$resultContainer.html('');
        this.option.$toggle.html('ENABLE');
        this.disableOption();
        return this.closeModal();
      } else {
        return this.closeModal();
      }
    },
    disableOption: function() {
      if (this.option.$enableCheckbox) {
        this.option.$enableCheckbox.val('');
        return this.option.$enableCheckbox.prop('checked', false);
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
      var errors;
      e.preventDefault();
      errors = [];
      $.each(this.$modalInputs, function(i, item) {
        if ($(item).val() === '') {
          return errors[i] = item;
        }
      });
      if (errors.length > 0) {
        $.each(errors, function(i, item) {
          return $(item).parent().addClass('error');
        });
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.closeModal();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.option.$data.successMessage);
      }
    }
  });
}
