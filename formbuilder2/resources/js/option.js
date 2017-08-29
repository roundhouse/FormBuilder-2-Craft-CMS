var Option;

if ($ && window.Garnish) {
  Option = Garnish.Base.extend({
    $container: null,
    $resultWrapper: null,
    $resultContainer: null,
    $toggle: null,
    $edit: null,
    $data: null,
    $inputs: null,
    enabled: false,
    editing: false,
    hasModal: false,
    hasTags: false,
    $enableCheckbox: null,
    $fields: null,
    init: function(container) {
      var self;
      self = this;
      this.$container = $(container);
      this.$resultWrapper = this.$container.find('.option-wrapper');
      this.$resultContainer = this.$container.find('.option-result');
      this.$toggle = this.$container.find('.option-toggle');
      this.$edit = this.$container.find('.option-edit');
      if (this.$container.hasClass('tags')) {
        this.hasTags = true;
      }
      this.$inputs = this.$container.data('inputs');
      this.$data = this.$container.data('modal');
      if (this.$data) {
        this.$fields = this.$data.fields;
        this.hasModal = true;
      }
      if (this.$inputs) {
        $.each(this.$inputs, function(i, item) {
          var name;
          if (item.type === 'checkbox') {
            self.enabled = item.checked;
            name = item.name;
            return self.$enableCheckbox = $("[name='" + name + "']");
          } else {
            return self.enabled = true;
          }
        });
      }
      this.addListener(this.$toggle, 'click', 'toggle');
      this.addListener(this.$edit, 'click', 'edit');
      if (this.enabled) {
        this.editing = true;
        if (this.$data) {
          return this.$edit.removeClass('hidden');
        }
      }
    },
    toggle: function(e) {
      e.preventDefault();
      this.editing = false;
      if (this.$container.hasClass('option-enabled')) {
        this.$edit.addClass('hidden');
        this.$container.removeClass('option-enabled');
        this.$resultWrapper.addClass('hidden');
        this.$resultContainer.html('');
        return this.$toggle.html('ENABLE');
      } else {
        this.$edit.removeClass('hidden');
        this.$container.addClass('option-enabled');
        this.$toggle.html('DISABLE');
        this.enableOption();
        if (this.hasModal) {
          if (!this.modal) {
            return this.modal = new Modal(this);
          } else {
            this.modal.$form.find('.fb-field').removeClass('error');
            this.modal.$form[0].reset();
            return this.modal.show();
          }
        }
      }
    },
    edit: function(e) {
      var self;
      self = this;
      this.editing = true;
      e.preventDefault();
      if (this.editing) {
        if (!this.modal) {
          return this.modal = new Modal(this);
        } else {
          this.modal.$form.find('.fb-field').removeClass('error');
          $.each(this.$inputs, function(i, item) {
            var className, currentValue;
            if (item.type !== 'checkbox') {
              currentValue = $("[name='" + item.name + "']").val();
              className = item.name.replace(/[_\W]+/g, "-").slice(0, -1);
              return $.each(self.modal.$modalInputs, function(i, item) {
                var input;
                input = $(item);
                if (input.hasClass(className)) {
                  return input.val(currentValue);
                }
              });
            }
          });
          return this.modal.show();
        }
      }
    },
    enableOption: function() {
      if (this.$enableCheckbox) {
        this.$enableCheckbox.val('true');
        return this.$enableCheckbox.prop('checked', true);
      }
    },
    updateHtmlFromModal: function() {
      var $resultHtml, body, index, key, name, self, totalResults, value;
      self = this;
      if (this.hasTags) {
        totalResults = this.$resultContainer.find('.result-item').length;
        if (totalResults) {
          index = totalResults;
        } else {
          index = 0;
        }
        $resultHtml = $('<div class="result-item" data-result-index="' + index + '">').appendTo(Garnish.$bod);
        name = $(this.modal.$modalInputs[0]).data('name');
        key = $(this.modal.$modalInputs[0]).val();
        value = $(this.modal.$modalInputs[1]).val();
        body = $(['<div class="option-result-actions">', '<a href="#" class="option-result-delete" title="' + Craft.t('Delete') + '"><svg width="19" height="19" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path d="M9.521064 18.5182504c-4.973493 0-9.019897-4.0510671-9.019897-9.030471 0-4.98018924 4.046404-9.0312563 9.019897-9.0312563s9.019897 4.05106706 9.019897 9.0312563c0 4.9794039-4.046404 9.030471-9.019897 9.030471zm0-16.05425785c-3.868359 0-7.015127 3.15021907-7.015127 7.02378685 0 3.8727824 3.146768 7.0237869 7.015127 7.0237869 3.86836 0 7.015127-3.1510045 7.015127-7.0237869 0-3.87356778-3.146767-7.02378685-7.015127-7.02378685zm3.167945 10.02870785c-.196085.1955634-.452564.2937378-.708258.2937378-.256479 0-.512958-.0981744-.709042-.2937378L9.521064 10.739699 7.77042 12.4927004c-.196085.1955634-.452564.2937378-.709043.2937378-.256478 0-.512957-.0981744-.708258-.2937378-.391385-.391912-.391385-1.0272965 0-1.4192086l1.750645-1.7530015-1.750645-1.7530015c-.391385-.391912-.391385-1.02729655 0-1.41920862.391385-.39191207 1.025131-.39191207 1.417301 0L9.521064 7.9012817l1.750645-1.75300152c.391385-.39191207 1.025915-.39191207 1.4173 0 .391385.39191207.391385 1.02729662 0 1.41920862l-1.750644 1.7530015 1.750644 1.7530015c.391385.3919121.391385 1.0272966 0 1.4192086z" fill="#8094A1" fill-rule="evenodd"/></svg></a>', '</div>', '<code><span class="option-key input-hint">' + key + '</span> ' + value + '</code>', '<input type="hidden" name="' + name + '[' + index + '][key]" value="' + key + '" />', '<input type="hidden" name="' + name + '[' + index + '][value]" value="' + value + '" />'].join('')).appendTo($resultHtml);
        this.$resultContainer.append($resultHtml);
        new Tag($resultHtml, this.modal);
      } else {
        this.$resultContainer.html('');
        $.each(this.modal.$modalInputs, function(i, item) {
          var hint;
          value = $(item).val();
          if (value) {
            name = $(item).data('name');
            hint = $(item).data('hint');
            $("[name='" + name + "']").val(value);
            return self.$resultContainer.append($("<code><span class='input-hint'>" + hint + ":</span> " + value + "</code>"));
          }
        });
      }
      return this.$resultWrapper.removeClass('hidden');
    }
  });
}

$(document).ready(function() {
  return $('.option-item').each(function(i, el) {
    return new Option(el);
  });
});
