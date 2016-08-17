EmailMessages = Garnish.Base.extend(
  messages: null
  init: ->
    @messages = []
    $container = $('#messages')
    $messages = $container.find('.message')
    i = 0
    while i < $messages.length
      message = new Message($messages[i])
      @messages.push message
      i++
    return
)
Message = Garnish.Base.extend(
  $container: null
  layoutId: null
  templateName: null
  templateOriName: null
  templatePath: null
  $body: null
  modal: null
  init: (container) ->
    @$container = $(container)
    @layoutId = @$container.attr('data-layout-id')
    @$body = @$container.find('.body:first')
    @addListener @$container, 'click', 'edit'
    return
  edit: ->
    if !@modal
      @modal = new MessageSettingsModal(this)
    else
      @modal.show()
    return
  updateHtmlFromModal: (layout) ->
    # body = @modal.$bodyInput.val().replace(/\n/g, '<br>')
    body =  'Template Name: ' + layout.fileOriginalName + '<br />Template Path: ' + layout.filePath 
    @$body.html body
    @$body.parent().find('#template-name-input').val(@modal.$bodyInput.val())
    @$body.parent().find('#template-original-name-input').val(layout.fileOriginalName)
    @$body.parent().find('#template-path-input').val(layout.filePath)
    return
)
MessageSettingsModal = Garnish.Modal.extend(
  message: null
  $bodyInput: null
  $saveBtn: null
  $cancelBtn: null
  $spinner: null
  loading: false
  init: (message) ->
    @message = message
    @base null, resizable: true
    @loadContainer()
    return
  loadContainer: () ->
    data = 
      layoutId: @message.layoutId
    # If CSRF protection isn't enabled, these won't be defined.
    if typeof Craft.csrfTokenName != 'undefined' and typeof Craft.csrfTokenValue != 'undefined'
      # Add the CSRF token
      data[Craft.csrfTokenName] = Craft.csrfTokenValue
    $.post Craft.getUrl('formbuilder2/layouts/_markupModal'), data, $.proxy(((response, textStatus, jqXHR) ->
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
            '            <input type="submit" class="btn submit" value="Set Template">' +
            '        </div>' +
            '    </div>' +
            '</form>'
            '</div>'
          ).appendTo(Garnish.$bod)
          # $container = $('<form class="modal" accept-charset="UTF-8">' + response + '</form>').appendTo(Garnish.$bod)
          @setContainer $container
          @show()
        else
          @$container.html response
        @$bodyInput = @$container.find('.templatePath:first')
        @$saveBtn = @$container.find('.submit:first')
        @$cancelBtn = @$container.find('.cancel:first')
        @$spinner = @$container.find('.spinner:first')
        @addListener @$container, 'submit', 'setTemplate'
        @addListener @$cancelBtn, 'click', 'cancel'
      return
    ), this)
    return
  setTemplate: (event) ->
    event.preventDefault()
    if @loading
      return
    data = 
      templatePath: @$bodyInput.val()
    @$bodyInput.removeClass 'error'
    if !data.templatePath
      if !data.templatePath
        @$bodyInput.addClass 'error'
      Garnish.shake @$container
      return
    @loading = true
    @$saveBtn.addClass 'active'
    @$spinner.show()
    Craft.postActionRequest 'formBuilder2/layout/setTemplate', data, $.proxy(((response, textStatus) ->
      @$saveBtn.removeClass 'active'
      @$spinner.hide()
      @loading = false
      if textStatus == 'success'
        if response.success
          # Only update the page if we're editing the app target locale
          @message.updateHtmlFromModal(response.layout)
          @hide()
          Craft.cp.displayNotice Craft.t(response.message)
        else
          Craft.cp.displayError Craft.t(response.error)
      return
    ), this)
    return
  cancel: ->
    @hide()
    if @message
      @message.modal = null
    return
)
new EmailMessages
