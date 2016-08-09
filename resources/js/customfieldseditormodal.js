(function($)
{
    if(!$ || !window.Garnish || !window.Craft)
    {
        return;
    }

    var EditorModal = Garnish.Modal.extend({

        originalTemplate:      null,

        init: function(originalTemplate)
        {
            this.base();

            this.originalTemplate     = originalTemplate;

            this.$form = $('<form class="modal fitted">').appendTo(Garnish.$bod);
            this.setContainer(this.$form);

            var body = $([
                '<div class="body">',
                    '<div class="field">',
                        '<div class="heading">',
                            '<label for="customfield-name-field">', Craft.t('Template Path'), '</label>',
                            '<div class="instructions"><p>', Craft.t('The template to use for this field.'), '</p></div>',
                        '</div>',
                        '<div class="input">',
                            '<input id="customfield-name-field" type="text" class="text fullwidth">',
                            '<ul id="customfield-name-errors" class="errors" style="display: none;"></ul>',
                        '</div>',
                    '</div>',
                    '<div class="buttons right" style="margin-top: 0;">',
                        '<div id="customfield-cancel-button" class="btn">', Craft.t('Cancel'), '</div>',
                        '<input id="customfield-save-button" type="submit" class="btn submit" value="', Craft.t('Save'), '">',
                    '</div>',
                '</div>'
            ].join('')).appendTo(this.$form);

            this.$nameField = body.find('#customfield-name-field');
            this.$nameErrors = body.find('#customfield-name-errors');
            this.$cancelBtn = body.find('#customfield-cancel-button');
            this.$saveBtn = body.find('#customfield-save-button');

            this.$nameField.prop('placeholder', this.originalTemplate);

            this.addListener(this.$cancelBtn, 'click', 'hide');
            this.addListener(this.$form, 'submit', 'onFormSubmit');
        },

        onFormSubmit: function(e)
        {
            e.preventDefault();

            // Prevent multi form submits with the return key
            if(!this.visible)
            {
                return;
            }

            this.trigger('setLabel', {
                template: this.$nameField.val(),
            });

            this.hide();
        },

        onFadeOut: function()
        {
            this.base();

            this.destroy();
        },

        destroy: function()
        {
            this.base();

            this.$container.remove();
            this.$shade.remove();
        },

        show: function(template, instruct)
        {
            if(template)     this.$nameField.val(template);

            if(!Garnish.isMobileBrowser())
            {
                setTimeout($.proxy(function()
                {
                    this.$nameField.focus()
                }, this), 100);
            }

            this.base();
        },

        displayErrors: function(attr, errors)
        {
            var $input;
            var $errorList;

            switch(attr)
            {
                case 'template':
                {
                    $input = this.$nameField;
                    $errorList = this.$nameErrors;

                    break;
                }
            }

            $errorList.children().remove();

            if(errors)
            {
                $input.addClass('error');
                $errorList.show();

                for(var i = 0; i < errors.length; i++)
                {
                    $('<li>').text(errors[i]).appendTo($errorList);
                }
            }
            else
            {
                $input.removeClass('error');
                $errorList.hide();
            }
        }
    });

    CustomField.Editor.Modal = EditorModal;

})(window.jQuery);
