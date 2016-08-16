Craft.CustomEmailLogo = Garnish.Base.extend({
  $this: null,
  $parentInput: null,
  $elements: null,
  $data: null,
  name: null,
  modals: null,
  svgCodeIconTpl: null,
  init: function(id) {
    var btnClick, preventDefault, that;
    that = this;
    this.$this = $('#' + id);
    this.$parentInput = this.$this.closest('.input').find('>input[type=\'hidden\']');
    this.$elements = this.$this.find('.elements');
    this.$data = this.$this.find('.svg-code-modal');
    this.name = this.$data.data('name');
    this.modals = [];
    this.leftAlignButton = '<div class="align-trigger left-align active" data-align="left"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 48 48" enable-background="new 0 0 48 48" xml:space="preserve"><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="9" x2="44" y2="9"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="15" x2="30" y2="15"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="21" x2="44" y2="21"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="27" x2="30" y2="27"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="33" x2="44" y2="33"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="39" x2="30" y2="39"/></svg></div>';
    this.centerAlignButton = '<div class="align-trigger center-align" data-align="center"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 48 48" enable-background="new 0 0 48 48" xml:space="preserve"><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="9" x2="44" y2="9"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="12" y1="15" x2="36" y2="15"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="21" x2="44" y2="21"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="12" y1="27" x2="36" y2="27"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="33" x2="44" y2="33"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="12" y1="39" x2="36" y2="39"/></svg></div>';
    this.rightAlignButton = '<div class="align-trigger right-align" data-align="right"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 48 48" enable-background="new 0 0 48 48" xml:space="preserve"><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="9" x2="44" y2="9"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="18" y1="15" x2="44" y2="15"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="21" x2="44" y2="21"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="18" y1="27" x2="44" y2="27"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4" y1="33" x2="44" y2="33"/><line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="18" y1="39" x2="44" y2="39"/></svg></div>';
    this.alignmentControls = '<div class="alignment-controls"></div>';
    this.svgCodeIconTpl = '<a class="svgcode-btn icon" title="SVG Code">SVG Code</a>';
    preventDefault = function(event) {
      return event.stopPropagation();
    };
    btnClick = function(event) {
      var id;
      var align, element;
      event.stopPropagation();
      id = $(this).closest('.element').data('id');
      align = $(this).data('align');
      element = $(this).closest('.element');
      $(element).parent().attr('data-align', align);
      $('#templateLogoAlignment').val(align);
      $('.align-trigger').removeClass('active');
      $(this).addClass('active');
      console.log('Align: ', align);
      return console.log('Element: ', element);
    };
    this.$elements.find('.element').each(function(i, e) {
      var $newEl, alignment, image, thumbContainer;
      $newEl = $(e).addClass('templatelogoelement');
      image = $newEl.data('url');
      alignment = $('#templateLogoAlignment').val();
      thumbContainer = $(e).find('.elementthumb');
      thumbContainer.html('<img src="' + image + '">').removeClass('elementthumb').addClass('logo-asset');
      $(that.alignmentControls).prependTo($(e));
      $(that.leftAlignButton).appendTo('.alignment-controls');
      $(that.centerAlignButton).appendTo('.alignment-controls');
      $(that.rightAlignButton).appendTo('.alignment-controls');
      $('.align-trigger').bind('click', btnClick).bind('mousedown mouseup', preventDefault);
      if (alignment !== '') {
        $('.align-trigger').removeClass('active');
        $($newEl).find('.' + alignment + '-align').addClass('active');
        return $($newEl).parent().attr('data-align', alignment);
      }
    });
    this.$this.data('elementSelect').on('selectElements', function(e) {
      var $newElements;
      $newElements = that.$elements.find('.element').slice(-e.elements.length);
      return $newElements.each(function(i, e) {
        var id;
        var $newEl, image, label, thumbContainer;
        $newEl = $(e).addClass('templatelogoelement');
        id = $newEl.data('id');
        label = $newEl.data('label');
        image = $newEl.data('url');
        thumbContainer = $(e).find('.elementthumb');
        thumbContainer.html('<img src="' + image + '">').removeClass('elementthumb').addClass('logo-asset');
        $(that.alignmentControls).prependTo($(e));
        $(that.leftAlignButton).appendTo('.alignment-controls');
        $(that.centerAlignButton).appendTo('.alignment-controls');
        $(that.rightAlignButton).appendTo('.alignment-controls');
        return $('.align-trigger').bind('click', btnClick).bind('mousedown mouseup', preventDefault);
      });
    });
    return this.$this.data('elementSelect').on('removeElements', function(e) {
      var id;
      id = 0;
      $('#templateLogo').find('.elements').removeAttr('data-align');
      return that.$data.find('>div').each(function() {
        if (e.target.$elements.filter('[data-id="' + $(this).data('id') + '"]').length < 1) {
          id = $(this).data('id');
          return $(this).remove();
        }
      });
    });
  },
  initializeModal: function(id) {
    var $modal, image, myModal, observer, observerConfig, oldDisplay, oldWidth, svgCode, timeout;
    image = this.$elements.find('.element[data-id=\'' + id + '\']').data('url');
    svgCode = '';
    $modal = $('<div class="modal elementselectormodal" data-id="' + id + '">' + '    <div class="body">' + '        <div class="content">' + '            <div class="main">' + '                <div class="field"><div class="input"><textarea class="text nicetext fullwidth put-svg-here" rows="4" cols="50" style="min-height:250px;"></textarea></div></div>' + '                <div class="svg-code"><img src="' + image + '" class="svg-convert"></div>' + '            </div>' + '        </div>' + '    </div>' + '    <div class="footer">' + '        <div class="buttons left secondary-buttons">' + '            <div class="btn load-svg dashed">Reload SVG Code</div>' + '        </div>' + '        <div class="buttons right">' + '            <div class="btn submit">Ok</div>' + '        </div>' + '    </div>' + '</div>');
    myModal = new Garnish.Modal($modal, {
      autoShow: false,
      resizable: false
    });
    oldWidth = $modal.width();
    oldDisplay = 'none';
    timeout = null;
    observer = new MutationObserver(function(mutations) {
      return mutations.forEach(function(mutation) {
        return console.log(mutation);
      });
    });
    observerConfig = {
      attributes: true,
      childList: false,
      characterData: false,
      subtree: false,
      attributeOldValue: false,
      characterDataOldValue: false,
      attributeFilter: ['style']
    };
    observer.observe($modal[0], observerConfig);
    $modal.find('.submit').click(function() {
      return myModal.hide();
    });
    $modal.find('.load-svg').click(function() {
      return $('.svg-convert').shapeSvgConvert({
        cleanUp: ['width', 'height', 'id', 'class', 'xmlns:xlink', 'xml:space', 'version'],
        onComplete: function() {
          return $('.put-svg-here').val($('.svg-code').html());
        }
      });
    });
    return this.modals[id] = myModal;
  },
  destroyModal: function(id) {
    return this.modals[id].destroy();
  }
});
