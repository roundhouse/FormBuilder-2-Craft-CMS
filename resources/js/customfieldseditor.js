(function($)
{
    if(!$ || !window.Garnish || !window.Craft)
    {
        return;
    }

    var Editor = Garnish.Base.extend({

        fld: null,
        labels: null,
        namespace: 'customfield',
        $form: null,

        init: function(fld)
        {
            if(!(fld instanceof Craft.FieldLayoutDesigner))
            {
                return;
            }

            this.fld = fld;
            this.fld.on('customfieldOptionSelected', $.proxy(this.openModal, this));
            this.labels = {};

            this.$form = this.fld.$container.closest('form');
            var fieldLayoutId = this.$form.find('input[name="fieldLayoutId"]').val();

            if(fieldLayoutId !== false)
            {
                this.applyLabels(fieldLayoutId)
            }
        },

        applyLabels: function(fieldLayoutId)
        {
            var initLabels = CustomField.getLabelsOnFieldLayout(fieldLayoutId);

            if(initLabels)
            {
                for(var labelId in initLabels) if(initLabels.hasOwnProperty(labelId))
                {
                    var label = initLabels[labelId];
                    this.setFormData(label.fieldId, label.template);
                }
            }
        },

        openModal: function(e)
        {
            var fieldId = e.id;

            var info = CustomField.getFieldInfo(fieldId);
            var originalTemplate = info && typeof info.name === 'string' ? info.name : '';

            var modal = new Editor.Modal(originalTemplate);
            var label = this.labels[fieldId];

            var that = this;
            modal.on('setLabel', function(f)
            {
                that.setFormData(fieldId, f.template);
            });

            modal.show(
                label ? label.template : ''
            );
        },

        setFormData: function(fieldId, template)
        {
            var $container = this.fld.$container;
            var $field = $container.find('.fld-field[data-id="' + fieldId + '"]');

            var templateField = this.namespace + '[' + fieldId + '][template]';

            $field.children('input[name="' + templateField + '"]').remove();

            if(template) $('<input type="hidden" name="' + templateField     + '">').val(template).appendTo($field);

            var hasLabel = !!(template);

            $field.toggleClass('customfield', hasLabel);

            if(hasLabel)
            {
                this.labels[fieldId] = {
                    template: template
                };
            }
            else
            {
                delete this.labels[fieldId];
            }
        }
    });

    CustomField.Editor = Editor;

})(window.jQuery);
