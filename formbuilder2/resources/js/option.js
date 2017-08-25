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
    $enableCheckbox: null,
    $fields: null,
    init: function(container) {
      var name;
      this.$container = $(container);
      this.$resultWrapper = this.$container.find('.option-wrapper');
      this.$resultContainer = this.$container.find('.option-result');
      this.$toggle = this.$container.find('.option-toggle');
      this.$edit = this.$container.find('.option-edit');
      this.$inputs = this.$container.data('inputs');
      this.$data = this.$container.data('modal');
      if (this.$data) {
        this.$fields = this.$data.fields;
        this.hasModal = true;
      }
      if (this.$inputs) {
        if (this.$inputs.hasOwnProperty('checkbox')) {
          this.enabled = this.$inputs['checkbox'].checked;
          name = this.$inputs['checkbox'].name;
          this.$enableCheckbox = $("[name='" + name + "']");
        } else {
          this.enabled = true;
        }
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
      var self;
      self = this;
      this.$resultContainer.html('');
      $.each(this.modal.$modalInputs, function(i, item) {
        var hint, name, value;
        value = $(item).val();
        name = $(item).data('name');
        hint = $(item).data('hint');
        $("[name='" + name + "']").val(value);
        return self.$resultContainer.append($("<code><span>" + hint + ":</span> " + value + "</code>"));
      });
      return this.$resultWrapper.removeClass('hidden');
    }
  });
}

$(document).ready(function() {
  return $('.option-item').each(function(i, el) {
    return new Option(el);
  });
});
