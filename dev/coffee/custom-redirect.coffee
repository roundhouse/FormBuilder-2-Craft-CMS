if $ and window.Garnish
    CustomRedirect = Garnish.Base.extend(
        $container: null

        $enableCustomRedirectInput: null
        $customRedirectInput: null
        $templatePathResult: null

        $toggleBtn: null
        $editPath: null

        modal: null
     
        init: (container) ->
            @$container = $(container)
            @$enableCustomRedirectInput = @$container.find '#input-option-redirect'
            @$templatePathResult = @$container.find '.option-result'
            @$customRedirectInput = @$container.find '#customRedirectUrl'
            @$toggleBtn = @$container.find '.toggle-option'
            @$editPath = @$container.find '.option-edit'
            @addListener @$toggleBtn, 'click', 'edit'
            @addListener @$editPath, 'click', 'editPath'

            if @$container.hasClass 'option-enabled'
                @$editPath.removeClass 'hidden'

        editPath: (e) ->
            e.preventDefault()
            if !@modal
                @modal = new CustomRedirectModal(@)
            else
                @modal.show()

        edit: (e) ->
            e.preventDefault()
            if @$container.hasClass 'option-enabled'
                @$editPath.addClass 'hidden'
                @$container.removeClass 'option-enabled'
                @$enableCustomRedirectInput.prop 'checked', false
                @$customRedirectInput.val ''
                @$templatePathResult.addClass 'hidden'
                @modal.$customRedirectInputModal.val ''
                @$toggleBtn.html 'ENABLE'
            else
                @$editPath.removeClass 'hidden'
                @$container.addClass 'option-enabled'
                @$enableCustomRedirectInput.prop 'checked', true
                @$toggleBtn.html 'DISABLE'

                if !@modal
                    @modal = new CustomRedirectModal(@)
                else
                    @modal.show()

        updateHtmlFromModal: ->
            redirectUrl = @modal.$customRedirectInputModal.val()
            @$customRedirectInput.val(redirectUrl)
            @$templatePathResult.removeClass 'hidden'
            @$templatePathResult.find('code').html redirectUrl
    )

    CustomRedirectModal = Garnish.Modal.extend(
        option: null
        $customRedirectInputModal: null

        init: (option) ->
            @option = option
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal" id="custom-redirect-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        Craft.t('Custom Redirect URL')
                    '</span>'
                '</header>'
                '<div class="body">'
                    '<input type="text" class="text code input-customredirecturl" size="50">'
                    '<div class="path-text">PATH</div>'
                '</div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)
            @show()
            @$customRedirectInputModal = body.find '.input-customredirecturl'
            @$customRedirectInputModal.val(@option.$customRedirectInput.val())
            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'hide'
            @addListener @$form, 'submit', 'onFormSubmit'

        onFormSubmit: (e) ->
            e.preventDefault()
            data = 
                customRedirectUrl: @$customRedirectInputModal.val()

            if !data.customRedirectUrl
                @$customRedirectInputModal.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @hide()
                Craft.cp.displayNotice(Craft.t('Custom Redirect Updated'))
    )