changeFont = (target, size) ->
    target.css 'font-size', size + 'px'

$ ->
    # Change Body container size
    $('#templateBodyContainerWidth').on 'change keyup', (e) ->
        $('#cc-wrapper').css 'width', $(this).val() + 'px'
        $('.size-info').html $(this).val() + 'px'
    
    # Change Body background color
    $('#templateBodyBackgroundColor').on 'change', (e) ->
        $('#cc-body').css 'backgroundColor', $(this).val()
    
    # Change Body font size
    bodyFontRange = document.getElementById('templateBodyTextSize')
    footerFontRange = document.getElementById('templateFooterTextSize')

    Array::slice.call(document.querySelectorAll('.text-size'), 0).forEach (bt) ->
        bt.addEventListener 'click', (e) ->
            text = $(@.closest('.text-content')).find('.body')
            target = $(@).data 'target'
            action = $(@).data 'action'
            switch action
                when 'increase'
                    document.getElementById(target).stepUp(1)
                when 'decrease'
                    document.getElementById(target).stepDown(1)
            changeFont(text, document.getElementById(target).value)

    bodyFontRange.addEventListener 'change', (->
        text = $(@.closest('.text-content')).find('.body')
        changeFont(text, $(this).val())
    ), false

    footerFontRange.addEventListener 'change', (->
        text = $(@.closest('.text-content')).find('.body')
        changeFont(text, $(this).val())
    ), false
    


templateContent = Garnish.Base.extend(
    copy: null
    init: ->
        @copy = []
        $container = $('#cc-wrapper')
        $copy = $container.find('.text-content')
        i = 0
        while i < $copy.length
            message = new ContentCopy($copy[i])
            @copy.push message
            i++
)

ContentCopy = Garnish.Base.extend(
    $container: null
    templateId: null
    copyType: null
    copyText: null
    $body: null
    modal: null

    init: (textContainer) ->
        @$container = $(textContainer)
        @templateId = @$container.attr('data-template-id')
        @copyType = @$container.attr('data-type')
        @copyText = @$container.attr('data-copy')
        @$body = @$container.find('.body:first')

        @addListener @$body, 'click', 'edit'

    edit: ->
        if !@modal
            @modal = new ContentCopyModal(this)
        else
            @modal.show()

    updateHtmlFromModal: (data) ->
        @$body.parent().addClass 'has-text'
        @$body.html data.copy
)

ContentCopyModal = Garnish.Modal.extend(
    copy: null
    $copyInput: null
    $saveBtn: null
    $cancelBtn: null
    $spinner: null
    loading: false

    init: (copy) ->
        @copy = copy
        @base null, resizable: true
        @loadContainer()

    loadContainer: () ->
        data =
            templateId: @copy.templateId
            copyType: @copy.copyType
            copy: @copy.copyText

        if typeof Craft.csrfTokenName != 'undefined' and typeof Craft.csrfTokenValue != 'undefined'
            data[Craft.csrfTokenName] = Craft.csrfTokenValue

        $.post Craft.getUrl('formbuilder2/templates/partials/_modal'), data, $.proxy(((response, textStatus, jqXHR) ->
            if textStatus == 'success'
                if !@$container
                    $container = $(
                        '<div class="modal fitted">' +
                        '<form accept-charset="UTF-8">' +
                        '    <div class="body">' +
                        '        <div class="content">' +
                        '            <div class="main">'+response+'</div>' +
                        '        </div>' +
                        '    </div>' +
                        '    <div class="footer">' +
                        '        <div class="buttons right">' +
                        '            <input type="button" class="btn cancel" value="Cancel">' +
                        '            <input type="submit" class="btn submit" value="Set Copy">' +
                        '        </div>' +
                        '    </div>' +
                        '</form>'
                        '</div>'
                    ).appendTo(Garnish.$bod)
                    @setContainer $container
                    @show()
                else
                    @$container.html response

                @$copyInput = @$container.find('.'+data.copyType+':first')
                @$saveBtn = @$container.find('.submit:first')
                @$cancelBtn = @$container.find('.cancel:first')
                @$spinner = @$container.find('.spinner:first')

                @addListener @$container, 'submit', 'setTemplate'
                @addListener @$cancelBtn, 'click', 'cancel'
        ), this)

    setTemplate: (event) ->
        event.preventDefault()
        if @loading
            return
        data =
            copy: @$copyInput.val().replace(/\n/g, '<br>')

        @$copyInput.removeClass 'error'
        if !data.copy
            @$copyInput.addClass 'error'
            Garnish.shake @$container
            return

        $('#field-'+@copy.copyType).val(data.copy)
        @copy.updateHtmlFromModal(data)
        @hide()
        Craft.cp.displayNotice Craft.t('Copy set')


    cancel: ->
        @hide()
        if @copy
            @copy.modal = null

)

new templateContent
# TemplatePicker = Garnish.Base.extend(
#     $element: $('#templateLayout')

#     init: () ->
#         that = this

#         @addListener @$element, 'change', (ev) ->
#             params = 
#               templateName: ev.target.value

#             Craft.postActionRequest 'formBuilder2/template/getTemplateByName', params, $.proxy(((response, textStatus) ->
#                 console.log response
#                 $('input[name="templateLayout[fileNameCleaned]"]').val response.fileNameCleaned
#                 $('input[name="templateLayout[fileExtension]"]').val response.fileExtension
#                 $('input[name="templateLayout[filePath]"]').val response.filePath
#                 $('input[name="templateLayout[fileContents]"]').val response.fileContents
#             ), that)

# )

# Craft.EmailTemplates = Garnish.Base.extend(

#     $this: null
#     $parentInput: null
#     $container: $('#emailTemplates')
#     $data: null
#     templates: null
#     templateId: null
#     modals: null

#     init: () ->
#         that = this
#         @$templates = @$container.find('.preview-template')
#         @modals = []

#         @$templates.each (i, e) ->
#             target = $(e).data 'template'
#             that.initializeModal(target)

#         @addListener @$container.find('.preview-template'), 'click', (ev) ->
#             ev.preventDefault()
#             target = $(ev.target).data 'template'
#             params = 
#               templateId: target
            
#             Craft.postActionRequest 'formBuilder2/template/getEmailTemplate', params, $.proxy(((response, textStatus) ->
#                 console.log response
#                 console.log that.modals[target].$container.find('.main').html response
#                 that.modals[target].show()
#             ), that)


#     initializeModal: (id) ->



#         $modal = $(
#             '<div class="modal elementselectormodal" data-id="' + id + '">' +
#             '    <div class="body">' +
#             '        <div class="content">' +
#             '            <div class="main">HIIIII</div>' +
#             '        </div>' +
#             '    </div>' +
#             '    <div class="footer">' +
#             '        <div class="buttons left secondary-buttons">' +
#             '            <div class="btn load-svg dashed">Reload SVG Code</div>' +
#             '        </div>' +
#             '        <div class="buttons right">' +
#             '            <div class="btn submit">Ok</div>' +
#             '        </div>' +
#             '    </div>' +
#             '</div>'
#         )

#         myModal = new (Garnish.Modal)($modal,
#             autoShow: false
#             resizable: false)

#         # oldWidth = $modal.width()
#         # oldDisplay = 'none'
#         # timeout = null

#         # observer = new MutationObserver((mutations) ->
#         #     mutations.forEach (mutation) ->
#         #         console.log mutation
#         # )


#         # observerConfig = 
#         #     attributes: true
#         #     childList: false
#         #     characterData: false
#         #     subtree: false
#         #     attributeOldValue: false
#         #     characterDataOldValue: false
#         #     attributeFilter: [ 'style' ]
#         # observer.observe $modal[0], observerConfig


#         $modal.find('.submit').click ->
#             myModal.hide()

#         @modals[id] = myModal

#         # @elementIndex = elementIndex
#         # @$container = $(container)
#         # @setSettings settings, Craft.BaseElementIndexView.defaults

#         # Create a "loading-more" spinner
#         # @$loadingMoreSpinner = $('<div class="centeralign hidden">' + '<div class="spinner loadingmore"></div>' + '</div>').insertAfter(@$container)

#         # @$elementContainer = @getElementContainer()
#         # $elements = @$elementContainer.children()

#         # if @settings.context == 'index'
#         #     @addListener @$elementContainer, 'dblclick', (ev) ->
#         #         `var $element`
#         #         $target = $(ev.target)
#         #         if $target.hasClass('element')
#         #           $element = $target
#         #         else
#         #           $element = $target.closest('.element')
#         #         if $element.length
#         #             @createElementEditor $element

#     # getElementContainer: () ->
#         # @$table = @$container.find('table:first')
#         # @$table.children 'tbody:first'

#     # createElementEditor: ($element) ->
#         # new Craft.ElementEditor($element)
#         # new (Craft.ElementEditor)($element,
#         #     onSaveElement: $.proxy(((response) ->
#         #         console.log response
#         # ), this))

# )