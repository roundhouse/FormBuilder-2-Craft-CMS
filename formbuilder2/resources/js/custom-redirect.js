var CustomRedirect, CustomRedirectModal;

if ($ && window.Garnish) {
  CustomRedirect = Garnish.Base.extend({
    $container: null,
    $enableCustomRedirectInput: null,
    $customRedirectInput: null,
    $templatePathResult: null,
    $toggleBtn: null,
    $editPath: null,
    modal: null,
    init: function(container) {
      this.$container = $(container);
      this.$enableCustomRedirectInput = this.$container.find('#input-option-redirect');
      this.$templatePathResult = this.$container.find('.option-result');
      this.$customRedirectInput = this.$container.find('#customRedirectUrl');
      this.$toggleBtn = this.$container.find('.toggle-option');
      this.$editPath = this.$container.find('.option-edit');
      this.addListener(this.$toggleBtn, 'click', 'edit');
      this.addListener(this.$editPath, 'click', 'editPath');
      if (this.$container.hasClass('option-enabled')) {
        return this.$editPath.removeClass('hidden');
      }
    },
    editPath: function(e) {
      e.preventDefault();
      if (!this.modal) {
        return this.modal = new CustomRedirectModal(this);
      } else {
        return this.modal.show();
      }
    },
    edit: function(e) {
      e.preventDefault();
      if (this.$container.hasClass('option-enabled')) {
        this.$editPath.addClass('hidden');
        this.$container.removeClass('option-enabled');
        this.$enableCustomRedirectInput.prop('checked', false);
        this.$customRedirectInput.val('');
        this.$templatePathResult.addClass('hidden');
        this.modal.$customRedirectInputModal.val('');
        return this.$toggleBtn.html('ENABLE');
      } else {
        this.$editPath.removeClass('hidden');
        this.$container.addClass('option-enabled');
        this.$enableCustomRedirectInput.prop('checked', true);
        this.$toggleBtn.html('DISABLE');
        if (!this.modal) {
          return this.modal = new CustomRedirectModal(this);
        } else {
          return this.modal.show();
        }
      }
    },
    updateHtmlFromModal: function() {
      var redirectUrl;
      redirectUrl = this.modal.$customRedirectInputModal.val();
      this.$customRedirectInput.val(redirectUrl);
      this.$templatePathResult.removeClass('hidden');
      return this.$templatePathResult.find('code').html(redirectUrl);
    }
  });
  CustomRedirectModal = Garnish.Modal.extend({
    option: null,
    $customRedirectInputModal: null,
    init: function(option) {
      var body;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal" id="custom-redirect-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', Craft.t('Custom Redirect URL'), '</span>', '</header>', '<div class="body">', '<input type="text" class="text code input-customredirecturl" size="50">', '<div class="path-text">PATH</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      this.show();
      this.$customRedirectInputModal = body.find('.input-customredirecturl');
      this.$customRedirectInputModal.val(this.option.$customRedirectInput.val());
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'hide');
      return this.addListener(this.$form, 'submit', 'onFormSubmit');
    },
    onFormSubmit: function(e) {
      var data;
      e.preventDefault();
      data = {
        customRedirectUrl: this.$customRedirectInputModal.val()
      };
      if (!data.customRedirectUrl) {
        this.$customRedirectInputModal.addClass('error');
        return Garnish.shake(this.$container);
      } else {
        this.option.updateHtmlFromModal();
        this.hide();
        return Craft.cp.displayNotice(Craft.t('Custom Redirect Updated'));
      }
    }
  });
}
