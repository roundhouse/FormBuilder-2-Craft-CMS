if $ and window.Garnish
    TemplateOption = Garnish.Base.extend(
        $container: null
        $enableFormOption: null
        $toggleBtn: null
        $editBtn: null

        $template: null

        editing: false
        type: null
        name: null

        init: (el, template) ->
            @$template = template
            @$container = $(el)
            @$enableTemplateContent = @$container.find '.enable-template-content'
            @$toggleBtn = @$container.find '.toggle-option'
            @$editBtn = @$container.find '.option-edit'

            @type = @$container.data 'type'
            @name = @$container.data 'name'

            @addListener @$toggleBtn, 'click', 'edit'
            @addListener @$editBtn, 'click', 'editContent'

        editContent: (e) ->
            e.preventDefault()
            @editing = true
            if !@modal
                @modal = new TemplateOptionModal(@)
            else
                if @$template.$CopyInput
                    @modal.$modalCopyInput.val(@$template.$CopyInput.val())
                @modal.show()
                @modal.$modalCopyInput.removeClass 'error'

        edit: (e) ->
            @editing = false
            e.preventDefault()
            if @$container.hasClass 'option-enabled'
                @$editBtn.addClass 'hidden'
                @$container.removeClass 'option-enabled'
                @$toggleBtn.html 'ENABLE'
                @$enableTemplateContent.val false
                @$enableTemplateContent.prop 'checked', false
            else
                @$editBtn.removeClass 'hidden'
                @$container.addClass 'option-enabled'
                @$toggleBtn.html 'DISABLE'
                @$enableTemplateContent.val true
                @$enableTemplateContent.prop 'checked', true

                if @type == 'header'
                    @$template.$headerHtml.removeClass 'hidden'
                else if @type == 'body'
                    @$template.$bodyHtml.removeClass 'hidden'
                else if @type == 'footer'
                    @$template.$footerHtml.removeClass 'hidden'

                if @$container.data('modal')
                    if !@modal then @modal = new TemplateOptionModal(@) else @modal.show()

        updateHtmlFromModal: ->
            console.log @type
            copy = @modal.$modalCopyInput.val()
            input = "<textarea type='text' class='template-input-textarea hidden' name='#{@name}'>#{copy}</textarea>"

            if @type == 'header'
                @$template.$headerHtml.html copy + input
            else if @type == 'body'
                @$template.$bodyHtml.html copy + input
            else if @type == 'footer'
                @$template.$footerHtml.html copy + input
    )

    TemplateOptionModal = Garnish.Modal.extend(
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
                '</header>'
                '<div class="body">'
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

            $textarea = '<textarea class="text form-option-modal-textarea" id="'+@option.type+'-textarea" rows="10"></textarea>'

            @$modalCopyField = @$form.find '.field-textarea'

            @$modalCopyField.append($textarea)

            # Redactor
            self = @
            $textareaRedactor = $('#'+@option.type+'-textarea').redactor
                maxHeight: 160
                minHeight: 150
                maxWidth: '500px'
                buttons: [
                    'bold'
                    'italic'
                    'link'
                    'horizontalrule'
                ]
                plugins: [
                    'fontfamily'
                    'fontsize'
                    'alignment'
                    'fontcolor'
                ]
                callbacks: init: ->
                    if self.option.$formOptionInputTwo
                        @insert.set self.option.$formOptionInputTwo.val()

            @show()

            @$modalCopyInput = body.find '.form-option-modal-textarea'

            setTimeout $.proxy((->
                @$modalCopyInput.focus()
            ), this), 100

            @$saveBtn = body.find '.submit'
            @$cancelBtn = body.find '.cancel'
            @addListener @$cancelBtn, 'click', 'cancel'
            @addListener @$form, 'submit', 'save'

        cancel: () ->
            if !@option.editing
                @option.$editBtn.addClass 'hidden'
                @option.$container.removeClass 'option-enabled'
                @option.$enableFormOption.val ''
                @option.$enableFormOption.prop 'checked', false
                @option.$toggleBtn.html 'ENABLE'
                @option.$template.$headerHtml.addClass 'hidden'
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
                copyResult: @$modalCopyInput.val()

            console.log data

            if !data.copyResult
                @$modalCopyField.addClass 'error'
                Garnish.shake(@$container)
            else
                @option.updateHtmlFromModal()
                @closeModal()
                @$form[0].reset()
                Craft.cp.displayNotice(@option.$container.data('modal-success-message'))

    )

    EmailTemplate = Garnish.Base.extend(
        $container: null
        $headerHtml: null
        $bodyHtml: null
        $footerHtml: null

        $headerCopyInput: null
        $bodyCopyInput: null
        $footerCopyInput: null

        init: (el) ->
            @$container = $(el)
            @$headerHtml = @$container.find '.template-header'
            @$bodyHtml = @$container.find '.template-body'
            @$footerHtml = @$container.find '.template-footer'

            @$headerCopyInput = @$container.find '#header-copy-input'
            @$bodyCopyInput = @$container.find '#body-copy-input'
            @$footerCopyInput = @$container.find '#footer-copy-input'

    )

$(document).ready ->
    template = new EmailTemplate('#template-minimum-html')

    $('.template-item').each (i, el) ->
        new TemplateOption(el, template)

    templateContainerHtml = $('.template-container')

    $('#templateBackgroundColor').on 'change', (e) ->
        color = $(this).val()
        templateContainerHtml.css 'backgroundColor', color

    $('#templateBorderColor').on 'change', (e) ->
        color = $(this).val()
        templateContainerHtml.css 'borderColor', color

    $('#templateBorderWidth').on 'change input', (e) ->
        width = $(this).val()
        templateContainerHtml.css 'borderWidth', width + 'px'
    
    $('#templateBorderRadius').on 'change input', (e) ->
        radius = $(this).val()
        templateContainerHtml.css 'borderRadius', radius + 'px'

    $('#templateContainerPadding').on 'change input', (e) ->
        padding = $(this).val()
        templateContainerHtml.css 'padding', padding + 'px'

    $('.delete-template').on 'click', (e) ->
        e.preventDefault()
        templateId = $(this).data 'id'
        data = id: templateId
        if confirm Craft.t("Are you sure you want to delete this template?")
            Craft.postActionRequest 'formBuilder2/template/deleteTemplate', data, $.proxy(((response, textStatus) ->
                if textStatus == 'success'
                    window.location.href = '/admin/formbuilder2/templates'
            ), this)

    $('.template-actions').each (index, value) ->
        templateId = $(value).data 'template-id'
        templateHandle = $(value).data 'template-handle'
        templateName = $(value).data 'template-name'
        $menu = $('<div class="template"/>').html(
            '<ul class="action-item-menu">' +
                '<li>' +
                    '<a href="#" class="copy-handle" data-clipboard-text="'+templateHandle+'">' +
                        'Copy Handle' +
                    '</a>' +
                '</li>' +
                '<li>' +
                    '<a href="#" class="delete">' +
                    'Delete</a>' +
                '</li>' +
            '</ul>')

        $(value).on 'click', (e) ->
            e.preventDefault()
            formbuilderTemplate = new (Garnish.HUD)($(value).find('.template-action-trigger'), $menu,
                hudClass: 'hud fb-hud formhud'
                closeOtherHUDs: false)

        $menu.find('.copy-handle').on 'click', (e) ->
            e.preventDefault()
            new Clipboard('.copy-handle', text: (trigger) ->
                templateHandle
            )
            for hudID of Garnish.HUD.activeHUDs
                Garnish.HUD.activeHUDs[hudID].hide()

            Craft.cp.displayNotice Craft.t('Form Handle Copied')

        $menu.find('.delete').on 'click', (e) ->
            e.preventDefault()
            data = id: templateId
            if confirm Craft.t("Are you sure you want to delete #{templateName}?")
                Craft.postActionRequest 'formBuilder2/template/deleteTemplate', data, $.proxy(((response, textStatus) ->
                    if response.success
                        $row = $('#formbuilder-template-'+templateId)
                        templateTable.sorter.removeItems($row)
                        $row.remove()
                        if response.count == 1
                            $('.templates-table').remove()
                            $('.templates-container').after '<div class="no-templates" id="notemplates"><span class="title">Hello! You don\'t have any templates yet.</span></div>'
                        for hudID of Garnish.HUD.activeHUDs
                            Garnish.HUD.activeHUDs[hudID].hide()
                            Craft.cp.displayNotice Craft.t('Template Deleted')

                ), this)