Craft.FileUploadsIndex = Garnish.Base.extend({
  $container: $('.upload-details'),
  elementIndex: null,
  init: function(elementIndex, container, settings) {
    var $elements;
    this.elementIndex = elementIndex;
    this.$container = $(container);
    this.setSettings(settings, Craft.BaseElementIndexView.defaults);
    this.$loadingMoreSpinner = $('<div class="centeralign hidden">' + '<div class="spinner loadingmore"></div>' + '</div>').insertAfter(this.$container);
    this.$elementContainer = this.getElementContainer();
    $elements = this.$elementContainer.children();
    if (this.settings.context === 'index') {
      return this.addListener(this.$elementContainer, 'dblclick', function(ev) {
        var $element;
        var $element, $target;
        $target = $(ev.target);
        if ($target.hasClass('element')) {
          $element = $target;
        } else {
          $element = $target.closest('.element');
        }
        if ($element.length) {
          return this.createElementEditor($element);
        }
      });
    }
  },
  getElementContainer: function() {
    this.$table = this.$container.find('table:first');
    return this.$table.children('tbody:first');
  },
  createElementEditor: function($element) {
    return new Craft.ElementEditor($element, {
      onSaveElement: $.proxy((function(response) {
        return Craft.cp.displayNotice(Craft.t('Asset updated'));
      }), this)
    });
  }
});

Garnish.$doc.ready(function() {
  return $('.submission-action-trigger').on('click', function(e) {
    var $menu, entryId, fileIds, formId;
    e.preventDefault();
    formId = $(this).data('form-id');
    entryId = $(this).data('entry-id');
    fileIds = $(this).data('file-ids');
    $menu = $('<div class="tout-dropdown"/>').html('<ul class="form-item-menu">' + '</ul>');
    $('<li><a href="' + window.FormBuilder.adminUrl + '/entries" class="download-all-files">Download All</a></li>').appendTo($menu.find('ul'));
    new Garnish.HUD($(this), $menu, {
      hudClass: 'hud fb-hud submissionhud',
      closeOtherHUDs: false
    });
    return $menu.find('.download-all-files').on('click', function(e) {
      var data;
      e.preventDefault();
      Craft.cp.displayNotice(Craft.t('Downloading...'));
      data = {
        ids: fileIds,
        formId: formId
      };
      return Craft.postActionRequest('formBuilder2/entry/downloadAllFiles', data, $.proxy((function(response, textStatus) {
        var hudID, results;
        if (response.success) {
          window.location = '/actions/formBuilder2/entry/downloadFiles?filePath=' + response.filePath;
          Craft.cp.displayNotice(Craft.t('Download Successful'));
        } else {
          Craft.cp.displayError(Craft.t(response.message));
        }
        results = [];
        for (hudID in Garnish.HUD.activeHUDs) {
          results.push(Garnish.HUD.activeHUDs[hudID].hide());
        }
        return results;
      }), this));
    });
  });
});
