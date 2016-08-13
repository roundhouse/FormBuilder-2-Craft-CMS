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
  key: null
  $body: null
  modal: null
  init: (container) ->
    @$container = $(container)
    @key = @$container.attr('data-key')
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
    console.log layout
    body = @modal.$bodyInput.val().replace(/\n/g, '<br>')
    @$body.html body
    @$body.append '<p>'+layout.fileContents+'</p>'
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
      key: @message.key
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
    console.log data
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
          Craft.cp.displayNotice Craft.t('Markup saved.')
        else
          Craft.cp.displayError()
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
