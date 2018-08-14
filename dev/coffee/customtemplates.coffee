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
                $field.toggleClass 'customfield', hasLabel
                if hasLabel
                    @labels[fieldId] = template: template
                else
                    delete @labels[fieldId]
        )

        # Editor Modal
        EditorModal = Garnish.Modal.extend(
            originalTemplate: null

            init: (originalTemplate) ->
                @base()
                @originalTemplate = originalTemplate
                @$form = $('<form class="modal fitted">').appendTo(Garnish.$bod)
                @setContainer @$form
                body = $([
                    '<div class="body">'
                    '<div class="field">'
                    '<div class="heading">'
                    '<label for="customfield-name-field">'
                    Craft.t('Template Path')
                    '</label>'
                    '<div class="instructions"><p>'
                    Craft.t('The template to use for this field.')
                    '</p></div>'
                    '</div>'
                    '<div class="input">'
                    '<input id="customfield-name-field" type="text" class="text fullwidth" placeholder="/templates/path">'
                    '<ul id="customfield-name-errors" class="errors" style="display: none;"></ul>'
                    '</div>'
                    '</div>'
                    '<div class="buttons right" style="margin-top: 0;">'
                    '<div id="customfield-cancel-button" class="btn">'
                    Craft.t('Cancel')
                    '</div>'
                    '<input id="customfield-save-button" type="submit" class="btn submit" value="'
                    Craft.t('Save')
                    '">'
                    '</div>'
                    '</div>'
                  ].join('')).appendTo(@$form)
                @$nameField = body.find('#customfield-name-field')
                @$nameErrors = body.find('#customfield-name-errors')
                @$cancelBtn = body.find('#customfield-cancel-button')
                @$saveBtn = body.find('#customfield-save-button')
                @$nameField.prop 'placeholder', @originalTemplate
                @addListener @$cancelBtn, 'click', 'hide'
                @addListener @$form, 'submit', 'onFormSubmit'

            onFormSubmit: (e) ->
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