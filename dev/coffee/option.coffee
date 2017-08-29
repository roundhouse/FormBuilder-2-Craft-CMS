if $ and window.Garnish
    # Modal Item
    Option = Garnish.Base.extend(
        $container: null
        $resultWrapper: null
        $resultContainer: null

        $toggle: null
        $edit: null

        $data: null
        $inputs: null

        enabled: false
        editing: false
        hasModal: false
        hasTags: false

        $enableCheckbox: null

        $fields: null

        init: (container) ->
            self = @
            @$container = $(container)
            @$resultWrapper = @$container.find '.option-wrapper'
            @$resultContainer = @$container.find '.option-result'

            @$toggle = @$container.find '.option-toggle'
            @$edit = @$container.find '.option-edit'

            if @$container.hasClass 'tags'
                @hasTags = true

            @$inputs = @$container.data 'inputs'
            @$data = @$container.data 'modal'
            if @$data
                @$fields = @$data.fields
                @hasModal = true

            # Check Enabled
            if @$inputs
                $.each @$inputs, (i, item) ->
                    if item.type == 'checkbox'
                        self.enabled = item.checked
                        name = item.name
                        self.$enableCheckbox = $("[name='#{name}']")
                    else
                        self.enabled = true

            @addListener @$toggle, 'click', 'toggle'
            @addListener @$edit, 'click', 'edit'

            if @enabled
                @editing = true
                # Check if option has modal
                if @$data
                    @$edit.removeClass 'hidden'
                

        toggle: (e) ->
            e.preventDefault()
            @editing = false

            if @$container.hasClass 'option-enabled'
                @$edit.addClass 'hidden'
                @$container.removeClass 'option-enabled'
                @$resultWrapper.addClass 'hidden'
                @$resultContainer.html ''
                @$toggle.html 'ENABLE'
            else
                @$edit.removeClass 'hidden'
                @$container.addClass 'option-enabled'
                @$toggle.html 'DISABLE'
                @enableOption()
                if @hasModal
                    if !@modal
                        @modal = new Modal(@)
                    else
                        @modal.$form.find('.fb-field').removeClass 'error'
                        @modal.$form[0].reset()
                        @modal.show()
        
        edit: (e) ->
            self = @
            @editing = true
            e.preventDefault()
            if @editing
                if !@modal
                    @modal = new Modal(@)
                else
                    @modal.$form.find('.fb-field').removeClass 'error'
                    $.each @$inputs, (i, item) ->
                        if item.type != 'checkbox'
                            currentValue = $("[name='#{item.name}']").val()
                            className = item.name.replace(/[_\W]+/g, "-").slice(0, -1)
                            $.each self.modal.$modalInputs, (i, item) ->
                                input = $(item)
                                if input.hasClass className
                                    input.val currentValue
                    @modal.show()

        enableOption: ->
            if @$enableCheckbox
                @$enableCheckbox.val 'true'
                @$enableCheckbox.prop 'checked', true

        updateHtmlFromModal: ->
            self = @
            if @hasTags
                totalResults = @$resultContainer.find('.result-item').length
                if totalResults then index = totalResults else index = 0
                $resultHtml = $('<div class="result-item" data-result-index="'+index+'">').appendTo(Garnish.$bod)
                name = $(@modal.$modalInputs[0]).data 'name'
                key = $(@modal.$modalInputs[0]).val()
                value = $(@modal.$modalInputs[1]).val()
                body = $([
                    '<div class="option-result-actions">'
                        '<a href="#" class="option-result-delete" title="'+Craft.t('Delete')+'"><svg width="19" height="19" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path d="M9.521064 18.5182504c-4.973493 0-9.019897-4.0510671-9.019897-9.030471 0-4.98018924 4.046404-9.0312563 9.019897-9.0312563s9.019897 4.05106706 9.019897 9.0312563c0 4.9794039-4.046404 9.030471-9.019897 9.030471zm0-16.05425785c-3.868359 0-7.015127 3.15021907-7.015127 7.02378685 0 3.8727824 3.146768 7.0237869 7.015127 7.0237869 3.86836 0 7.015127-3.1510045 7.015127-7.0237869 0-3.87356778-3.146767-7.02378685-7.015127-7.02378685zm3.167945 10.02870785c-.196085.1955634-.452564.2937378-.708258.2937378-.256479 0-.512958-.0981744-.709042-.2937378L9.521064 10.739699 7.77042 12.4927004c-.196085.1955634-.452564.2937378-.709043.2937378-.256478 0-.512957-.0981744-.708258-.2937378-.391385-.391912-.391385-1.0272965 0-1.4192086l1.750645-1.7530015-1.750645-1.7530015c-.391385-.391912-.391385-1.02729655 0-1.41920862.391385-.39191207 1.025131-.39191207 1.417301 0L9.521064 7.9012817l1.750645-1.75300152c.391385-.39191207 1.025915-.39191207 1.4173 0 .391385.39191207.391385 1.02729662 0 1.41920862l-1.750644 1.7530015 1.750644 1.7530015c.391385.3919121.391385 1.0272966 0 1.4192086z" fill="#8094A1" fill-rule="evenodd"/></svg></a>'
                    '</div>'
                    '<code><span class="option-key input-hint">'+key+'</span> '+value+'</code>'
                    '<input type="hidden" name="'+name+'['+index+'][key]" value="'+key+'" />'
                    '<input type="hidden" name="'+name+'['+index+'][value]" value="'+value+'" />'
                ].join('')).appendTo($resultHtml)

                @$resultContainer.append $resultHtml
                new Tag($resultHtml, @modal)
            else
                @$resultContainer.html ''
                $.each @modal.$modalInputs, (i, item) ->
                    value = $(item).val()
                    if value
                        name = $(item).data 'name'
                        hint = $(item).data 'hint'
                        $("[name='#{name}']").val value
                        self.$resultContainer.append $("<code><span class='input-hint'>#{hint}:</span> #{value}</code>")

            @$resultWrapper.removeClass 'hidden'

    )

$(document).ready ->
    $('.option-item').each (i, el) ->
        new Option(el)