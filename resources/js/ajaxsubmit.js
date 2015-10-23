var AjaxSubmit,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AjaxSubmit = (function() {
  function AjaxSubmit() {
    this.init = bind(this.init, this);
  }

  AjaxSubmit.prototype.init = function() {
    return $('form').submit(function(event) {
      var fd;
      event.preventDefault();
      fd = new FormData(document.querySelector('form'));
      fd.append('CustomField', 'This is some extra data');
      return console.log(fd);
    });
  };

  return AjaxSubmit;

})();

$(document).ready(function() {
  var Application;
  Application = new AjaxSubmit();
  return Application.init();
});
