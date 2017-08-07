(($) ->
    CustomTemplates = setup: ->

    if $ and window.Garnish and window.Craft
        CustomTemplates = new (Garnish.Base.extend(
            fields: null
            labels: null
            
            init: ->
                @fields = {}
                @labels = {}

            setup: ->
                if Craft.FieldLayoutDesigner
                    FLD = Craft.FieldLayoutDesigner
                    FLD_init = FLD::init
                    FLD_field = FLD::initField
                    FLD_options = FLD::onFieldOptionSelect

                    FLD::init = ->
                        FLD_init.apply this, arguments
                        @customfield = new (window.CustomTemplates.Editor)(this)

                    FLD::initField = ($field) ->
                        FLD_field.apply this, arguments
                        $editBtn = $field.find('.settings')
                        menuBtn = $editBtn.data('menubtn')
                        menu = menuBtn.menu
                        $menu = menu.$container
                        $ul = $menu.children('ul')
                        $customfield = $('<li><a data-action="customfield">' + Craft.t('Custom Template') + '</a></li>').appendTo($ul)
                        menu.addOptions $customfield.children('a')

                    FLD::onFieldOptionSelect = (option) ->
                        FLD_options.apply this, arguments
                        $option = $(option)
                        $field = $option.data('menu').$anchor.parent()
                        action = $option.data('action')
                        switch action
                            when 'customfield'
                                @trigger 'customfieldOptionSelected',
                                    target: $option[0]
                                    $target: $option
                                    $field: $field
                                    fld: this
                                    id: $field.data('id') | 0

            getFieldInfo: (id) ->
                @fields[id]

            getLabelId: (fieldId, fieldLayoutId) ->
                @getLabel(fieldId, fieldLayoutId).id

            getLabel: (fieldId, fieldLayoutId) ->
                for id of @labels
                    if @labels.hasOwnProperty(id)
                        label = @labels[id]
                        if label.fieldId == fieldId and label.fieldLayoutId == fieldLayoutId
                            return label
                false

            getLabelsOnFieldLayout: (fieldLayoutId) ->
                fieldLayoutId = if isNaN(fieldLayoutId) then '' else fieldLayoutId
                labels = {}
                for labelId of @labels
                    if @labels.hasOwnProperty(labelId)
                        label = @labels[labelId]
                        if `label.fieldLayoutId == fieldLayoutId`
                            labels[labelId] = label
                labels
        ))


        # Editor
        Editor = Garnish.Base.extend(
            fld: null
            labels: null
            namespace: 'customfield'
            $form: null

            init: (fld) ->
                if !(fld instanceof Craft.FieldLayoutDesigner)
                    return
                @fld = fld
                @fld.on 'customfieldOptionSelected', $.proxy(@openModal, this)
                @labels = {}
                @$form = @fld.$container.closest('form')
                fieldLayoutId = @$form.find('input[name="fieldLayoutId"]').val()
                if fieldLayoutId != false
                    @applyLabels fieldLayoutId

            applyLabels: (fieldLayoutId) ->
                initLabels = CustomTemplates.getLabelsOnFieldLayout(fieldLayoutId)
                if initLabels
                    for labelId of initLabels
                        if initLabels.hasOwnProperty(labelId)
                            label = initLabels[labelId]
                            @setFormData label.fieldId, label.template

            openModal: (e) ->
                fieldId = e.id
                info = CustomTemplates.getFieldInfo(fieldId)
                originalTemplate = if info and typeof info.name == 'string' then info.name else ''
                modal = new (Editor.Modal)(originalTemplate)
                label = @labels[fieldId]
                that = this
                modal.on 'setLabel', (f) ->
                    that.setFormData fieldId, f.template
                modal.show if label then label.template else ''

            setFormData: (fieldId, template) ->
                $container = @fld.$container
                $field = $container.find('.fld-field[data-id="' + fieldId + '"]')
                templateField = @namespace + '[' + fieldId + '][template]'
                $field.children('input[name="' + templateField + '"]').remove()
                if template
                    $('<input type="hidden" name="' + templateField + '">').val(template).appendTo $field
                hasLabel = ! !template
                $field.toggleClass 'custom-template', hasLabel
                if hasLabel
                    @labels[fieldId] = template: template
                    svg = '<svg width="25" height="13" viewBox="0 0 25 13" xmlns="http://www.w3.org/2000/svg"><path d="M20.656 12.656c-.23.225-.533.337-.836.337-.302 0-.604-.112-.835-.337-.462-.45-.462-1.178 0-1.627L21.07 9H7C3.186 9-.003 6.104-.003 2.39V1.148c0-.635.53-1.15 1.182-1.15C1.83-.002 2 .365 2 1v1c0 2.445 2.49 5 5 5h14.085L19 5c-.462-.45-.477-1.25-.015-1.7.46-.45 1.21-.45 1.67 0l3.956 3.852c.248.236.363.53.362.826 0 .296-.114.59-.345.816l-3.97 3.862z" fill="#8094A1" fill-rule="evenodd"/></svg>'
                    $field.append("<div class=\"template-result\">#{template}</div>")
                    $field.find('.template-result').prepend svg
                else
                    delete @labels[fieldId]
        )

        # Editor Modal
        EditorModal = Garnish.Modal.extend(
            originalTemplate: null

            init: (originalTemplate) ->
                @base()
                @originalTemplate = originalTemplate
                console.log originalTemplate
                @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
                @setContainer @$form
                body = $([
                    '<header>'
                        '<span class="modal-title">'
                            Craft.t('Template Path')
                        '</span>'
                        '<div class="instructions"><p>'
                            Craft.t('The template to use for this field.')
                        '</p></div>'
                    '</header>'
                    '<div class="body">'
                        '<input id="customfield-name-field" type="text" class="text fullwidth" placeholder="templates/path">'
                        '<ul id="customfield-name-errors" class="errors" style="display: none;"></ul>'
                    '</div>'
                    '<footer class="footer">'
                        '<div class="buttons">'
                            '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                            '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                        '</div>'
                    '</footer>'
                  ].join('')).appendTo(@$form)
                @$nameField = body.find('#customfield-name-field')
                @$nameErrors = body.find('#customfield-name-errors')
                @$cancelBtn = body.find('.cancel')
                @$saveBtn = body.find('.submit')
                # @$nameField.prop 'placeholder', @originalTemplate
                @addListener @$cancelBtn, 'click', 'hide'
                @addListener @$form, 'submit', 'onFormSubmit'

            onFormSubmit: (e) ->
                console.log @$nameField.val()
                e.preventDefault()
                # Prevent multi form submits with the return key
                if !@visible
                    return
                @trigger 'setLabel', template: @$nameField.val()
                @hide()
            
            onFadeOut: ->
                @base()
                @destroy()
            
            destroy: ->
                @base()
                @$container.remove()
                @$shade.remove()
            
            show: (template, instruct) ->
                if template
                    @$nameField.val template
                if !Garnish.isMobileBrowser()
                    setTimeout $.proxy((->
                        @$nameField.focus()
                    ), this), 100
                @base()
            
            displayErrors: (attr, errors) ->
                $input = undefined
                $errorList = undefined
                switch attr
                    when 'template'
                        $input = @$nameField
                        $errorList = @$nameErrors
                $errorList.children().remove()
                if errors
                    $input.addClass 'error'
                    $errorList.show()
                    i = 0
                    while i < errors.length
                        $('<li>').text(errors[i]).appendTo $errorList
                        i++
                else
                    $input.removeClass 'error'
                    $errorList.hide()
        )

        window.CustomTemplates = CustomTemplates
        CustomTemplates.Editor = Editor
        CustomTemplates.Editor.Modal = EditorModal

) window.jQuery