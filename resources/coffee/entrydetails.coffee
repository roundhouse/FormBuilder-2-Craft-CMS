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
        new Craft.ElementEditor($element)
        # new (Craft.ElementEditor)($element,
        #     onSaveElement: $.proxy(((response) ->
        #         console.log response
        # ), this))

)