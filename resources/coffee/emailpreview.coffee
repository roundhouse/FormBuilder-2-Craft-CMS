Craft.EmailTemplates = Garnish.Base.extend(

    $this: null
    $parentInput: null
    $container: $('#emailTemplates')
    $data: null
    templates: null
    templateId: null
    modals: null

    init: () ->
        that = this
        @$templates = @$container.find('.preview-template')
        @modals = []

        @$templates.each (i, e) ->
            target = $(e).data 'template'
            that.initializeModal(target)

        @addListener @$container.find('.preview-template'), 'click', (ev) ->
            ev.preventDefault()
            target = $(ev.target).data 'template'
            params = 
              templateId: target
            
            Craft.postActionRequest 'formBuilder2/getEmailTemplate', params, $.proxy(((response, textStatus) ->
                console.log response
                console.log that.modals[target].$container.find('.main').html response
                that.modals[target].show()
            ), that)


    initializeModal: (id) ->



        $modal = $(
            '<div class="modal elementselectormodal" data-id="' + id + '">' +
            '    <div class="body">' +
            '        <div class="content">' +
            '            <div class="main">HIIIII</div>' +
            '        </div>' +
            '    </div>' +
            '    <div class="footer">' +
            '        <div class="buttons left secondary-buttons">' +
            '            <div class="btn load-svg dashed">Reload SVG Code</div>' +
            '        </div>' +
            '        <div class="buttons right">' +
            '            <div class="btn submit">Ok</div>' +
            '        </div>' +
            '    </div>' +
            '</div>'
        )

        myModal = new (Garnish.Modal)($modal,
            autoShow: false
            resizable: false)

        # oldWidth = $modal.width()
        # oldDisplay = 'none'
        # timeout = null

        # observer = new MutationObserver((mutations) ->
        #     mutations.forEach (mutation) ->
        #         console.log mutation
        # )


        # observerConfig = 
        #     attributes: true
        #     childList: false
        #     characterData: false
        #     subtree: false
        #     attributeOldValue: false
        #     characterDataOldValue: false
        #     attributeFilter: [ 'style' ]
        # observer.observe $modal[0], observerConfig


        $modal.find('.submit').click ->
            myModal.hide()

        @modals[id] = myModal

        # @elementIndex = elementIndex
        # @$container = $(container)
        # @setSettings settings, Craft.BaseElementIndexView.defaults

        # Create a "loading-more" spinner
        # @$loadingMoreSpinner = $('<div class="centeralign hidden">' + '<div class="spinner loadingmore"></div>' + '</div>').insertAfter(@$container)

        # @$elementContainer = @getElementContainer()
        # $elements = @$elementContainer.children()

        # if @settings.context == 'index'
        #     @addListener @$elementContainer, 'dblclick', (ev) ->
        #         `var $element`
        #         $target = $(ev.target)
        #         if $target.hasClass('element')
        #           $element = $target
        #         else
        #           $element = $target.closest('.element')
        #         if $element.length
        #             @createElementEditor $element

    # getElementContainer: () ->
        # @$table = @$container.find('table:first')
        # @$table.children 'tbody:first'

    # createElementEditor: ($element) ->
        # new Craft.ElementEditor($element)
        # new (Craft.ElementEditor)($element,
        #     onSaveElement: $.proxy(((response) ->
        #         console.log response
        # ), this))

)