var ContentCopy, ContentCopyModal, changeFont, templateContent;

changeFont = function(target, size) {
  return target.css('font-size', size + 'px');
};

$(function() {
  var bodyFontRange, footerFontRange;
  $('#templateBodyContainerWidth').on('change keyup', function(e) {
    $('#cc-wrapper').css('width', $(this).val() + 'px');
    return $('.size-info').html($(this).val() + 'px');
  });
  $('#templateBodyBackgroundColor').on('change', function(e) {
    return $('#cc-body').css('backgroundColor', $(this).val());
  });
  $('#templateBackgroundColor').on('change', function(e) {
    return $('#cc-wrapper').css('backgroundColor', $(this).val());
  });
  bodyFontRange = document.getElementById('templateBodyTextSize');
  footerFontRange = document.getElementById('templateFooterTextSize');
  Array.prototype.slice.call(document.querySelectorAll('.text-size'), 0).forEach(function(bt) {
    return bt.addEventListener('click', function(e) {
      var action, target, text;
      text = $(this.closest('.text-content')).find('.body');
      target = $(this).data('target');
      action = $(this).data('action');
      switch (action) {
        case 'increase':
          document.getElementById(target).stepUp(1);
          break;
        case 'decrease':
          document.getElementById(target).stepDown(1);
      }
      return changeFont(text, document.getElementById(target).value);
    });
  });
  bodyFontRange.addEventListener('change', (function() {
    var text;
    text = $(this.closest('.text-content')).find('.body');
    return changeFont(text, $(this).val());
  }), false);
  footerFontRange.addEventListener('change', (function() {
    var text;
    text = $(this.closest('.text-content')).find('.body');
    return changeFont(text, $(this).val());
  }), false);
  $('#templateBodyTextColor').on('change', function(e) {
    var text;
    text = $(this.closest('.text-content')).find('.body');
    return text.css('color', $(this).val());
  });
  $('#templateFooterTextColor').on('change', function(e) {
    var text;
    text = $(this.closest('.text-content')).find('.body');
    return text.css('color', $(this).val());
  });
  return $('.delete-text').on('click', function(e) {
    var placeholder, target;
    e.preventDefault();
    target = $(this).data('target');
    placeholder = $(this).parent().find('.body').data('placeholder');
    $('.' + target).val('');
    $(this).parent().find('.body').addClass('txt').html(placeholder);
    $(this).addClass('hidden');
    return $(this).parent().find('.text-actions').addClass('hidden');
  });
});

templateContent = Garnish.Base.extend({
  copy: null,
  init: function() {
    var $container, $copy, i, message, results;
    this.copy = [];
    $container = $('#cc-wrapper');
    $copy = $container.find('.text-content');
    i = 0;
    results = [];
    while (i < $copy.length) {
      message = new ContentCopy($copy[i]);
      this.copy.push(message);
      results.push(i++);
    }
    return results;
  }
});

ContentCopy = Garnish.Base.extend({
  $container: null,
  templateId: null,
  copyType: null,
  copyText: null,
  $body: null,
  modal: null,
  init: function(textContainer) {
    this.$container = $(textContainer);
    this.templateId = this.$container.attr('data-template-id');
    this.copyType = this.$container.attr('data-type');
    this.copyText = this.$container.attr('data-copy');
    this.$body = this.$container.find('.body:first');
    return this.addListener(this.$body, 'click', 'edit');
  },
  edit: function() {
    if (!this.modal) {
      return this.modal = new ContentCopyModal(this);
    } else {
      return this.modal.show();
    }
  },
  updateHtmlFromModal: function(data) {
    this.$body.parent().addClass('text-set');
    this.$body.parent().find('.text-actions').removeClass('hidden');
    this.$body.parent().find('.delete').removeClass('hidden');
    this.$body.removeClass('txt');
    return this.$body.html(data.copy);
  }
});

ContentCopyModal = Garnish.Modal.extend({
  copy: null,
  $copyInput: null,
  $saveBtn: null,
  $cancelBtn: null,
  $spinner: null,
  loading: false,
  init: function(copy) {
    this.copy = copy;
    this.base(null, {
      resizable: true
    });
    return this.loadContainer();
  },
  loadContainer: function() {
    var data;
    data = {
      templateId: this.copy.templateId,
      copyType: this.copy.copyType,
      copy: this.copy.copyText
    };
    if (typeof Craft.csrfTokenName !== 'undefined' && typeof Craft.csrfTokenValue !== 'undefined') {
      data[Craft.csrfTokenName] = Craft.csrfTokenValue;
    }
    return $.post(Craft.getUrl('formbuilder2/templates/partials/_modal'), data, $.proxy((function(response, textStatus, jqXHR) {
      var $container;
      if (textStatus === 'success') {
        if (!this.$container) {
          $container = $('<div class="modal fitted">' + '<form accept-charset="UTF-8">' + '    <div class="body">' + '        <div class="content">' + '            <div class="main">' + response + '</div>' + '        </div>' + '    </div>' + '    <div class="footer">' + '        <div class="buttons right">' + '            <input type="button" class="btn cancel" value="Cancel">' + '            <input type="submit" class="btn submit" value="Set Copy">' + '        </div>' + '    </div>' + '</form>', '</div>').appendTo(Garnish.$bod);
          this.setContainer($container);
          this.show();
        } else {
          this.$container.html(response);
        }
        this.$copyInput = this.$container.find('.' + data.copyType + ':first');
        this.$saveBtn = this.$container.find('.submit:first');
        this.$cancelBtn = this.$container.find('.cancel:first');
        this.$spinner = this.$container.find('.spinner:first');
        this.addListener(this.$container, 'submit', 'setTemplate');
        return this.addListener(this.$cancelBtn, 'click', 'cancel');
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
      copy: this.$copyInput.val().replace(/\n/g, '<br>')
    };
    this.$copyInput.removeClass('error');
    if (!data.copy) {
      this.$copyInput.addClass('error');
      Garnish.shake(this.$container);
      return;
    }
    $('#field-' + this.copy.copyType).val(data.copy);
    this.copy.updateHtmlFromModal(data);
    this.hide();
    return Craft.cp.displayNotice(Craft.t('Copy set'));
  },
  cancel: function() {
    this.hide();
    if (this.copy) {
      return this.copy.modal = null;
    }
  }
});

new templateContent;
