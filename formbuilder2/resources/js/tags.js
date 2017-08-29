var Tag;

if ($ && window.Garnish) {
  Tag = Garnish.Base.extend({
    $item: null,
    $deleteTag: null,
    init: function(item, modal) {
      this.$item = $(item);
      this.$deleteTag = this.$item.find('.option-result-delete');
      return this.addListener(this.$deleteTag, 'click', 'delete');
    },
    "delete": function(e) {
      var self;
      e.preventDefault();
      self = this;
      this.$item.addClass('zap');
      return setTimeout((function() {
        self.$item.remove();
        return Craft.cp.displayNotice(Craft.t('Item Removed'));
      }), 300);
    }
  });
}
