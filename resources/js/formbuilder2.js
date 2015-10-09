var App,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

App = (function() {
  function App() {
    this.init = bind(this.init, this);
  }

  App.prototype.init = function() {
    var clipboard, newFormActiveTab;
    clipboard = new Clipboard('.copy');
    clipboard.on('success', function(e) {
      return e.clearSelection();
    });
    if ($('.fb-new-form').length > 0) {
      newFormActiveTab = Cookies.get('newform-active-tab');
      return $('.menu-tabs a').click(function(event) {
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
    }
  };

  return App;

})();

$(document).ready(function() {
  var Application;
  Application = new App();
  return Application.init();
});
