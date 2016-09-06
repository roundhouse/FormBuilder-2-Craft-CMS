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

    # Change Template background color
    $('#templateBackgroundColor').on 'change', (e) ->
        $('#cc-wrapper').css 'backgroundColor', $(this).val()
    
    # Change Optional Font Size
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

    # Change Optional Font Color
    $('#templateBodyTextColor').on 'change', (e) ->
        text = $(@.closest('.text-content')).find('.body')
        text.css 'color', $(this).val()

    $('#templateFooterTextColor').on 'change', (e) ->
        text = $(@.closest('.text-content')).find('.body')
        text.css 'color', $(this).val()
    
    # Delete Text
    $('.delete-text').on 'click', (e) ->
        e.preventDefault()
        target = $(this).data 'target'
        placeholder = $(this).parent().find('.body').data 'placeholder'
        $('.'+target).val ''
        $(this).parent().find('.body').addClass('txt').html placeholder
        $(this).addClass 'hidden'
        $(this).parent().find('.text-actions').addClass 'hidden'


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
        @$body.parent().addClass 'text-set'
        @$body.parent().find('p').css 'white-space', 'pre'
        @$body.parent().find('.text-actions').removeClass 'hidden'
        @$body.parent().find('.delete').removeClass 'hidden'
        @$body.removeClass 'txt'
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
            copy: @$copyInput.val()

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