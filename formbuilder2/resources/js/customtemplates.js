(function($) {
  var CustomTemplates, Editor, EditorModal;
  CustomTemplates = {
    setup: function() {}
  };
  if ($ && window.Garnish && window.Craft) {
    CustomTemplates = new (Garnish.Base.extend({
      fields: null,
      labels: null,
      init: function() {
        this.fields = {};
        return this.labels = {};
      },
      setup: function() {
        var FLD, FLD_field, FLD_init, FLD_options;
        if (Craft.FieldLayoutDesigner) {
          FLD = Craft.FieldLayoutDesigner;
          FLD_init = FLD.prototype.init;
          FLD_field = FLD.prototype.initField;
          FLD_options = FLD.prototype.onFieldOptionSelect;
          FLD.prototype.init = function() {
            FLD_init.apply(this, arguments);
            return this.customfield = new window.CustomTemplates.Editor(this);
          };
          FLD.prototype.initField = function($field) {
            var $customfield, $editBtn, $menu, $ul, menu, menuBtn;
            FLD_field.apply(this, arguments);
            $editBtn = $field.find('.settings');
            menuBtn = $editBtn.data('menubtn');
            menu = menuBtn.menu;
            $menu = menu.$container;
            $ul = $menu.children('ul');
            $customfield = $('<li><a data-action="customfield">' + Craft.t('Custom Template') + '</a></li>').appendTo($ul);
            return menu.addOptions($customfield.children('a'));
          };
          return FLD.prototype.onFieldOptionSelect = function(option) {
            var $field, $option, action;
            FLD_options.apply(this, arguments);
            $option = $(option);
            $field = $option.data('menu').$anchor.parent();
            action = $option.data('action');
            switch (action) {
              case 'customfield':
                return this.trigger('customfieldOptionSelected', {
                  target: $option[0],
                  $target: $option,
                  $field: $field,
                  fld: this,
                  id: $field.data('id') | 0
                });
            }
          };
        }
      },
      getFieldInfo: function(id) {
        return this.fields[id];
      },
      getLabelId: function(fieldId, fieldLayoutId) {
        return this.getLabel(fieldId, fieldLayoutId).id;
      },
      getLabel: function(fieldId, fieldLayoutId) {
        var id, label;
        for (id in this.labels) {
          if (this.labels.hasOwnProperty(id)) {
            label = this.labels[id];
            if (label.fieldId === fieldId && label.fieldLayoutId === fieldLayoutId) {
              return label;
            }
          }
        }
        return false;
      },
      getLabelsOnFieldLayout: function(fieldLayoutId) {
        var label, labelId, labels;
        fieldLayoutId = isNaN(fieldLayoutId) ? '' : fieldLayoutId;
        labels = {};
        for (labelId in this.labels) {
          if (this.labels.hasOwnProperty(labelId)) {
            label = this.labels[labelId];
            if (label.fieldLayoutId == fieldLayoutId) {
              labels[labelId] = label;
            }
          }
        }
        return labels;
      }
    }));
    Editor = Garnish.Base.extend({
      fld: null,
      labels: null,
      namespace: 'customfield',
      $form: null,
      init: function(fld) {
        var fieldLayoutId;
        if (!(fld instanceof Craft.FieldLayoutDesigner)) {
          return;
        }
        this.fld = fld;
        this.fld.on('customfieldOptionSelected', $.proxy(this.openModal, this));
        this.labels = {};
        this.$form = this.fld.$container.closest('form');
        fieldLayoutId = this.$form.find('input[name="fieldLayoutId"]').val();
        if (fieldLayoutId !== false) {
          return this.applyLabels(fieldLayoutId);
        }
      },
      applyLabels: function(fieldLayoutId) {
        var initLabels, label, labelId, results;
        initLabels = CustomTemplates.getLabelsOnFieldLayout(fieldLayoutId);
        if (initLabels) {
          results = [];
          for (labelId in initLabels) {
            if (initLabels.hasOwnProperty(labelId)) {
              label = initLabels[labelId];
              results.push(this.setFormData(label.fieldId, label.template));
            } else {
              results.push(void 0);
            }
          }
          return results;
        }
      },
      openModal: function(e) {
        var fieldId, info, label, modal, originalTemplate, that;
        fieldId = e.id;
        info = CustomTemplates.getFieldInfo(fieldId);
        originalTemplate = info && typeof info.name === 'string' ? info.name : '';
        modal = new Editor.Modal(originalTemplate);
        label = this.labels[fieldId];
        that = this;
        modal.on('setLabel', function(f) {
          return that.setFormData(fieldId, f.template);
        });
        return modal.show(label ? label.template : '');
      },
      setFormData: function(fieldId, template) {
        var $container, $field, hasLabel, templateField;
        $container = this.fld.$container;
        $field = $container.find('.fld-field[data-id="' + fieldId + '"]');
        templateField = this.namespace + '[' + fieldId + '][template]';
        $field.children('input[name="' + templateField + '"]').remove();
        if (template) {
          $('<input type="hidden" name="' + templateField + '">').val(template).appendTo($field);
        }
        hasLabel = !!template;
        $field.toggleClass('customfield', hasLabel);
        if (hasLabel) {
          return this.labels[fieldId] = {
            template: template
          };
        } else {
          return delete this.labels[fieldId];
        }
      }
    });
    EditorModal = Garnish.Modal.extend({
      originalTemplate: null,
      init: function(originalTemplate) {
        var body;
        this.base();
        this.originalTemplate = originalTemplate;
        this.$form = $('<form class="modal fitted">').appendTo(Garnish.$bod);
        this.setContainer(this.$form);
        body = $(['<div class="body">', '<div class="field">', '<div class="heading">', '<label for="customfield-name-field">', Craft.t('Template Path'), '</label>', '<div class="instructions"><p>', Craft.t('The template to use for this field.'), '</p></div>', '</div>', '<div class="input">', '<input id="customfield-name-field" type="text" class="text fullwidth" placeholder="/templates/path">', '<ul id="customfield-name-errors" class="errors" style="display: none;"></ul>', '</div>', '</div>', '<div class="buttons right" style="margin-top: 0;">', '<div id="customfield-cancel-button" class="btn">', Craft.t('Cancel'), '</div>', '<input id="customfield-save-button" type="submit" class="btn submit" value="', Craft.t('Save'), '">', '</div>', '</div>'].join('')).appendTo(this.$form);
        this.$nameField = body.find('#customfield-name-field');
        this.$nameErrors = body.find('#customfield-name-errors');
        this.$cancelBtn = body.find('#customfield-cancel-button');
        this.$saveBtn = body.find('#customfield-save-button');
        this.$nameField.prop('placeholder', this.originalTemplate);
        this.addListener(this.$cancelBtn, 'click', 'hide');
        return this.addListener(this.$form, 'submit', 'onFormSubmit');
      },
      onFormSubmit: function(e) {
        e.preventDefault();
        if (!this.visible) {
          return;
        }
        this.trigger('setLabel', {
          template: this.$nameField.val()
        });
        return this.hide();
      },
      onFadeOut: function() {
        this.base();
        return this.destroy();
      },
      destroy: function() {
        this.base();
        this.$container.remove();
        return this.$shade.remove();
      },
      show: function(template, instruct) {
        if (template) {
          this.$nameField.val(template);
        }
        if (!Garnish.isMobileBrowser()) {
          setTimeout($.proxy((function() {
            return this.$nameField.focus();
          }), this), 100);
        }
        return this.base();
      },
      displayErrors: function(attr, errors) {
        var $errorList, $input, i, results;
        $input = void 0;
        $errorList = void 0;
        switch (attr) {
          case 'template':
            $input = this.$nameField;
            $errorList = this.$nameErrors;
        }
        $errorList.children().remove();
        if (errors) {
          $input.addClass('error');
          $errorList.show();
          i = 0;
          results = [];
          while (i < errors.length) {
            $('<li>').text(errors[i]).appendTo($errorList);
            results.push(i++);
          }
          return results;
        } else {
          $input.removeClass('error');
          return $errorList.hide();
        }
      }
    });
    window.CustomTemplates = CustomTemplates;
    CustomTemplates.Editor = Editor;
    return CustomTemplates.Editor.Modal = EditorModal;
  }
})(window.jQuery);
