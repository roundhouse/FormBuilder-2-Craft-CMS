var EmailTemplate, TemplateOption, TemplateOptionModal;

if ($ && window.Garnish) {
  TemplateOption = Garnish.Base.extend({
    $container: null,
    $enableFormOption: null,
    $toggleBtn: null,
    $editBtn: null,
    $template: null,
    editing: false,
    type: null,
    name: null,
    init: function(el, template) {
      this.$template = template;
      this.$container = $(el);
      this.$enableTemplateContent = this.$container.find('.enable-template-content');
      this.$toggleBtn = this.$container.find('.toggle-option');
      this.$editBtn = this.$container.find('.option-edit');
      this.type = this.$container.data('type');
      this.name = this.$container.data('name');
      this.addListener(this.$toggleBtn, 'click', 'edit');
      return this.addListener(this.$editBtn, 'click', 'editContent');
    },
    editContent: function(e) {
      e.preventDefault();
      this.editing = true;
      if (!this.modal) {
        return this.modal = new TemplateOptionModal(this);
      } else {
        if (this.$template.$CopyInput) {
          this.modal.$modalCopyInput.val(this.$template.$CopyInput.val());
        }
        this.modal.show();
        return this.modal.$modalCopyInput.removeClass('error');
      }
    },
    edit: function(e) {
      this.editing = false;
      e.preventDefault();
      if (this.$container.hasClass('option-enabled')) {
        this.$editBtn.addClass('hidden');
        this.$container.removeClass('option-enabled');
        this.$toggleBtn.html('ENABLE');
        this.$enableTemplateContent.val(false);
        return this.$enableTemplateContent.prop('checked', false);
      } else {
        this.$editBtn.removeClass('hidden');
        this.$container.addClass('option-enabled');
        this.$toggleBtn.html('DISABLE');
        this.$enableTemplateContent.val(true);
        this.$enableTemplateContent.prop('checked', true);
        if (this.type === 'header') {
          this.$template.$headerHtml.removeClass('hidden');
        } else if (this.type === 'body') {
          this.$template.$bodyHtml.removeClass('hidden');
        } else if (this.type === 'footer') {
          this.$template.$footerHtml.removeClass('hidden');
        }
        if (this.$container.data('modal')) {
          if (!this.modal) {
            return this.modal = new TemplateOptionModal(this);
          } else {
            return this.modal.show();
          }
        }
      }
    },
    updateHtmlFromModal: function() {
      var copy, input;
      console.log(this.type);
      copy = this.modal.$modalCopyInput.val();
      input = "<textarea type='text' class='template-input-textarea hidden' name='" + this.name + "'>" + copy + "</textarea>";
      if (this.type === 'header') {
        return this.$template.$headerHtml.html(copy + input);
      } else if (this.type === 'body') {
        return this.$template.$bodyHtml.html(copy + input);
      } else if (this.type === 'footer') {
        return this.$template.$footerHtml.html(copy + input);
      }
    }
  });
  TemplateOptionModal = Garnish.Modal.extend({
    option: null,
    $form: null,
    $modalLabelField: null,
    $modalCopyField: null,
    init: function(option) {
      var $textarea, $textareaRedactor, body, self;
      this.option = option;
      this.base();
      this.$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod);
      this.setContainer(this.$form);
      body = $(['<header>', '<span class="modal-title">', option.$container.data('modal-title'), '</span>', '</header>', '<div class="body">', '<div class="fb-field field-textarea">', '<div class="input-hint">', option.$container.data('input-hint-textarea'), '</div>', '</div>', '</div>', '<footer class="footer">', '<div class="buttons">', '<input type="button" class="btns btn-modal cancel" value="' + Craft.t('Cancel') + '">', '<input type="submit" class="btns btn-modal submit" value="' + Craft.t('Save') + '">', '</div>', '</footer>'].join('')).appendTo(this.$form);
      $textarea = '<textarea class="text form-option-modal-textarea" id="' + this.option.type + '-textarea" rows="10"></textarea>';
      this.$modalCopyField = this.$form.find('.field-textarea');
      this.$modalCopyField.append($textarea);
      self = this;
      $textareaRedactor = $('#' + this.option.type + '-textarea').redactor({
        maxHeight: 160,
        minHeight: 150,
        maxWidth: '500px',
        buttons: ['bold', 'italic', 'link', 'horizontalrule'],
        plugins: ['fontfamily', 'fontsize', 'alignment', 'fontcolor'],
        callbacks: {
          init: function() {
            if (self.option.$formOptionInputTwo) {
              return this.insert.set(self.option.$formOptionInputTwo.val());
            }
          }
        }
      });
      this.show();
      this.$modalCopyInput = body.find('.form-option-modal-textarea');
      setTimeout($.proxy((function() {
        return this.$modalCopyInput.focus();
      }), this), 100);
      this.$saveBtn = body.find('.submit');
      this.$cancelBtn = body.find('.cancel');
      this.addListener(this.$cancelBtn, 'click', 'cancel');
      return this.addListener(this.$form, 'submit', 'save');
    },
    cancel: function() {
      if (!this.option.editing) {
        this.option.$editBtn.addClass('hidden');
        this.option.$container.removeClass('option-enabled');
        this.option.$enableFormOption.val('');
        this.option.$enableFormOption.prop('checked', false);
        this.option.$toggleBtn.html('ENABLE');
        this.option.$template.$headerHtml.addClass('hidden');
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
        copyResult: this.$modalCopyInput.val()
      };
      console.log(data);
      if (!data.copyResult) {
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
  EmailTemplate = Garnish.Base.extend({
    $container: null,
    $headerHtml: null,
    $bodyHtml: null,
    $footerHtml: null,
    $headerCopyInput: null,
    $bodyCopyInput: null,
    $footerCopyInput: null,
    init: function(el) {
      this.$container = $(el);
      this.$headerHtml = this.$container.find('.template-header');
      this.$bodyHtml = this.$container.find('.template-body');
      this.$footerHtml = this.$container.find('.template-footer');
      this.$headerCopyInput = this.$container.find('#header-copy-input');
      this.$bodyCopyInput = this.$container.find('#body-copy-input');
      return this.$footerCopyInput = this.$container.find('#footer-copy-input');
    }
  });
}

$(document).ready(function() {
  var template, templateContainerHtml;
  template = new EmailTemplate('#template-minimum-html');
  $('.template-item').each(function(i, el) {
    return new TemplateOption(el, template);
  });
  templateContainerHtml = $('.template-container');
  $('#templateBackgroundColor').on('change', function(e) {
    var color;
    color = $(this).val();
    return templateContainerHtml.css('backgroundColor', color);
  });
  $('#templateBorderColor').on('change', function(e) {
    var color;
    color = $(this).val();
    return templateContainerHtml.css('borderColor', color);
  });
  $('#templateBorderWidth').on('change input', function(e) {
    var width;
    width = $(this).val();
    return templateContainerHtml.css('borderWidth', width + 'px');
  });
  $('#templateBorderRadius').on('change input', function(e) {
    var radius;
    radius = $(this).val();
    return templateContainerHtml.css('borderRadius', radius + 'px');
  });
  $('#templateContainerPadding').on('change input', function(e) {
    var padding;
    padding = $(this).val();
    return templateContainerHtml.css('padding', padding + 'px');
  });
  $('.delete-template').on('click', function(e) {
    var data, templateId;
    e.preventDefault();
    templateId = $(this).data('id');
    data = {
      id: templateId
    };
    if (confirm(Craft.t("Are you sure you want to delete this template?"))) {
      return Craft.postActionRequest('formBuilder2/template/deleteTemplate', data, $.proxy((function(response, textStatus) {
        if (textStatus === 'success') {
          return window.location.href = '/admin/formbuilder2/templates';
        }
      }), this));
    }
  });
  return $('.template-actions').each(function(index, value) {
    var $menu, templateHandle, templateId, templateName;
    templateId = $(value).data('template-id');
    templateHandle = $(value).data('template-handle');
    templateName = $(value).data('template-name');
    $menu = $('<div class="template"/>').html('<ul class="action-item-menu">' + '<li>' + '<a href="#" class="copy-handle" data-clipboard-text="' + templateHandle + '">' + 'Copy Handle' + '</a>' + '</li>' + '<li>' + '<a href="#" class="delete">' + 'Delete</a>' + '</li>' + '</ul>');
    $(value).on('click', function(e) {
      var formbuilderTemplate;
      e.preventDefault();
      return formbuilderTemplate = new Garnish.HUD($(value).find('.template-action-trigger'), $menu, {
        hudClass: 'hud fb-hud formhud',
        closeOtherHUDs: false
      });
    });
    $menu.find('.copy-handle').on('click', function(e) {
      var hudID;
      e.preventDefault();
      new Clipboard('.copy-handle', {
        text: function(trigger) {
          return templateHandle;
        }
      });
      for (hudID in Garnish.HUD.activeHUDs) {
        Garnish.HUD.activeHUDs[hudID].hide();
      }
      return Craft.cp.displayNotice(Craft.t('Form Handle Copied'));
    });
    return $menu.find('.delete').on('click', function(e) {
      var data;
      e.preventDefault();
      data = {
        id: templateId
      };
      if (confirm(Craft.t("Are you sure you want to delete " + templateName + "?"))) {
        return Craft.postActionRequest('formBuilder2/template/deleteTemplate', data, $.proxy((function(response, textStatus) {
          var $row, hudID, results;
          if (response.success) {
            $row = $('#formbuilder-template-' + templateId);
            templateTable.sorter.removeItems($row);
            $row.remove();
            if (response.count === 1) {
              $('.templates-table').remove();
              $('.templates-container').after('<div class="no-templates" id="notemplates"><span class="title">Hello! You don\'t have any templates yet.</span></div>');
            }
            results = [];
            for (hudID in Garnish.HUD.activeHUDs) {
              Garnish.HUD.activeHUDs[hudID].hide();
              results.push(Craft.cp.displayNotice(Craft.t('Template Deleted')));
            }
            return results;
          }
        }), this));
      }
    });
  });
});
