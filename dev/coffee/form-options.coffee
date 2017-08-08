if $ and window.Garnish
    FormOption = Garnish.Base.extend(
        $container: null
        $enableFormOption: null
        $formOptionInput: null
        $formOptionResultHtml: null
        $toggleBtn: null
        $editFormOption: null

        modal: null
        editing: false
     
        init: (container) ->
            @$container = $(container)
            @$enableFormOption = @$container.find '.enable-form-option'
            @$formOptionInput = @$container.find '.form-option-input'
            @$formOptionResultHtml = @$container.find '.option-result'
            @$editFormOption = @$container.find '.option-edit'
            @$toggleBtn = @$container.find '.toggle-option'

            @addListener @$editFormOption, 'click', 'editFormOption'
            @addListener @$toggleBtn, 'click', 'edit'

            if @$container.hasClass 'option-enabled'
                if @$container.data('modal')
                    @$editFormOption.removeClass 'hidden'

        editFormOption: (e) ->
            e.preventDefault()
            @editing = true
            if !@modal
                @modal = new FormOptionModal(@)
            else
                @modal.show()
                @modal.$formOptionModalInput.removeClass 'error'

        edit: (e) ->
            @editing = false
            e.preventDefault()
            if @$container.hasClass 'option-enabled'
                @$editFormOption.addClass 'hidden'
                @$container.removeClass 'option-enabled'
                @$enableFormOption.val('')
                @$enableFormOption.prop 'checked', false
                @$formOptionInput.val ''
                @$formOptionResultHtml.addClass 'hidden'
                if @$container.data('modal') && @modal
                    @modal.$formOptionModalInput.val ''
                @$toggleBtn.html 'ENABLE'
            else
                @$editFormOption.removeClass 'hidden'
                @$container.addClass 'option-enabled'
                @$enableFormOption.val('1')
                @$enableFormOption.prop 'checked', true
                @$toggleBtn.html 'DISABLE'

                if @$container.data('modal')
                    if !@modal
                        @modal = new FormOptionModal(@)
                    else
                        @modal.show()

        updateHtmlFromModal: ->
            formOptionResultText = @modal.$formOptionModalInput.val()
            @$formOptionInput.val(formOptionResultText)
            @$formOptionResultHtml.removeClass 'hidden'
            @$formOptionResultHtml.find('code').html formOptionResultText
    )

    FormOptionModal = Garnish.Modal.extend(
        option: null
        $formOptionModalInput: null
        
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

            $input = '<input type="text" class="text code form-option-modal-input" size="50">'
            if option.$container.data('input-type') == 'select'
                $input = $('<div class="select"><select class="form-option-modal-input" /></div>')
                inputOptions = option.$container.data 'input-options'
                $.each inputOptions, (i, item) ->
                    $input.find('select').append $('<option>',
                        value: item.value
                        text: item.label)


            @$form.find('.body').append($input)

            @show()
            @$formOptionModalInput = body.find '.form-option-modal-input'
            @$formOptionModalInput.val(@option.$formOptionInput.val())
            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        cancel: () ->
            if !@option.editing
                @option.$editFormOption.addClass 'hidden'
                @option.$container.removeClass 'option-enabled'
                @option.$enableFormOption.val('')
                @option.$enableFormOption.prop 'checked', false
                @option.$formOptionInput.val ''
                @option.$formOptionResultHtml.addClass 'hidden'
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
                @$formOptionModalInput.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @closeModal()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))
    )

$(document).ready ->
    $('.option-item').each (i, el) ->
        new FormOption(el)