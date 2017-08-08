if $ and window.Garnish
    NotificationSettingsItem = Garnish.Base.extend(
        $container: null
        $settingInput: null
        $settingResultHtml: null
        $editSetting: null

        modal: null
        editing: false
        
        init: (container) ->
            @$container = $(container)
            @$settingInput = @$container.find '.settings-input'
            @$settingResultHtml = @$container.find '.settings-result'
            @$editSetting = @$container.find '.settings-edit'

            @addListener @$editSetting, 'click', 'editSettings'

        editSettings: (e) ->
            e.preventDefault()

            if !@modal
                @modal = new SettingsItemModal(@)
            else
                @modal.show()

        updateHtmlFromModal: ->
            settingsResultText = @modal.$settingsModalInput.val()
            @$settingInput.val(settingsResultText)
            @$settingResultHtml.removeClass 'hidden'
            @$settingResultHtml.find('code').html settingsResultText

    )

    SettingsItemModal = Garnish.Modal.extend(
        option: null
        $settingsModalInput: null

        init: (option) ->
            console.log @

        init: (option) ->
            @option = option
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        option.$container.data('modal-title')
                    '</span>'
                    '<div class="instructions">'
                        option.$container.data('modal-instructions')
                    '</div>'
                '</header>'
                '<div class="body">'
                    '<div class="path-text">'
                        option.$container.data('input-hint')
                    '</div>'
                '</div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)

            $input = '<input type="text" class="text code settings-modal-input" size="50">'
            if option.$container.data('input-type') == 'select'
                $input = $('<div class="select"><select class="settings-modal-input" /></div>')
                inputOptions = option.$container.data 'input-options'
                $.each inputOptions, (i, item) ->
                    $input.find('select').append $('<option>',
                        value: item.value
                        text: item.label)


            @$form.find('.body').append($input)

            @show()
            @$settingsModalInput = body.find '.settings-modal-input'
            @$settingsModalInput.val(@option.$settingInput.val())
            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'hide'
            @addListener @$form, 'submit', 'save'

        save: (e) ->
            e.preventDefault()
            data = 
                settingsResultText: @$settingsModalInput.val()

            if !data.settingsResultText
                @$settingsModalInput.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @hide()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))

    )