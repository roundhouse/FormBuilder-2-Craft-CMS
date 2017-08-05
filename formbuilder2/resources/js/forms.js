var enableCustomRedirect, enableHoneypotProtection, enableTimedProtection;

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

enableHoneypotProtection = function(el) {
  el = $(el);
  if (el.hasClass('on')) {
    return $('.spam-protection-honeypot').removeClass('hidden');
  } else {
    return $('.spam-protection-honeypot').addClass('hidden');
  }
};

enableTimedProtection = function(el) {
  el = $(el);
  if (el.hasClass('on')) {
    return $('.spam-protection-timed').removeClass('hidden');
  } else {
    return $('.spam-protection-timed').addClass('hidden');
  }
};

enableCustomRedirect = function(el) {
  el = $(el);
  if (el.hasClass('on')) {
    return $('.custom-redirect-container').removeClass('hidden');
  } else {
    return $('.custom-redirect-container').addClass('hidden');
  }
};

$(document).ready(function() {
  $('#spamHoneypotMethod').on('change', function() {
    return enableHoneypotProtection(this);
  });
  $('#spamTimeMethod').on('change', function() {
    return enableTimedProtection(this);
  });
  return $('#customRedirectMethod').on('change', function() {
    return enableCustomRedirect(this);
  });
});
