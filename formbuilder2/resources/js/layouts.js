var EmailMessages, Message, MessageSettingsModal;

EmailMessages = Garnish.Base.extend({
  messages: null,
  init: function() {
    var $container, $messages, i, message;
    this.messages = [];
    $container = $('#messages');
    $messages = $container.find('.message');
    i = 0;
    while (i < $messages.length) {
      message = new Message($messages[i]);
      this.messages.push(message);
      i++;
    }
  }
});

Message = Garnish.Base.extend({
  $container: null,
  layoutId: null,
  templateName: null,
  templateOriName: null,
  templatePath: null,
  $body: null,
  modal: null,
  init: function(container) {
    this.$container = $(container);
    this.layoutId = this.$container.attr('data-layout-id');
    this.$body = this.$container.find('.body:first');
    this.addListener(this.$container, 'click', 'edit');
  },
  edit: function() {
    if (!this.modal) {
      this.modal = new MessageSettingsModal(this);
    } else {
      this.modal.show();
    }
  },
  updateHtmlFromModal: function(layout) {
    var body;
    body = 'Template Name: ' + layout.fileOriginalName + '<br />Template Path: ' + layout.filePath;
    this.$body.html(body);
    this.$body.parent().find('#template-name-input').val(this.modal.$bodyInput.val());
    this.$body.parent().find('#template-original-name-input').val(layout.fileOriginalName);
    this.$body.parent().find('#template-path-input').val(layout.filePath);
  }
});

MessageSettingsModal = Garnish.Modal.extend({
  message: null,
  $bodyInput: null,
  $saveBtn: null,
  $cancelBtn: null,
  $spinner: null,
  loading: false,
  init: function(message) {
    this.message = message;
    this.base(null, {
      resizable: true
    });
    this.loadContainer();
  },
  loadContainer: function() {
    var data;
    data = {
      layoutId: this.message.layoutId
    };
    if (typeof Craft.csrfTokenName !== 'undefined' && typeof Craft.csrfTokenValue !== 'undefined') {
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
    }
    $.post(Craft.getUrl('formbuilder2/layouts/_markupModal'), data, $.proxy((function(response, textStatus, jqXHR) {
      var $container;
      if (textStatus === 'success') {
        if (!this.$container) {
          $container = $('<div class="modal fitted">' + '<form accept-charset="UTF-8">' + '    <div class="body">' + '        <div class="content">' + '            <div class="main">' + response + '</div>' + '        </div>' + '    </div>' + '    <div class="footer">' + '        <div class="buttons right">' + '            <input type="button" class="btn cancel" value="Cancel">' + '            <input type="submit" class="btn submit" value="Set Template">' + '        </div>' + '    </div>' + '</form>', '</div>').appendTo(Garnish.$bod);
          this.setContainer($container);
          this.show();
        } else {
          this.$container.html(response);
        }
        this.$bodyInput = this.$container.find('.templatePath:first');
        this.$saveBtn = this.$container.find('.submit:first');
        this.$cancelBtn = this.$container.find('.cancel:first');
        this.$spinner = this.$container.find('.spinner:first');
        this.addListener(this.$container, 'submit', 'setTemplate');
        this.addListener(this.$cancelBtn, 'click', 'cancel');
      }
    }), this));
  },
  setTemplate: function(event) {
    var data;
    event.preventDefault();
    if (this.loading) {
      return;
    }
    data = {
      templatePath: this.$bodyInput.val()
    };
    this.$bodyInput.removeClass('error');
    if (!data.templatePath) {
      if (!data.templatePath) {
        this.$bodyInput.addClass('error');
      }
      Garnish.shake(this.$container);
      return;
    }
    this.loading = true;
    this.$saveBtn.addClass('active');
    this.$spinner.show();
    Craft.postActionRequest('formBuilder2/layout/setTemplate', data, $.proxy((function(response, textStatus) {
      this.$saveBtn.removeClass('active');
      this.$spinner.hide();
      this.loading = false;
      if (textStatus === 'success') {
        if (response.success) {
          this.message.updateHtmlFromModal(response.layout);
          this.hide();
          Craft.cp.displayNotice(Craft.t(response.message));
        } else {
          Craft.cp.displayError(Craft.t(response.error));
        }
      }
    }), this));
  },
  cancel: function() {
    this.hide();
    if (this.message) {
      this.message.modal = null;
    }
  }
});

new EmailMessages;
