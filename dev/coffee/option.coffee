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

        $enableCheckbox: null

        $fields: null

        init: (container) ->
            @$container = $(container)
            @$resultWrapper = @$container.find '.option-wrapper'
            @$resultContainer = @$container.find '.option-result'

            @$toggle = @$container.find '.option-toggle'
            @$edit = @$container.find '.option-edit'


            @$inputs = @$container.data 'inputs'
            @$data = @$container.data 'modal'
            if @$data
                @$fields = @$data.fields
                @hasModal = true

            # Enabled
            if @$inputs
                if @$inputs.hasOwnProperty 'checkbox'
                    @enabled = @$inputs['checkbox'].checked
                    name = @$inputs['checkbox'].name
                    @$enableCheckbox = $("[name='#{name}']")
                else
                    @enabled = true


            
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
            @$resultContainer.html ''
            $.each @modal.$modalInputs, (i, item) ->
                value = $(item).val()
                name = $(item).data 'name'
                hint = $(item).data 'hint'
                $("[name='#{name}']").val value
                self.$resultContainer.append $("<code><span>#{hint}:</span> #{value}</code>")

            @$resultWrapper.removeClass 'hidden'

    )

$(document).ready ->
    $('.option-item').each (i, el) ->
        new Option(el)