var FormBuilderSection;

if ($ && window.Garnish) {
  $('.form-actions').each(function(index, value) {
    var $menu, formHandle, formId, formName;
    formId = $(value).data('form-id');
    formHandle = $(value).data('form-handle');
    formName = $(value).data('form-name');
    $menu = $('<div class="form"/>').html('<ul class="form-item-menu">' + '<li>' + '<a href="#" class="copy-handle" data-clipboard-text="' + formHandle + '">' + 'Copy Handle' + '</a>' + '</li>' + '<li>' + '<a href="#" class="delete">' + 'Delete</a>' + '</li>' + '</ul>');
    $(value).on('click', function(e) {
      var formbuilderForms;
      e.preventDefault();
      return formbuilderForms = new Garnish.HUD($(value).find('.form-action-trigger'), $menu, {
        hudClass: 'hud fb-hud formhud',
        closeOtherHUDs: false
      });
    });
    $menu.find('.copy-handle').on('click', function(e) {
      var hudID;
      e.preventDefault();
      new Clipboard('.copy-handle', {
        text: function(trigger) {
          return formHandle;
        }
      });
      for (hudID in Garnish.HUD.activeHUDs) {
        Garnish.HUD.activeHUDs[hudID].hide();
      }
      return Craft.cp.displayNotice(Craft.t('Form handle copied'));
    });
    return $menu.find('.delete').on('click', function(e) {
      var data;
      e.preventDefault();
      data = {
        id: formId
      };
      if (confirm(Craft.t("Are you sure you want to delete " + formName + " and all its entries?"))) {
        return Craft.postActionRequest('formBuilder2/form/deleteForm', data, $.proxy((function(response, textStatus) {
          var $row, hudID, results;
          if (textStatus === 'success') {
            $row = $('#formbuilder-form-' + formId);
            formListTable.sorter.removeItems($row);
            $row.remove();
            results = [];
            for (hudID in Garnish.HUD.activeHUDs) {
              Garnish.HUD.activeHUDs[hudID].hide();
              results.push(Craft.cp.displayNotice(Craft.t('Form deleted')));
            }
            return results;
          }
        }), this));
      }
    });
  });
  FormBuilderSection = Garnish.Base.extend({
    $container: null,
    $titlebar: null,
    $fieldsContainer: null,
    $previewContainer: null,
    $actionMenu: null,
    $collapserBtn: null,
    $menuBtn: null,
    $status: null,
    collapsed: false,
    init: function(el) {
      var menuBtn;
      this.$container = $(el);
      this.$menuBtn = this.$container.find('.actions > .settings');
      this.$collapserBtn = this.$container.find('.actions > .collapser');
      this.$titlebar = this.$container.find('.titlebar');
      this.$fieldsContainer = this.$container.find('.fields');
      this.$previewContainer = this.$container.find('.preview');
      this.$status = this.$container.find('.actions > .status');
      menuBtn = new Garnish.MenuBtn(this.$menuBtn);
      this.$actionMenu = menuBtn.menu.$container;
      menuBtn.menu.settings.onOptionSelect = $.proxy(this, 'onMenuOptionSelect');
      this._handleTitleBarClick = function(ev) {
        ev.preventDefault();
        return this.toggle();
      };
      this.addListener(this.$collapserBtn, 'click', this.toggle);
      return this.addListener(this.$titlebar, 'doubletap', this._handleTitleBarClick);
    },
    toggle: function() {
      if (this.collapsed) {
        return this.expand();
      } else {
        return this.collapse(true);
      }
    },
    collapse: function(animate) {
      var $customTemplates, $fields, previewHtml, title;
      if (this.collapsed) {
        return;
      }
      this.$container.addClass('collapsed');
      previewHtml = '';
      title = this.$titlebar.find('.tout-title').text();
      if (title === 'Fields') {
        $fields = this.$fieldsContainer.find('.fld-field:not(.unused)').length;
        $customTemplates = this.$fieldsContainer.find('.custom-template:not(.unused)').length;
        if ($fields > 0) {
          previewHtml += "| " + $fields + " Total Fields";
        }
        if ($customTemplates > 0) {
          previewHtml += " | " + $customTemplates + " Custom Templates";
        }
      }
      this.$previewContainer.html(previewHtml);
      this.$fieldsContainer.velocity('stop');
      this.$container.velocity('stop');
      if (animate) {
        this.$fieldsContainer.velocity('fadeOut', {
          duration: 'fast'
        });
        this.$container.velocity({
          height: 50
        }, 'fast');
      } else {
        this.$previewContainer.show();
        this.$fieldsContainer.hide();
        this.$container.css({
          height: 50
        });
      }
      setTimeout($.proxy((function() {
        this.$actionMenu.find('a[data-action=collapse]:first').parent().addClass('hidden');
        return this.$actionMenu.find('a[data-action=expand]:first').parent().removeClass('hidden');
      }), this), 200);
      return this.collapsed = true;
    },
    expand: function() {
      var collapsedContainerHeight, expandedContainerHeight;
      if (!this.collapsed) {
        return;
      }
      this.$container.removeClass('collapsed');
      this.$fieldsContainer.velocity('stop');
      this.$container.velocity('stop');
      collapsedContainerHeight = this.$container.height();
      this.$container.height('auto');
      this.$fieldsContainer.show();
      expandedContainerHeight = this.$container.height();
      this.$container.height(collapsedContainerHeight);
      this.$fieldsContainer.hide().velocity('fadeIn', {
        duration: 'fast'
      });
      this.$container.velocity({
        height: expandedContainerHeight
      }, 'fast', $.proxy((function() {
        return this.$container.height('auto');
      }), this));
      setTimeout($.proxy((function() {
        this.$actionMenu.find('a[data-action=collapse]:first').parent().removeClass('hidden');
        return this.$actionMenu.find('a[data-action=expand]:first').parent().addClass('hidden');
      }), this), 200);
      return this.collapsed = false;
    },
    disable: function() {
      this.$fieldsContainer.find('.enable-notification-section').val('').prop('checked', false);
      this.$status.removeClass('on');
      this.$status.addClass('off');
      setTimeout($.proxy((function() {
        this.$actionMenu.find('a[data-action=disable]:first').parent().addClass('hidden');
        return this.$actionMenu.find('a[data-action=enable]:first').parent().removeClass('hidden');
      }), this), 200);
      return this.collapse(true);
    },
    enable: function() {
      this.$fieldsContainer.find('.enable-notification-section').val('1').prop('checked', true);
      this.$status.removeClass('off');
      this.$status.addClass('on');
      return setTimeout($.proxy((function() {
        this.$actionMenu.find('a[data-action=disable]:first').parent().removeClass('hidden');
        return this.$actionMenu.find('a[data-action=enable]:first').parent().addClass('hidden');
      }), this), 200);
    },
    onMenuOptionSelect: function(option) {
      var $option;
      $option = $(option);
      switch ($option.data('action')) {
        case 'collapse':
          return this.collapse(true);
        case 'expand':
          return this.expand();
        case 'disable':
          return this.disable();
        case 'enable':
          this.enable();
          return this.expand();
      }
    }
  });
}
