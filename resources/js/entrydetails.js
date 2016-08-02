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
    return new Craft.ElementEditor($element);
  }
});
