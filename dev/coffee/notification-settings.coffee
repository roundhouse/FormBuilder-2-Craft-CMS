if $ and window.Garnish
    NotificationSettingsItem = Garnish.Base.extend(
        $container: null
        $settingInput: null
        $settingResultHtml: null
        $editSetting: null
        $toggleSettings: null

        type: null
        name: null
        nameOne: null
        nameTwo: null
        modal: null
        editing: false
        
        init: (container) ->
            @$container = $(container)
            @$settingInput = @$container.find '.form-settings-input'
            @$settingResultHtml = @$container.find '.settings-result'
            @$editSetting = @$container.find '.settings-edit'
            @$toggleSettings = @$container.find '.toggle-settings'

            @type = @$container.data 'type'
            @name = @$container.data 'name'
            @nameOne = @$container.data 'nameOne'
            @nameTwo = @$container.data 'nameTwo'

            @addListener @$editSetting, 'click', 'editSettings'
            @addListener @$toggleSettings, 'click', 'toggle'
            @addListener @$editSingleSetting, 'click', 'editSettings'

            if @$container.hasClass 'settings-enabled'
                if @$container.data('modal')
                    @$editSetting.removeClass 'hidden'

        toggle: (e) ->
            @editing = false
            e.preventDefault()
            if @$container.hasClass 'settings-enabled'
                @$editSetting.addClass 'hidden'
                @$container.removeClass 'settings-enabled'
                @$settingInput.val ''
                @$settingInput.prop 'checked', false
                @$settingResultHtml.addClass 'hidden'
                @$toggleSettings.html 'ENABLE'

                if @$container.data('modal') && @modal
                    if @type == 'select'
                        @modal.$inputValueOne.val ''
                        @modal.$inputValueTwo.val ''
                    else
                        @modal.$settingsModalInput.val ''
            else
                @$editSetting.removeClass 'hidden'
                @$container.addClass 'settings-enabled'
                @$settingInput.val '1'
                @$settingInput.prop 'checked', true
                @$toggleSettings.html 'DISABLE'

                if @$container.data('modal')
                    if @type == 'select'
                        if !@modal
                            @modal = new CustomSubjectModal(@) 
                        else
                            if @modal.$fieldOne
                                @modal.$fieldOne.removeClass 'error'
                                @modal.$fieldTwo.removeClass 'error'
                            @modal.show()
                    else
                        if !@modal
                            @modal = new SingleItemModal(@)
                        else
                            if @modal.$fieldOne
                                @modal.$fieldOne.removeClass 'error'
                            @modal.show()

        editSettings: (e) ->
            e.preventDefault()
            if @type == 'multiple'
                if !@modal then @modal = new MultipleItemModal(@) else @modal.show()
            else if @type == 'template'
                if !@modal
                    @editing = true
                    @modal = new TemplateModal(@)
                    @modal.$inputValueOne.val(@$settingInput.val())
                else
                    @modal.show()
                    @modal.$inputValueOne.val(@$settingInput.val())

            else if @type == 'select'
                if !@modal
                    @modal = new CustomSubjectModal(@) 
                    if @$settingInput.data('type') == 'text'
                        @editing = true
                        @modal.$inputValueOne.val(@$settingInput.val())
                    else
                        @editing = true
                        @modal.$inputValueTwo.val(@$settingInput.val())
                else
                    if @modal.type == 'text'
                        @modal.$inputValueOne.val(@$settingInput.val())
                    else
                        @modal.$inputValueTwo.val(@$settingInput.val())
                    @modal.show()
            else
                if !@modal
                    @modal = new SingleItemModal(@)
                else
                    @modal.$fieldOne.removeClass 'error'
                    if @$settingInput
                        @modal.$settingsModalInput.val(@$settingInput.val())
                    @modal.show()

        updateHtmlFromModal: ->
            if @modal.item.type == 'multiple'
                @updateMultipleHtmlFromModal()
            else if @modal.item.type == 'attachments'
                @updateAttachmentsHtmlFromModal()
            else if @modal.item.type == 'select'
                @updateSubjectHtmlFromModal()
            else if @modal.item.type == 'template'
                @updateTemplateHtmlFromModal()
            else
                @updateSingleHtmlFromModal()

        updateTemplateHtmlFromModal: ->
            templateResultHandle = @modal.$inputValueOne.val()
            $code = "<code>Template Handle: #{templateResultHandle}</code>";
            $input = '<input type="hidden" class="form-settings-input" name="'+@name+'" value="'+templateResultHandle+'" />';
            @$settingResultHtml.removeClass 'hidden'
            @$settingInput = $($input)
            @$settingResultHtml.html $code + $input

        updateSubjectHtmlFromModal: ->
            settingsResultTextOne = @modal.$inputValueOne.val()
            settingsResultTextTwo = @modal.$inputValueTwo.val()
            @$settingResultHtml.removeClass 'hidden'

            if @modal.type == 'text'
                $code = "<code>Text: #{settingsResultTextOne}</code>";
                $input = '<input type="hidden" class="form-settings-input" name="'+@nameOne+'" value="'+settingsResultTextOne+'" />';
            else
                $code = "<code>Field: #{settingsResultTextTwo}</code>";
                $input = '<input type="hidden" class="form-settings-input" name="'+@nameTwo+'" value="'+settingsResultTextTwo+'" />';

            @$settingInput = $($input)
            @$settingResultHtml.html $code + $input

        updateAttachmentsHtmlFromModal: ->
            console.log 'attachments html'

        updateMultipleHtmlFromModal: ->
            totalResults = @modal.item.$settingResultHtml.find('.result-item').length
            if totalResults then index = totalResults else index = 0
            $resultHtml = $('<div class="result-item" data-result-index="'+index+'">').appendTo(Garnish.$bod)
            body = $([
                '<div class="settings-result-actions">'
                    '<a href="#" class="settings-result-delete" title="'+Craft.t('Delete')+'"><svg width="19" height="19" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path d="M9.521064 18.5182504c-4.973493 0-9.019897-4.0510671-9.019897-9.030471 0-4.98018924 4.046404-9.0312563 9.019897-9.0312563s9.019897 4.05106706 9.019897 9.0312563c0 4.9794039-4.046404 9.030471-9.019897 9.030471zm0-16.05425785c-3.868359 0-7.015127 3.15021907-7.015127 7.02378685 0 3.8727824 3.146768 7.0237869 7.015127 7.0237869 3.86836 0 7.015127-3.1510045 7.015127-7.0237869 0-3.87356778-3.146767-7.02378685-7.015127-7.02378685zm3.167945 10.02870785c-.196085.1955634-.452564.2937378-.708258.2937378-.256479 0-.512958-.0981744-.709042-.2937378L9.521064 10.739699 7.77042 12.4927004c-.196085.1955634-.452564.2937378-.709043.2937378-.256478 0-.512957-.0981744-.708258-.2937378-.391385-.391912-.391385-1.0272965 0-1.4192086l1.750645-1.7530015-1.750645-1.7530015c-.391385-.391912-.391385-1.02729655 0-1.41920862.391385-.39191207 1.025131-.39191207 1.417301 0L9.521064 7.9012817l1.750645-1.75300152c.391385-.39191207 1.025915-.39191207 1.4173 0 .391385.39191207.391385 1.02729662 0 1.41920862l-1.750644 1.7530015 1.750644 1.7530015c.391385.3919121.391385 1.0272966 0 1.4192086z" fill="#8094A1" fill-rule="evenodd"/></svg></a>'
                '</div>'
                '<code class="value-one">'+@modal.$inputValueOne.val()+'</code>'
                '<code class="value-two">'+@modal.$inputValueTwo.val()+'</code>'
                '<input type="hidden" name="'+@name+'['+index+'][key]" value="'+@modal.$inputValueOne.val()+'" />'
                '<input type="hidden" name="'+@name+'['+index+'][value]" value="'+@modal.$inputValueTwo.val()+'" />'
            ].join('')).appendTo($resultHtml)
            @$settingResultHtml.append $resultHtml
            new ResultItem($resultHtml, @modal)

        updateSingleHtmlFromModal: ->
            settingsResultText = @modal.$settingsModalInput.val()
            @$settingInput.val(settingsResultText)
            @$settingResultHtml.removeClass 'hidden'
            $code = "<code>#{settingsResultText}</code>";
            $input = '<input type="hidden" class="form-settings-input" name="'+@name+'" value="'+settingsResultText+'" />';
            @$settingInput = $($input)
            @$settingResultHtml.html $code + $input
    )

    # Result Item
    ResultItem = Garnish.Base.extend(
        $item: null
        # $editItemBtn: null
        $deleteItemBtn: null

        modal: null
        inputValueOne: null
        inputValueTwo: null

        init: (item, modal) ->
            @$item = $(item)
            # @$editItemBtn = @$item.find '.settings-result-edit'
            @$deleteItemBtn = @$item.find '.settings-result-delete'
            
            @modal = modal
            @inputValueOne = @$item.find('.value-one').text()
            @inputValueTwo = @$item.find('.value-two').text()

            # @addListener @$editItemBtn, 'click', 'editItemSettings'
            @addListener @$deleteItemBtn, 'click', 'deleteItemSettings'

        # editItemSettings: (e) ->
        #     e.preventDefault()
        #     @modal.$inputValueOne.val(@inputValueOne)
        #     @modal.$inputValueTwo.val(@inputValueTwo)
        #     @modal.show()
        
        deleteItemSettings: (e) ->
            e.preventDefault()
            self = @
            @$item.addClass 'zap'
            setTimeout (->
                self.$item.remove()
                Craft.cp.displayNotice(Craft.t('Item Removed'))
            ), 300
    )

    # Template Modal
    TemplateModal = Garnish.Modal.extend(
        item: null
        $form: null
        $inputValueOne: null
        $fieldOne: null

        init: (item) ->
            @item = item
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        item.$container.data('modal-title')
                    '</span>'
                    '<div class="instructions">'
                        item.$container.data('modal-instructions')
                    '</div>'
                '</header>'
                '<div class="body">'
                    '<div class="fb-field input-one">'
                        '<div class="input-hint input-hint-one">'
                            item.$container.data('inputHint-one')
                        '</div>'
                    '</div>'
                '</div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)

            $input1 = $('<div class="select"><select class="modal-input-one" /></div>')
            inputOptions = item.$container.data 'input-options'
            $.each inputOptions, (i, item) ->
                $input1.find('select').append $('<option>',
                    value: item.value
                    text: item.label)

            @$form.find('.body .input-one').append($input1)
            @show()

            @$inputValueOne = body.find '.modal-input-one'
            @$fieldOne = body.find '.input-one'

            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        hide: (e) ->
            @cancel()
        
        cancel: (e) ->
            if !@item.editing
                @item.$editSetting.addClass 'hidden'
                @item.$container.removeClass 'settings-enabled'
                @item.$settingInput.val('')
                @item.$settingInput.prop 'checked', false
                # @item.$formOptionInput.val ''
                @item.$settingResultHtml.addClass 'hidden'
                @item.$toggleSettings.html 'ENABLE'
                @closeModal()
            else
                @closeModal()

        closeModal: (ev) ->
            @disable()
            if ev
                ev.stopPropagation()
            if @$container
                @$container.velocity 'fadeOut', duration: Garnish.FX_DURATION
                @$shade.velocity 'fadeOut',
                    duration: Garnish.FX_DURATION
                    complete: $.proxy(this, 'onFadeOut')
                if @settings.hideOnShadeClick
                    @removeListener @$shade, 'click'
                @removeListener Garnish.$win, 'resize'
            @visible = false
            Garnish.Modal.visibleModal = null
            if @settings.hideOnEsc
                Garnish.escManager.unregister this
            @trigger 'hide'
            @settings.onHide()

        save: (e) ->
            e.preventDefault()
            data = 
                inputValueOne: @$inputValueOne.val()

            if !data.inputValueOne
                @$fieldOne.addClass 'error'
                Garnish.shake(@$container)
            else
                @item.updateHtmlFromModal()
                @hide()
                @$form[0].reset()
                Craft.cp.displayNotice(@item.$container.data('modal-success-message'))
    )

    # Custom Subject Modal
    CustomSubjectModal = Garnish.Modal.extend(
        item: null
        $form: null
        $inputValueOne: null
        $inputValueTwo: null
        $fieldOne: null
        $fieldTwo: null

        type: null

        init: (item) ->
            @item = item
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        item.$container.data('modal-title')
                    '</span>'
                    '<div class="instructions">'
                        item.$container.data('modal-instructions')
                    '</div>'
                '</header>'
                '<div class="body">'
                    '<div class="fb-field input-one">'
                        '<div class="input-hint input-hint-one">'
                            item.$container.data('inputHint-one')
                        '</div>'
                    '</div>'
                    '<span class="section-or">OR</span>'
                    '<div class="fb-field input-two">'
                        '<div class="input-hint input-hint-two">'
                            item.$container.data('inputHint-two')
                        '</div>'
                    '</div>'
                '</div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)

            $input1 = '<input type="text" class="text code modal-input-one" size="50">'
            $input2 = $('<div class="select"><select class="modal-input-two" /></div>')
            inputOptions = item.$container.data 'input-options'
            $.each inputOptions, (i, item) ->
                $input2.find('select').append $('<option>',
                    value: item.value
                    text: item.label)

            @$form.find('.body .input-one').append($input1)
            @$form.find('.body .input-two').append($input2)
            @show()

            @$inputValueOne = body.find '.modal-input-one'
            @$inputValueTwo = body.find '.modal-input-two'
            @$fieldOne = body.find '.input-one'
            @$fieldTwo = body.find '.input-two'

            setTimeout $.proxy((->
                @$inputValueOne.focus()
            ), this), 100

            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'
            @addListener @$inputValueOne, 'keyup', 'clearInputTwo'
            @addListener @$inputValueTwo, 'change', 'clearInputOne'

        clearInputOne: (e) ->
            @$inputValueOne.val ''
        
        clearInputTwo: (e) ->
            @$inputValueTwo.val ''

        hide: (e) ->
            @cancel()
        
        cancel: (e) ->
            if !@item.editing
                @item.$editSetting.addClass 'hidden'
                @item.$container.removeClass 'settings-enabled'
                @item.$settingInput.val('')
                @item.$settingInput.prop 'checked', false
                # @item.$formOptionInput.val ''
                @item.$settingResultHtml.addClass 'hidden'
                @item.$toggleSettings.html 'ENABLE'
                @closeModal()
            else
                @closeModal()

        closeModal: (ev) ->
            @disable()
            if ev
                ev.stopPropagation()
            if @$container
                @$container.velocity 'fadeOut', duration: Garnish.FX_DURATION
                @$shade.velocity 'fadeOut',
                    duration: Garnish.FX_DURATION
                    complete: $.proxy(this, 'onFadeOut')
                if @settings.hideOnShadeClick
                    @removeListener @$shade, 'click'
                @removeListener Garnish.$win, 'resize'
            @visible = false
            Garnish.Modal.visibleModal = null
            if @settings.hideOnEsc
                Garnish.escManager.unregister this
            @trigger 'hide'
            @settings.onHide()

        save: (e) ->
            e.preventDefault()
            data = 
                inputValueOne: @$inputValueOne.val()
                inputValueTwo: @$inputValueTwo.val()

            if !data.inputValueOne && !data.inputValueTwo
                @$fieldOne.addClass 'error'
                @$fieldTwo.addClass 'error'
                Garnish.shake(@$container)
                @item.editing = false
            else
                if data.inputValueTwo
                    @type = 'field'
                    @item.editing = true
                else
                    @type = 'text'
                    @item.editing = true

                @item.updateHtmlFromModal()
                @hide()
                @$form[0].reset()
                Craft.cp.displayNotice(@item.$container.data('modal-success-message'))
    )

    # Multi Line Modal
    MultipleItemModal = Garnish.Modal.extend(
        item: null
        $form: null
        $inputValueOne: null
        $inputValueTwo: null
        $fieldOne: null
        $fieldTwo: null

        init: (item) ->
            @item = item
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        item.$container.data('modal-title')
                    '</span>'
                    '<div class="instructions">'
                        item.$container.data('modal-instructions')
                    '</div>'
                '</header>'
                '<div class="body">'
                    '<div class="fb-field input-one">'
                        '<div class="input-hint input-hint-one">'
                            item.$container.data('inputHint-one')
                        '</div>'
                    '</div>'
                    '<div class="fb-field input-two">'
                        '<div class="input-hint input-hint-two">'
                            item.$container.data('inputHint-two')
                        '</div>'
                    '</div>'
                '</div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Add')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)

            $input1 = '<input type="text" class="text code modal-input-one" size="50">'
            $input2 = '<input type="text" class="text code modal-input-two" size="50">'

            @$form.find('.body .input-one').append($input1)
            @$form.find('.body .input-two').append($input2)
            @show()

            @$inputValueOne = body.find '.modal-input-one'
            @$inputValueTwo = body.find '.modal-input-two'
            @$fieldOne = body.find '.input-one'
            @$fieldTwo = body.find '.input-two'

            setTimeout $.proxy((->
                @$inputValueOne.focus()
            ), this), 100

            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'hide'
            @addListener @$form, 'submit', 'save'

        save: (e) ->
            e.preventDefault()
            data = 
                inputValueOne: @$inputValueOne.val()
                inputValueTwo: @$inputValueTwo.val()

            if !data.inputValueOne
                @$fieldOne.addClass 'error'
                Garnish.shake(@$container)
            else if !data.inputValueTwo
                @$fieldTwo.addClass 'error'
                Garnish.shake(@$container)
            else
                @item.updateHtmlFromModal()
                @hide()
                @$form[0].reset()
                Craft.cp.displayNotice(@item.$container.data('modal-success-message'))

    )


    SingleItemModal = Garnish.Modal.extend(
        item: null
        $form: null
        $settingsModalInput: null
        $fieldOne: null

        init: (item) ->
            @item = item
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        item.$container.data('modal-title')
                    '</span>'
                    '<div class="instructions">'
                        item.$container.data('modal-instructions')
                    '</div>'
                '</header>'
                '<div class="body">'
                    '<div class="fb-field input-one">'
                        '<div class="input-hint">'
                            item.$container.data('input-hint')
                        '</div>'
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
            if item.$container.data('input-type') == 'select'
                $input = $('<div class="select"><select class="settings-modal-input" /></div>')
                inputOptions = item.$container.data 'input-options'
                $.each inputOptions, (i, item) ->
                    $input.find('select').append $('<option>',
                        value: item.value
                        text: item.label)


            @$form.find('.fb-field').append($input)
            @$fieldOne = body.find '.input-one'

            @show()

            @$settingsModalInput = body.find '.settings-modal-input'

            setTimeout $.proxy((->
                @$settingsModalInput.focus()
            ), this), 100

            if @item.$settingInput
                @$settingsModalInput.val(@item.$settingInput.val())

            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'hide'
            @addListener @$form, 'submit', 'save'

        save: (e) ->
            e.preventDefault()
            data = 
                settingsResultText: @$settingsModalInput.val()

            if !data.settingsResultText
                @$fieldOne.addClass 'error'
                Garnish.shake(@$container)
            else
                @item.updateHtmlFromModal()
                @hide()
                @$form[0].reset()
                Craft.cp.displayNotice(@item.$container.data('modal-success-message'))

    )