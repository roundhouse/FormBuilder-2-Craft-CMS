var TemplatePicker;

$(function() {
  return $('.template-tabs a').click(function(event) {
    var tab;
    event.preventDefault();
    $(this).parent().addClass('active');
    $(this).parent().siblings().removeClass('active');
    tab = $(this).attr('href');
    Cookies.set('template-active-tab', tab, {
      expires: 7
    });
    $('.tab-content').not(tab).css('display', 'none');
    return $(tab).fadeIn();
  });
});

TemplatePicker = Garnish.Base.extend({
  $element: $('#templateLayout'),
  init: function() {
    var that;
    that = this;
    return this.addListener(this.$element, 'change', function(ev) {
      var params;
      params = {
        templateName: ev.target.value
      };
      return Craft.postActionRequest('formBuilder2/template/getTemplateByName', params, $.proxy((function(response, textStatus) {
        console.log(response);
        $('input[name="templateLayout[fileNameCleaned]"]').val(response.fileNameCleaned);
        $('input[name="templateLayout[fileExtension]"]').val(response.fileExtension);
        $('input[name="templateLayout[filePath]"]').val(response.filePath);
        return $('input[name="templateLayout[fileContents]"]').val(response.fileContents);
      }), that));
    });
  }
});

Craft.EmailTemplates = Garnish.Base.extend({
  $this: null,
  $parentInput: null,
  $container: $('#emailTemplates'),
  $data: null,
  templates: null,
  templateId: null,
  modals: null,
  init: function() {
    var that;
    that = this;
    this.$templates = this.$container.find('.preview-template');
    this.modals = [];
    this.$templates.each(function(i, e) {
      var target;
      target = $(e).data('template');
      return that.initializeModal(target);
    });
    return this.addListener(this.$container.find('.preview-template'), 'click', function(ev) {
      var params, target;
      ev.preventDefault();
      target = $(ev.target).data('template');
      params = {
        templateId: target
      };
      return Craft.postActionRequest('formBuilder2/template/getEmailTemplate', params, $.proxy((function(response, textStatus) {
        console.log(response);
        console.log(that.modals[target].$container.find('.main').html(response));
        return that.modals[target].show();
      }), that));
    });
  },
  initializeModal: function(id) {
    var $modal, myModal;
    $modal = $('<div class="modal elementselectormodal" data-id="' + id + '">' + '    <div class="body">' + '        <div class="content">' + '            <div class="main">HIIIII</div>' + '        </div>' + '    </div>' + '    <div class="footer">' + '        <div class="buttons left secondary-buttons">' + '            <div class="btn load-svg dashed">Reload SVG Code</div>' + '        </div>' + '        <div class="buttons right">' + '            <div class="btn submit">Ok</div>' + '        </div>' + '    </div>' + '</div>');
    myModal = new Garnish.Modal($modal, {
      autoShow: false,
      resizable: false
    });
    $modal.find('.submit').click(function() {
      return myModal.hide();
    });
    return this.modals[id] = myModal;
  }
});
