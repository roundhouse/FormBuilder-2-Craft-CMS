if $ and window.Garnish
    FormOption = Garnish.Base.extend(
        $container: null
        $enableFormOption: null
        $formOptionInput: null
        $formOptionInputTwo: null
        $formOptionResultHtml: null
        $toggleBtn: null
        $editFormOption: null

        modal: null
        type: null
        name: null
        editing: false
     
        init: (container) ->
            @$container = $(container)
            @$enableFormOption = @$container.find '.enable-form-option'
            @$formOptionInput = @$container.find '.form-option-input'
            @$formOptionInputTwo = @$container.find '.form-option-textarea'
            @$formOptionResultHtml = @$container.find '.option-result'
            @$editFormOption = @$container.find '.option-edit'
            @$toggleBtn = @$container.find '.toggle-option'

            @type = @$container.data 'type'
            @name = @$container.data 'name'
            @nameTwo = @$container.data 'name-two'

            @addListener @$editFormOption, 'click', 'editFormOption'
            @addListener @$toggleBtn, 'click', 'edit'

            if @$container.hasClass 'option-enabled'
                if @$container.data('modal')
                    @$editFormOption.removeClass 'hidden'

        editFormOption: (e) ->
            e.preventDefault()
            @editing = true
            if @type == 'terms'
                if !@modal
                    @modal = new FormTermsOptionModal(@)
                else
                    if @$formOptionInput
                        @modal.$modalLabelInput.val(@$formOptionInput.val())
                    if @$formOptionInputTwo
                        @modal.$modalCopyInput.val(@$formOptionInputTwo.val())
                    @modal.show()
            else
                if !@modal
                    @modal = new FormOptionModal(@)
                else
                    if @$formOptionInput
                        @modal.$formOptionModalInput.val(@$formOptionInput.val())
                    @modal.show()
                    @modal.$formOptionModalField.removeClass 'error'

        edit: (e) ->
            @editing = false
            e.preventDefault()
            if @$container.hasClass 'option-enabled'
                @$editFormOption.addClass 'hidden'
                @$container.removeClass 'option-enabled'
                @$enableFormOption.val false
                @$enableFormOption.prop 'checked', false
                @$formOptionResultHtml.addClass 'hidden'
                @$formOptionResultHtml.find('.result-container').html ''
                if @$container.data('modal') && @modal
                    if @type == 'terms'
                        @modal.$modalLabelInput.val ''
                        @modal.$modalCopyInput.val ''
                    else
                        @modal.$formOptionModalInput.val ''
                @$toggleBtn.html 'ENABLE'
            else
                @$editFormOption.removeClass 'hidden'
                @$container.addClass 'option-enabled'
                @$enableFormOption.val true
                @$enableFormOption.prop 'checked', true
                @$toggleBtn.html 'DISABLE'

                if @$container.data('modal')
                    if @type == 'terms'
                        if !@modal then @modal = new FormTermsOptionModal(@) else @modal.show()
                    else
                        if !@modal
                            @modal = new FormOptionModal(@) 
                        else 
                            @modal.$formOptionModalField.removeClass 'error'
                            @modal.show()

        updateHtmlFromModal: ->
            if @type == 'terms'
                @updateTermsHtmlFromModal()
            else
                @updateSingleHtmlFromModal()

        updateTermsHtmlFromModal: ->
            formLabelResult = @modal.$modalLabelInput.val()
            formCopyResult = @modal.$modalCopyInput.val()
            @$formOptionResultHtml.removeClass 'hidden'
            $code = "<code>Label: #{formLabelResult}</code><br />"
            $input = "<input type='text' class='form-option-input hidden' name='#{@name}' value='#{formLabelResult}' />"
            $codeTwo = "<code class='inline-copy'>Copy: #{formCopyResult}</code>"
            $textarea = "<textarea type='text' class='form-option-textarea hidden' name='#{@nameTwo}'>#{formCopyResult}</textarea>"
            @$formOptionInput = $($input)
            @$formOptionInputTwo = $($textarea)
            @$formOptionInput.val(formLabelResult)
            @$formOptionInputTwo.val(formCopyResult)
            @$formOptionResultHtml.find('.result-container').html $code + $input + $codeTwo + $textarea

        updateSingleHtmlFromModal: ->
            formOptionResultText = @modal.$formOptionModalInput.val()
            @$formOptionResultHtml.removeClass 'hidden'
            $code = "<code>#{formOptionResultText}</code>"
            $input = "<input type='text' class='form-option-input hidden' name='#{@name}' value='#{formOptionResultText}' />"
            @$formOptionInput = $($input)
            @$formOptionInput.val(formOptionResultText)
            @$formOptionResultHtml.find('.result-container').html $code + $input
            
    )

    # Terms & Conditions
    FormTermsOptionModal = Garnish.Modal.extend(
        option: null
        $form: null
        $modalLabelField: null
        $modalCopyField: null

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
                    '<div class="fb-field field-input">'
                        '<div class="input-hint">'
                            option.$container.data('input-hint-input')
                        '</div>'
                    '</div>'
                    '<div class="fb-field field-textarea">'
                        '<div class="input-hint">'
                            option.$container.data('input-hint-textarea')
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

            $input = '<input type="text" class="text code form-option-modal-input" size="50">'
            $textarea = '<textarea class="text form-option-modal-textarea" id="termsAndConditionsCopy" rows="10"></textarea>'

            @$modalLabelField = @$form.find '.field-input'
            @$modalCopyField = @$form.find '.field-textarea'

            @$modalLabelField.append($input)
            @$modalCopyField.append($textarea)

            # Redactor
            self = @
            $textareaRedactor = $('#termsAndConditionsCopy').redactor
                maxHeight: 160
                minHeight: 150
                maxWidth: '400px'
                buttons: [
                    'bold'
                    'italic'
                    'link'
                    'horizontalrule'
                ]
                plugins: [
                    'alignment'
                    'inlinestyle'
                ]
                callbacks: init: ->
                    if self.option.$formOptionInputTwo
                        @insert.set self.option.$formOptionInputTwo.val()

            @show()

            @$modalLabelInput = body.find '.form-option-modal-input'
            @$modalCopyInput = body.find '.form-option-modal-textarea'

            setTimeout $.proxy((->
                @$modalLabelInput.focus()
            ), this), 100

            if @option.$formOptionInput
                @$modalLabelInput.val(@option.$formOptionInput.val())

            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        cancel: () ->
            if !@option.editing
                @option.$editFormOption.addClass 'hidden'
                @option.$container.removeClass 'option-enabled'
                @option.$enableFormOption.val ''
                @option.$enableFormOption.prop 'checked', false
                if @option.$formOptionInput
                    @option.$formOptionInput.val ''
                @option.$formOptionResultHtml.html ''
                @option.$toggleBtn.html 'ENABLE'
                @closeModal()
            else
                @closeModal()

        hide: () ->
            @cancel()

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
                labelResut: @$modalLabelInput.val()
                copyResult: @$modalCopyInput.val()

            console.log data

            if !data.labelResut && !data.copyResult
                @$modalLabelField.addClass 'error'
                @$modalCopyField.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @closeModal()
                @$form[0].reset()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))
    )


    FormOptionModal = Garnish.Modal.extend(
        option: null
        $form: null
        $formOptionModalInput: null
        $formOptionModalField: null
        
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
                    '<div class="fb-field">'
                        '<div class="input-hint">'
                            option.$container.data('input-hint')
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

            $input = '<input type="text" class="text code form-option-modal-input" size="50">'

            if option.$container.data('input-type') == 'select'
                $input = $('<div class="select"><select class="form-option-modal-input" /></div>')
                inputOptions = option.$container.data 'input-options'
                $.each inputOptions, (i, item) ->
                    $input.find('select').append $('<option>',
                        value: item.value
                        text: item.label)

            @$form.find('.fb-field').append($input)

            @show()
            @$formOptionModalInput = body.find '.form-option-modal-input'

            setTimeout $.proxy((->
                @$formOptionModalInput.focus()
            ), this), 100

            if @option.$formOptionInput
                @$formOptionModalInput.val(@option.$formOptionInput.val())
            
            @$formOptionModalField = body.find '.fb-field'

            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        cancel: () ->
            if !@option.editing
                @option.$editFormOption.addClass 'hidden'
                @option.$container.removeClass 'option-enabled'
                @option.$enableFormOption.val ''
                @option.$enableFormOption.prop 'checked', false
                if @option.$formOptionInput
                    @option.$formOptionInput.val ''
                @option.$formOptionResultHtml.html ''
                @option.$toggleBtn.html 'ENABLE'
                @closeModal()
            else
                @closeModal()

        hide: () ->
            @cancel()

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
                formOptionResultText: @$formOptionModalInput.val()

            if !data.formOptionResultText
                @$formOptionModalField.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @closeModal()
                @$form[0].reset()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))
    )

$(document).ready ->
    $('.option-item').each (i, el) ->
        new FormOption(el)