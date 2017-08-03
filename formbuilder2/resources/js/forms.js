if ($ && window.Garnish) {
  $('.form-actions').each(function(index, value) {
    var $menu, formHandle, formId, formName;
    formId = $(value).data('form-id');
    formHandle = $(value).data('form-handle');
    formName = $(value).data('form-name');
    $menu = $('<div class="form"/>').html('<ul class="form-item-menu">' + '<li>' + '<a href="#" class="copy-handle" data-clipboard-text="' + formHandle + '">' + '<svg width="17" height="15" viewBox="0 0 17 15" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><rect stroke="#696F75" stroke-width="2" x="1" y="5.75" width="9.34" height="8.25" rx="3"/><rect fill="#4CA0FE" x="5.66" width="11.34" height="10.25" rx="3"/></g></svg>' + 'Copy Handle' + '</a>' + '</li>' + '<li>' + '<a href="#" class="delete">' + '<svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">' + '<g fill="none" fill-rule="evenodd">' + '<circle fill="#FF6D4A" cx="7.5" cy="7.5" r="7.5"/>' + '<path d="M5.5 6h4c.828 0 1.5.672 1.5 1.5S10.328 9 9.5 9h-4C4.672 9 4 8.328 4 7.5S4.672 6 5.5 6z" fill="#FAFBFC"/>' + '</g>' + '</svg>' + 'Delete</a>' + '</li>' + '</ul>');
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
      console.log(formId);
      data = {
        id: formId
      };
      if (confirm(Craft.t("Are you sure you want to delete " + formName + " and all its entries?"))) {
        return Craft.postActionRequest('formBuilder2/form/deleteForm', data, $.proxy((function(response, textStatus) {
          var $row, hudID, results;
          console.log('Response: ', response);
          console.log('Text Status: ', textStatus);
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
}
