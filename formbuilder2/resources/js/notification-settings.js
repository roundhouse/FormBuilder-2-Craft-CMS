var NotificationSettingsItem, SettingsItemModal;

if ($ && window.Garnish) {
  NotificationSettingsItem = Garnish.Base.extend({
    $container: null,
    $settingInput: null,
    $settingResultHtml: null,
    $editSetting: null,
    modal: null,
    editing: false,
    init: function(container) {
      this.$container = $(container);
      this.$settingInput = this.$container.find('.settings-input');
      this.$settingResultHtml = this.$container.find('.settings-result');
      this.$editSetting = this.$container.find('.settings-edit');
      return this.addListener(this.$editSetting, 'click', 'editSettings');
    },
    editSettings: function(e) {
      e.preventDefault();
      if (!this.modal) {
        return this.modal = new SettingsItemModal(this);
      } else {
        return this.modal.show();
      }
    },
    updateHtmlFromModal: function() {
      var settingsResultText;
      settingsResultText = this.modal.$settingsModalInput.val();
      this.$settingInput.val(settingsResultText);
      this.$settingResultHtml.removeClass('hidden');
      return this.$settingResultHtml.find('code').html(settingsResultText);
    }
  });
  SettingsItemModal = Garnish.Modal.extend({
    option: null,
    $settingsModalInput: null,
    init: function(option) {
      return console.log(this);
    },
    init: function(option) {
      var $input, body, inputOptions;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$container.data('modal-title'), '</span>', '<div class="instructions">', option.$container.data('modal-instructions'), '</div>', '</header>', '<div class="body">', '<div class="path-text">', option.$container.data('input-hint'), '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $input = '<input type="text" class="text code settings-modal-input" size="50">';
      if (option.$container.data('input-type') === 'select') {
        $input = $('<div class="select"><select class="settings-modal-input" /></div>');
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
      this.$settingsModalInput = body.find('.settings-modal-input');
      this.$settingsModalInput.val(this.option.$settingInput.val());
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
        this.$settingsModalInput.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.hide();
        return Craft.cp.displayNotice(this.option.$container.data('modal-success-message'));
      }
    }
  });
}
