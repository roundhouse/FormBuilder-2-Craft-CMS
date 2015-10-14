var App,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

App = (function() {
  function App() {
    this.newForm = bind(this.newForm, this);
    this.init = bind(this.init, this);
  }

  App.prototype.init = function() {
    var clipboard;
    clipboard = new Clipboard('.copy');
    return clipboard.on('success', function(e) {
      return e.clearSelection();
    });
  };

  App.prototype.newForm = function() {
    var newFormActiveTab;
    if ($('.fb-new-form').length > 0) {
      newFormActiveTab = Cookies.get('newform-active-tab');
      $('.menu-tabs a').click(function(event) {
        var tab;
        event.preventDefault();
        $(this).parent().addClass('current');
        $(this).parent().siblings().removeClass('current');
        tab = $(this).attr('href');
        Cookies.set('newform-active-tab', tab, {
          expires: 7
        });
        $('.tab-content').not(tab).css('display', 'none');
        return $(tab).fadeIn();
      });
      if ($('#customRedirect').is(':checked')) {
        $('.method-redirect .checkbox-toggle').addClass('selected');
        $('.method-redirect .checkbox-extra').show();
      }
      if ($('#ajaxSubmit').is(':checked')) {
        $('.method-ajax .checkbox-toggle').addClass('selected');
      }
      if ($('#spamTimeMethod').is(':checked')) {
        $('.method-time .checkbox-toggle').addClass('selected');
        $('.method-time .checkbox-extra').show();
      }
      if ($('#spamHoneypotMethod').is(':checked')) {
        $('.method-honeypot .checkbox-toggle').addClass('selected');
        $('.method-honeypot .checkbox-extra').show();
      }
      if ($('#notifySubmission').is(':checked')) {
        $('.method-notify .checkbox-toggle').addClass('selected');
        $('.method-notify .checkbox-extra').show();
      }
      return $('.checkbox-toggle').on('click', function() {
        var toggle;
        toggle = $(this).data('checkbox');
        $(this).toggleClass('selected');
        if ($(this).hasClass('selected')) {
          console.log('has class selected');
          $('#' + toggle).prop('checked', true);
          return $(this).next('.checkbox-extra').stop().slideDown();
        } else {
          $('#' + toggle).prop('checked', false);
          return $(this).next('.checkbox-extra').stop().slideUp();
        }
      });
    }
  };

  return App;

})();

$(document).ready(function() {
  var Application;
  Application = new App();
  Application.init();
  return Application.newForm();
});
