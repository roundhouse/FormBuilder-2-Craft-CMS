Craft.FileUploadsIndex = Garnish.Base.extend(

    $container: $('.upload-details')
    elementIndex: null

    init: (elementIndex, container, settings) ->

        @elementIndex = elementIndex
        @$container = $(container)
        @setSettings settings, Craft.BaseElementIndexView.defaults

        # Create a "loading-more" spinner
        @$loadingMoreSpinner = $('<div class="centeralign hidden">' + '<div class="spinner loadingmore"></div>' + '</div>').insertAfter(@$container)

        @$elementContainer = @getElementContainer()
        $elements = @$elementContainer.children()

        if @settings.context == 'index'
            @addListener @$elementContainer, 'dblclick', (ev) ->
                `var $element`
                $target = $(ev.target)
                if $target.hasClass('element')
                  $element = $target
                else
                  $element = $target.closest('.element')
                if $element.length
                    @createElementEditor $element

    getElementContainer: () ->
        @$table = @$container.find('table:first')
        @$table.children 'tbody:first'

    createElementEditor: ($element) ->
        new (Craft.ElementEditor)($element,
            onSaveElement: $.proxy(((response) ->
                Craft.cp.displayNotice Craft.t('Asset updated')
        ), this))

)

Garnish.$doc.ready ->
    $('.submission-action-trigger').on 'click', (e) ->
        e.preventDefault()
        formId = $(this).data 'form-id'
        entryId = $(this).data 'entry-id'
        fileIds = $(this).data 'file-ids'

        $menu = $('<div class="tout-dropdown"/>').html(
            '<ul class="form-item-menu">' +
            '</ul>')

        $('<li><a href="'+window.FormBuilder.adminUrl+'/entries" class="download-all-files">Download All</a></li>').appendTo($menu.find('ul'))

        new (Garnish.HUD)($(this), $menu,
            hudClass: 'hud fb-hud submissionhud'
            closeOtherHUDs: false)

        $menu.find('.download-all-files').on 'click', (e) ->
            e.preventDefault()
            Craft.cp.displayNotice Craft.t('Downloading...')
            data =
                ids: fileIds
                formId: formId
            Craft.postActionRequest 'formBuilder2/entry/downloadAllFiles', data, $.proxy(((response, textStatus) ->
                if response.success
                    window.location = '/actions/formBuilder2/entry/downloadFiles?filePath=' + response.filePath
                    Craft.cp.displayNotice Craft.t('Download Successful')
                else
                    Craft.cp.displayError Craft.t(response.message)
                for hudID of Garnish.HUD.activeHUDs
                    Garnish.HUD.activeHUDs[hudID].hide()
            ), this)