var Field, Fields;

if ($ && window.Garnish) {
  Fields = Garnish.Base.extend({
    $container: null,
    $form: null,
    $body: null,
    $tagContainer: null,
    $targetEl: null,
    $target: null,
    init: function(container, form, target) {
      var self, tags, targetClassName;
      self = this;
      this.$container = container;
      this.$form = $(form);
      this.$body = this.$form.find('.body');
      this.$tagContainer = $('<div class="tags-container"></div>');
      this.$body.append(this.$tagContainer);
      tags = [];
      $.each($.parseJSON(this.$container.$fields), function(i, item) {
        return tags[i] = "<div class='tag-btn tag-" + item.value + "' data-tag='{" + item.value + "}'>" + item.label + "</div>";
      });
      tags.push("<div class='tag-btn tag-date' data-tag='{date}'>Date</div>");
      tags.splice(0, 1);
      console.log(tags);
      this.$tagContainer.html(tags);
      $.each(this.$container.$inputs, function(i, item) {
        if (item.tags) {
          return self.$targetEl = item;
        }
      });
      targetClassName = this.$targetEl.name.replace(/[_\W]+/g, "-").slice(0, -1);
      this.$target = $("." + targetClassName);
      return $.each(this.$tagContainer.find('.tag-btn'), function(i, item) {
        return new Field(item, self.$target);
      });
    }
  });
  Field = Garnish.Base.extend({
    $tag: null,
    $target: null,
    init: function(tag, target) {
      this.$tag = $(tag);
      this.$target = target;
      return this.addListener(this.$tag, 'click', 'addTag');
    },
    addTag: function() {
      var tag;
      tag = this.$tag.data('tag');
      return this.$target.val(this.$target.val() + tag);
    }
  });
}
