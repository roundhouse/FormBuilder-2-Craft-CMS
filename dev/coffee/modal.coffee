if $ and window.Garnish
    # Modal
    Modal = Garnish.Modal.extend(
        option: null
        $form: null
        $modalInputs: null
        $redactor: null

        $validationItems: []

        errors: []
        errorLength: 0

        init: (option) ->
            self = @
            @option = option
            @base()
            @$form = $('<form class="modal fitted formbuilder-modal">').appendTo(Garnish.$bod)
            @setContainer @$form
            body = $([
                '<header>'
                    '<span class="modal-title">'
                        option.$data.title
                    '</span>'
                    '<div class="instructions">'
                        option.$data.instructions
                    '</div>'
                '</header>'
                '<div class="body"></div>'
                '<footer class="footer">'
                    '<div class="buttons">'
                        '<input type="button" class="btns btn-modal cancel" value="'+Craft.t('Cancel')+'">'
                        '<input type="submit" class="btns btn-modal submit" value="'+Craft.t('Save')+'">'
                    '</div>'
                '</footer>'
            ].join('')).appendTo(@$form)

            $.each option.$inputs, (i, item) ->
                if item.type != 'checkbox'
                    className = item.name.replace(/[_\W]+/g, "-").slice(0, -1)
                    camelClassName = className.replace /-([a-z])/g, (g) ->
                                        g[1].toUpperCase()
                    if item.validation
                        validation = item.validation
                        validation['passed'] = false
                        validation['inputClass'] = className
                        self.$validationItems[i] = item

                    if item.type == 'textarea'
                        $input = "<textarea class='#{className}' value='#{item.value}' data-hint='#{item.hint}' data-name='#{item.name}' /></textarea>"
                    else if item.type == 'select'
                        $input = $.parseJSON(item.options)
                    else
                        $input = "<input type='#{item.type}' class='#{className}' value='#{item.value}' data-hint='#{item.hint}' data-name='#{item.name}' />"

                    self.renderInputs($input, item.value, item.type, item.name, item.hint, className)

            # Load Fields
            if @option.$container.hasClass 'has-fields'
                fields = new Fields(@option, @$form)

            @$modalInputs = @$form.find('.body').find('input, textarea, select')

            @show()

            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'

            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        renderInputs: (el, value, type, name, hint, className) ->
            if type == 'select'
                $input = $('<div class="fb-field">' +
                    '<div class="input-hint">' +
                        hint +
                    '</div>' +
                    '<div class="select"><select class='+className+' data-hint='+hint+' data-name='+name+' /></div>' +
                '</div>')
                $.each el, (i, item) ->
                    $input.find('select').append $('<option>',
                        value: item.value
                        text: item.label)
                $input.find('select').val value
            else
                $input = $('<div class="fb-field">' +
                    '<div class="input-hint">' +
                        hint +
                    '</div>' +
                    el +
                '</div>')
            
            @$form.find('.body').append($input)

            if type == 'textarea'
                @initRedactor(el)

        initRedactor: (item) ->
            className = $(item)[0].className
            el = @$form.find(".#{className}")
            el.redactor
                maxHeight: 160
                minHeight: 150
                maxWidth: '400px'
                buttons: ['bold', 'italic', 'link', 'horizontalrule']
                plugins: ['fontfamily', 'fontsize', 'alignment', 'fontcolor']
                callbacks: 
                    init: ->
                        console.log 'init'

            @$redactor = el.redactor('core.object')

        cancel: () ->
            if !@option.editing
                @option.$edit.addClass 'hidden'
                @option.$container.removeClass 'option-enabled'
                @option.$resultContainer.html ''
                @option.$toggle.html 'ENABLE'
                @disableOption()
                @closeModal()
            else
                @closeModal()

        disableOption: ->
            if @option.$enableCheckbox
                @option.$enableCheckbox.val ''
                @option.$enableCheckbox.prop 'checked', false

        hide: ->
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

        runValidation: (e) ->
            e.preventDefault()
            self = @
            console.log @$validationItems
            if @$validationItems
                $.each @$validationItems, (i, item) ->
                    input = self.$form.find(".#{item.validation.inputClass}")
                    if input.val().match(/^\d+$/)
                        item.validation.passed = true
                    else
                        item.validation.passed = false
                        Craft.cp.displayNotice(item.validation.errorMessage)
            else
                @save()

        save: (e) ->
            e.preventDefault()
            self = @

            if @option.$container.hasClass 'tags'
                @checkErrors()

                if @errors.length > 0
                    $.each self.errors, (i, item) ->
                        $(item).parent().addClass 'error'
                    Garnish.shake(@$container)
                else
                    @updateOption()
            else 
                @checkErrors()
                if @errorLength == @$modalInputs.length
                    $.each self.errors, (i, item) ->
                        if $(item).is('select')
                            $(item).parent().parent().addClass 'error'
                        else    
                            $(item).parent().addClass 'error'
                    Garnish.shake(@$container)
                else
                    @updateOption()

        checkErrors: ->
            self = @
            @errors = []
            @errorLength = 0
            $.each @$modalInputs, (i, item) ->
                if $(item).val() == ''
                    self.errors[i] = item
                    self.errorLength += 1

        updateOption: ->
            @option.updateHtmlFromModal()
            @closeModal()
            @$form[0].reset()
            Craft.cp.displayNotice(@option.$data.successMessage)
            

    )