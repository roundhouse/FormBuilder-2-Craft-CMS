if $ and window.Garnish

    Fields = Garnish.Base.extend(
        $container: null
        $form: null
        $body: null

        $tagContainer: null
        $targetEl: null
        $target: null

        init: (container, form, target) ->
            self = @
            @$container = container
            @$form = $(form)
            @$body = @$form.find('.body')

            @$tagContainer = $('<div class="tags-container"></div>')
            @$body.append @$tagContainer
            
            tags = []
            $.each $.parseJSON(@$container.$fields), (i, item) ->
                tags[i] = "<div class='tag-btn tag-#{item.value}' data-tag='{#{item.value}}'>#{item.label}</div>"
            tags.push("<div class='tag-btn tag-date' data-tag='{date}'>Date</div>")
            @$tagContainer.html tags

            $.each @$container.$inputs, (i, item) ->
                if item.tags
                    self.$targetEl = item
            targetClassName = @$targetEl.name.replace(/[_\W]+/g, "-").slice(0, -1)
            @$target = $(".#{targetClassName}")

            $.each @$tagContainer.find('.tag-btn'), (i, item) ->
                new Field(item, self.$target)
    )

    Field = Garnish.Base.extend(
        $tag: null
        $target: null

        init: (tag, target) ->
            @$tag = $(tag)
            @$target = target

            @addListener @$tag, 'click', 'addTag'

        addTag: ->
            tag = @$tag.data 'tag'
            @$target.val(@$target.val() + tag)

    )