if $ and window.Garnish
    FormOption = Garnish.Base.extend(
        $container: null

        $enableFormOption: null
        $formOptionInput: null
        $formOptionResultHtml: null

        $toggleBtn: null
        $editFormOption: null

        modal: null
     
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
            if !@modal
                @modal = new FormOptionModal(@)
            else
                @modal.show()
                @modal.$formOptionModalInput.removeClass 'error'

        edit: (e) ->
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
            @$form = $('<form class="modal fitted formbuilder-modal" id="custom-redirect-modal">').appendTo(Garnish.$bod)
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
            @addListener @$cancelBtn, 'click', 'hide'
            @addListener @$form, 'submit', 'onFormSubmit'

        onFormSubmit: (e) ->
            e.preventDefault()
            data = 
                formOptionResultText: @$formOptionModalInput.val()

            if !data.formOptionResultText
                @$formOptionModalInput.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @hide()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))
    )

$(document).ready ->
    $('.option-item').each (i, el) ->
        new FormOption(el)