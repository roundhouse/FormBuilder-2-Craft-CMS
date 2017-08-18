var CustomSubjectModal, MultipleItemModal, NotificationSettingsItem, ResultItem, SingleItemModal, TemplateModal;

if ($ && window.Garnish) {
  NotificationSettingsItem = Garnish.Base.extend({
    $container: null,
    $settingInput: null,
    $settingResultHtml: null,
    $editSetting: null,
    $toggleSettings: null,
    type: null,
    name: null,
    nameOne: null,
    nameTwo: null,
    modal: null,
    editing: false,
    init: function(container) {
      this.$container = $(container);
      this.$settingInput = this.$container.find('.form-settings-input');
      this.$settingResultHtml = this.$container.find('.settings-result');
      this.$editSetting = this.$container.find('.settings-edit');
      this.$toggleSettings = this.$container.find('.toggle-settings');
      this.type = this.$container.data('type');
      this.name = this.$container.data('name');
      this.nameOne = this.$container.data('nameOne');
      this.nameTwo = this.$container.data('nameTwo');
      this.addListener(this.$editSetting, 'click', 'editSettings');
      this.addListener(this.$toggleSettings, 'click', 'toggle');
      this.addListener(this.$editSingleSetting, 'click', 'editSettings');
      if (this.$container.hasClass('settings-enabled')) {
        if (this.$container.data('modal')) {
          return this.$editSetting.removeClass('hidden');
        }
      }
    },
    toggle: function(e) {
      this.editing = false;
      e.preventDefault();
      if (this.$container.hasClass('settings-enabled')) {
        this.$editSetting.addClass('hidden');
        this.$container.removeClass('settings-enabled');
        this.$settingInput.val('');
        this.$settingInput.prop('checked', false);
        this.$settingResultHtml.addClass('hidden');
        this.$toggleSettings.html('ENABLE');
        if (this.$container.data('modal') && this.modal) {
          if (this.type === 'select') {
            this.modal.$inputValueOne.val('');
            return this.modal.$inputValueTwo.val('');
          } else {
            return this.modal.$settingsModalInput.val('');
          }
        }
      } else {
        this.$editSetting.removeClass('hidden');
        this.$container.addClass('settings-enabled');
        this.$settingInput.val('1');
        this.$settingInput.prop('checked', true);
        this.$toggleSettings.html('DISABLE');
        if (this.$container.data('modal')) {
          if (this.type === 'select') {
            if (!this.modal) {
              return this.modal = new CustomSubjectModal(this);
            } else {
              if (this.modal.$fieldOne) {
                this.modal.$fieldOne.removeClass('error');
                this.modal.$fieldTwo.removeClass('error');
              }
              return this.modal.show();
            }
          } else {
            if (!this.modal) {
              return this.modal = new SingleItemModal(this);
            } else {
              if (this.modal.$fieldOne) {
                this.modal.$fieldOne.removeClass('error');
              }
              return this.modal.show();
            }
          }
        }
      }
    },
    editSettings: function(e) {
      e.preventDefault();
      if (this.type === 'multiple') {
        if (!this.modal) {
          return this.modal = new MultipleItemModal(this);
        } else {
          return this.modal.show();
        }
      } else if (this.type === 'template') {
        if (!this.modal) {
          this.editing = true;
          this.modal = new TemplateModal(this);
          return this.modal.$inputValueOne.val(this.$settingInput.val());
        } else {
          this.modal.show();
          return this.modal.$inputValueOne.val(this.$settingInput.val());
        }
      } else if (this.type === 'select') {
        if (!this.modal) {
          this.modal = new CustomSubjectModal(this);
          if (this.$settingInput.data('type') === 'text') {
            this.editing = true;
            return this.modal.$inputValueOne.val(this.$settingInput.val());
          } else {
            this.editing = true;
            return this.modal.$inputValueTwo.val(this.$settingInput.val());
          }
        } else {
          if (this.modal.type === 'text') {
            this.modal.$inputValueOne.val(this.$settingInput.val());
          } else {
            this.modal.$inputValueTwo.val(this.$settingInput.val());
          }
          return this.modal.show();
        }
      } else {
        if (!this.modal) {
          return this.modal = new SingleItemModal(this);
        } else {
          this.modal.$fieldOne.removeClass('error');
          if (this.$settingInput) {
            this.modal.$settingsModalInput.val(this.$settingInput.val());
          }
          return this.modal.show();
        }
      }
    },
    updateHtmlFromModal: function() {
      if (this.modal.item.type === 'multiple') {
        return this.updateMultipleHtmlFromModal();
      } else if (this.modal.item.type === 'attachments') {
        return this.updateAttachmentsHtmlFromModal();
      } else if (this.modal.item.type === 'select') {
        return this.updateSubjectHtmlFromModal();
      } else if (this.modal.item.type === 'template') {
        return this.updateTemplateHtmlFromModal();
      } else {
        return this.updateSingleHtmlFromModal();
      }
    },
    updateTemplateHtmlFromModal: function() {
      var $code, $input, templateResultHandle;
      templateResultHandle = this.modal.$inputValueOne.val();
      $code = "<code>Template Handle: " + templateResultHandle + "</code>";
      $input = '<input type="hidden" class="form-settings-input" name="' + this.name + '" value="' + templateResultHandle + '" />';
      this.$settingResultHtml.removeClass('hidden');
      this.$settingInput = $($input);
      return this.$settingResultHtml.html($code + $input);
    },
    updateSubjectHtmlFromModal: function() {
      var $code, $input, settingsResultTextOne, settingsResultTextTwo;
      settingsResultTextOne = this.modal.$inputValueOne.val();
      settingsResultTextTwo = this.modal.$inputValueTwo.val();
      this.$settingResultHtml.removeClass('hidden');
      if (this.modal.type === 'text') {
        $code = "<code>Text: " + settingsResultTextOne + "</code>";
        $input = '<input type="hidden" class="form-settings-input" name="' + this.nameOne + '" value="' + settingsResultTextOne + '" />';
      } else {
        $code = "<code>Field: " + settingsResultTextTwo + "</code>";
        $input = '<input type="hidden" class="form-settings-input" name="' + this.nameTwo + '" value="' + settingsResultTextTwo + '" />';
      }
      this.$settingInput = $($input);
      return this.$settingResultHtml.html($code + $input);
    },
    updateAttachmentsHtmlFromModal: function() {
      return console.log('attachments html');
    },
    updateMultipleHtmlFromModal: function() {
      var $resultHtml, body, index, totalResults;
      totalResults = this.modal.item.$settingResultHtml.find('.result-item').length;
      if (totalResults) {
        index = totalResults;
      } else {
        index = 0;
      }
      $resultHtml = $('<div class="result-item" data-result-index="' + index + '">').appendTo(Garnish.$bod);
      body = $(['<div class="settings-result-actions">', '<a href="#" class="settings-result-delete" title="' + Craft.t('Delete') + '"><svg width="19" height="19" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path d="M9.521064 18.5182504c-4.973493 0-9.019897-4.0510671-9.019897-9.030471 0-4.98018924 4.046404-9.0312563 9.019897-9.0312563s9.019897 4.05106706 9.019897 9.0312563c0 4.9794039-4.046404 9.030471-9.019897 9.030471zm0-16.05425785c-3.868359 0-7.015127 3.15021907-7.015127 7.02378685 0 3.8727824 3.146768 7.0237869 7.015127 7.0237869 3.86836 0 7.015127-3.1510045 7.015127-7.0237869 0-3.87356778-3.146767-7.02378685-7.015127-7.02378685zm3.167945 10.02870785c-.196085.1955634-.452564.2937378-.708258.2937378-.256479 0-.512958-.0981744-.709042-.2937378L9.521064 10.739699 7.77042 12.4927004c-.196085.1955634-.452564.2937378-.709043.2937378-.256478 0-.512957-.0981744-.708258-.2937378-.391385-.391912-.391385-1.0272965 0-1.4192086l1.750645-1.7530015-1.750645-1.7530015c-.391385-.391912-.391385-1.02729655 0-1.41920862.391385-.39191207 1.025131-.39191207 1.417301 0L9.521064 7.9012817l1.750645-1.75300152c.391385-.39191207 1.025915-.39191207 1.4173 0 .391385.39191207.391385 1.02729662 0 1.41920862l-1.750644 1.7530015 1.750644 1.7530015c.391385.3919121.391385 1.0272966 0 1.4192086z" fill="#8094A1" fill-rule="evenodd"/></svg></a>', '</div>', '<code class="value-one">' + this.modal.$inputValueOne.val() + '</code>', '<code class="value-two">' + this.modal.$inputValueTwo.val() + '</code>', '<input type="hidden" name="' + this.name + '[' + index + '][key]" value="' + this.modal.$inputValueOne.val() + '" />', '<input type="hidden" name="' + this.name + '[' + index + '][value]" value="' + this.modal.$inputValueTwo.val() + '" />'].join('')).appendTo($resultHtml);
      this.$settingResultHtml.append($resultHtml);
      return new ResultItem($resultHtml, this.modal);
    },
    updateSingleHtmlFromModal: function() {
      var $code, $input, settingsResultText;
      settingsResultText = this.modal.$settingsModalInput.val();
      this.$settingInput.val(settingsResultText);
      this.$settingResultHtml.removeClass('hidden');
      $code = "<code>" + settingsResultText + "</code>";
      $input = '<input type="hidden" class="form-settings-input" name="' + this.name + '" value="' + settingsResultText + '" />';
      this.$settingInput = $($input);
      return this.$settingResultHtml.html($code + $input);
    }
  });
  ResultItem = Garnish.Base.extend({
    $item: null,
    $deleteItemBtn: null,
    modal: null,
    inputValueOne: null,
    inputValueTwo: null,
    init: function(item, modal) {
      this.$item = $(item);
      this.$deleteItemBtn = this.$item.find('.settings-result-delete');
      this.modal = modal;
      this.inputValueOne = this.$item.find('.value-one').text();
      this.inputValueTwo = this.$item.find('.value-two').text();
      return this.addListener(this.$deleteItemBtn, 'click', 'deleteItemSettings');
    },
    deleteItemSettings: function(e) {
      var self;
      e.preventDefault();
      self = this;
      this.$item.addClass('zap');
      return setTimeout((function() {
        self.$item.remove();
        return Craft.cp.displayNotice(Craft.t('Item Removed'));
      }), 300);
    }
  });
  TemplateModal = Garnish.Modal.extend({
    item: null,
    $form: null,
    $inputValueOne: null,
    $fieldOne: null,
    init: function(item) {
      var $input1, body, inputOptions;
      this.item = item;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', item.$container.data('modal-title'), '</span>', '<div class="instructions">', item.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field input-one">', '<div class="input-hint input-hint-one">', item.$container.data('inputHint-one'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input1 = $('<div class="select"><select class="modal-input-one" /></div>');
      inputOptions = item.$container.data('input-options');
      $.each(inputOptions, function(i, item) {
        return $input1.find('select').append($('<option>', {
          value: item.value,
          text: item.label
        }));
      });
      this.$form.find('.body .input-one').append($input1);
      this.show();
      this.$inputValueOne = body.find('.modal-input-one');
      this.$fieldOne = body.find('.input-one');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      return this.addListener(this.$form, 'submit', 'save');
    },
    hide: function(e) {
      return this.cancel();
    },
    cancel: function(e) {
      if (!this.item.editing) {
        this.item.$editSetting.addClass('hidden');
        this.item.$container.removeClass('settings-enabled');
        this.item.$settingInput.val('');
        this.item.$settingInput.prop('checked', false);
        this.item.$settingResultHtml.addClass('hidden');
        this.item.$toggleSettings.html('ENABLE');
        return this.closeModal();
      } else {
        return this.closeModal();
      }
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
        inputValueOne: this.$inputValueOne.val()
      };
      if (!data.inputValueOne) {
        this.$fieldOne.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.item.updateHtmlFromModal();
        this.hide();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.item.$container.data('modal-success-message'));
      }
    }
  });
  CustomSubjectModal = Garnish.Modal.extend({
    item: null,
    $form: null,
    $inputValueOne: null,
    $inputValueTwo: null,
    $fieldOne: null,
    $fieldTwo: null,
    type: null,
    init: function(item) {
      var $input1, $input2, body, inputOptions;
      this.item = item;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', item.$container.data('modal-title'), '</span>', '<div class="instructions">', item.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field input-one">', '<div class="input-hint input-hint-one">', item.$container.data('inputHint-one'), '</div>', '</div>', '<span class="section-or">OR</span>', '<div class="fb-field input-two">', '<div class="input-hint input-hint-two">', item.$container.data('inputHint-two'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input1 = '<input type="text" class="text code modal-input-one" size="50">';
      $input2 = $('<div class="select"><select class="modal-input-two" /></div>');
      inputOptions = item.$container.data('input-options');
      $.each(inputOptions, function(i, item) {
        return $input2.find('select').append($('<option>', {
          value: item.value,
          text: item.label
        }));
      });
      this.$form.find('.body .input-one').append($input1);
      this.$form.find('.body .input-two').append($input2);
      this.show();
      this.$inputValueOne = body.find('.modal-input-one');
      this.$inputValueTwo = body.find('.modal-input-two');
      this.$fieldOne = body.find('.input-one');
      this.$fieldTwo = body.find('.input-two');
      setTimeout($.proxy((function() {
        return this.$inputValueOne.focus();
      }), this), 100);
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      this.addListener(this.$form, 'submit', 'save');
      this.addListener(this.$inputValueOne, 'keyup', 'clearInputTwo');
      return this.addListener(this.$inputValueTwo, 'change', 'clearInputOne');
    },
    clearInputOne: function(e) {
      return this.$inputValueOne.val('');
    },
    clearInputTwo: function(e) {
      return this.$inputValueTwo.val('');
    },
    hide: function(e) {
      return this.cancel();
    },
    cancel: function(e) {
      if (!this.item.editing) {
        this.item.$editSetting.addClass('hidden');
        this.item.$container.removeClass('settings-enabled');
        this.item.$settingInput.val('');
        this.item.$settingInput.prop('checked', false);
        this.item.$settingResultHtml.addClass('hidden');
        this.item.$toggleSettings.html('ENABLE');
        return this.closeModal();
      } else {
        return this.closeModal();
      }
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
        inputValueOne: this.$inputValueOne.val(),
        inputValueTwo: this.$inputValueTwo.val()
      };
      if (!data.inputValueOne && !data.inputValueTwo) {
        this.$fieldOne.addClass('error');
        this.$fieldTwo.addClass('error');
        Garnish.shake(this.$container);
        return this.item.editing = false;
      } else {
        if (data.inputValueTwo) {
          this.type = 'field';
          this.item.editing = true;
        } else {
          this.type = 'text';
          this.item.editing = true;
        }
        this.item.updateHtmlFromModal();
        this.hide();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.item.$container.data('modal-success-message'));
      }
    }
  });
  MultipleItemModal = Garnish.Modal.extend({
    item: null,
    $form: null,
    $inputValueOne: null,
    $inputValueTwo: null,
    $fieldOne: null,
    $fieldTwo: null,
    init: function(item) {
      var $input1, $input2, body;
      this.item = item;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', item.$container.data('modal-title'), '</span>', '<div class="instructions">', item.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field input-one">', '<div class="input-hint input-hint-one">', item.$container.data('inputHint-one'), '</div>', '</div>', '<div class="fb-field input-two">', '<div class="input-hint input-hint-two">', item.$container.data('inputHint-two'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Add') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input1 = '<input type="text" class="text code modal-input-one" size="50">';
      $input2 = '<input type="text" class="text code modal-input-two" size="50">';
      this.$form.find('.body .input-one').append($input1);
      this.$form.find('.body .input-two').append($input2);
      this.show();
      this.$inputValueOne = body.find('.modal-input-one');
      this.$inputValueTwo = body.find('.modal-input-two');
      this.$fieldOne = body.find('.input-one');
      this.$fieldTwo = body.find('.input-two');
      setTimeout($.proxy((function() {
        return this.$inputValueOne.focus();
      }), this), 100);
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'hide');
      return this.addListener(this.$form, 'submit', 'save');
    },
    save: function(e) {
      var data;
      e.preventDefault();
      data = {
        inputValueOne: this.$inputValueOne.val(),
        inputValueTwo: this.$inputValueTwo.val()
      };
      if (!data.inputValueOne) {
        this.$fieldOne.addClass('error');
        return Garnish.shake(this.$container);
      } else if (!data.inputValueTwo) {
        this.$fieldTwo.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.item.updateHtmlFromModal();
        this.hide();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.item.$container.data('modal-success-message'));
      }
    }
  });
  SingleItemModal = Garnish.Modal.extend({
    item: null,
    $form: null,
    $settingsModalInput: null,
    $fieldOne: null,
    init: function(item) {
      var $input, body, inputOptions;
      this.item = item;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', item.$container.data('modal-title'), '</span>', '<div class="instructions">', item.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="fb-field input-one">', '<div class="input-hint">', item.$container.data('input-hint'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input = '<input type="text" class="text code settings-modal-input" size="50">';
      if (item.$container.data('input-type') === 'select') {
        $input = $('<div class="select"><select class="settings-modal-input" /></div>');
        inputOptions = item.$container.data('input-options');
        $.each(inputOptions, function(i, item) {
          return $input.find('select').append($('<option>', {
            value: item.value,
            text: item.label
          }));
        });
      }
      this.$form.find('.fb-field').append($input);
      this.$fieldOne = body.find('.input-one');
      this.show();
      this.$settingsModalInput = body.find('.settings-modal-input');
      setTimeout($.proxy((function() {
        return this.$settingsModalInput.focus();
      }), this), 100);
      if (this.item.$settingInput) {
        this.$settingsModalInput.val(this.item.$settingInput.val());
      }
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'hide');
      return this.addListener(this.$form, 'submit', 'save');
    },
    save: function(e) {
      var data;
      e.preventDefault();
      data = {
        settingsResultText: this.$settingsModalInput.val()
      };
      if (!data.settingsResultText) {
        this.$fieldOne.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.item.updateHtmlFromModal();
        this.hide();
        this.$form[0].reset();
        return Craft.cp.displayNotice(this.item.$container.data('modal-success-message'));
      }
    }
  });
}
