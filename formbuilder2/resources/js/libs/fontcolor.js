(function($)
{
	$.Redactor.prototype.fontcolor = function()
	{
		return {
			init: function()
			{
				var colors = [
					'#ffffff', '#000000', '#eeece1', '#1f497d', '#4f81bd', '#c0504d', '#9bbb59', '#8064a2', '#4bacc6', '#f79646', '#ffff00',
					'#f2f2f2', '#7f7f7f', '#ddd9c3', '#c6d9f0', '#dbe5f1', '#f2dcdb', '#ebf1dd', '#e5e0ec', '#dbeef3', '#fdeada', '#fff2ca',
					'#d8d8d8', '#595959', '#c4bd97', '#8db3e2', '#b8cce4', '#e5b9b7', '#d7e3bc', '#ccc1d9', '#b7dde8', '#fbd5b5', '#ffe694',
					'#bfbfbf', '#3f3f3f', '#938953', '#548dd4', '#95b3d7', '#d99694', '#c3d69b', '#b2a2c7', '#b7dde8', '#fac08f', '#f2c314',
					'#a5a5a5', '#262626', '#494429', '#17365d', '#366092', '#953734', '#76923c', '#5f497a', '#92cddc', '#e36c09', '#c09100',
					'#7f7f7f', '#0c0c0c', '#1d1b10', '#0f243e', '#244061', '#632423', '#4f6128', '#3f3151', '#31859b',  '#974806', '#7f6000'
				];


				var $button = this.button.add('fontcolor', 'Text Color');
				this.button.setIcon($button, '<i class="re-icon-fontcolor"></i>');

				var $dropdown = this.button.addDropdown($button);
                $dropdown.attr('rel', 'fontcolor');
				$dropdown.width(242);

				var $selector = $('<div style="overflow: hidden; text-align: center;">');
				var $selectorText = $('<span rel="text" class="re-dropdown-box-selector-font" style="background: #eee; float: left; padding: 8px 0; cursor: pointer; font-size: 12px; width: 50%;">Text</span>');
				var $selectorBack = $('<span rel="back" class="re-dropdown-box-selector-font" style="float: left; padding: 8px 0; cursor: pointer; font-size: 12px; width: 50%;">Highlight</span>');

				$selector.append($selectorText);
				$selector.append($selectorBack);

				$dropdown.append($selector);

				this.fontcolor.buildPicker($dropdown, 'textcolor', colors);
				this.fontcolor.buildPicker($dropdown, 'backcolor', colors);

				$selectorText.on('mousedown', function(e)
				{
    				e.preventDefault();

                    $dropdown.find('.re-dropdown-box-selector-font').css('background', 'none');
    				$dropdown.find('.re-dropdown-box-backcolor').hide();
    				$dropdown.find('.re-dropdown-box-textcolor').show();

    				$(this).css('background', '#eee');
				});

				$selectorBack.on('mousedown', function(e)
				{
    				e.preventDefault();

                    $dropdown.find('.re-dropdown-box-selector-font').css('background', 'none');
    				$dropdown.find('.re-dropdown-box-textcolor').hide();
    				$dropdown.find('.re-dropdown-box-backcolor').show();

    				$(this).css('background', '#eee');
				});

			},
			buildPicker: function($dropdown, name, colors)
			{
    			var $box = $('<div class="re-dropdown-box-' + name + '">');
				var rule = (name == 'backcolor') ? 'background-color' : 'color';
				var len = colors.length;
				var self = this;
				var func = function(e)
				{
					e.preventDefault();
					self.fontcolor.set($(this).data('rule'), $(this).attr('rel'));
				};

				for (var z = 0; z < len; z++)
				{
					var color = colors[z];

					var $swatch = $('<a rel="' + color + '" data-rule="' + rule +'" href="#" style="float: left; box-sizing: border-box; font-size: 0; border: 2px solid #fff; padding: 0; margin: 0; width: 22px; height: 22px;"></a>');
					$swatch.css('background-color', color);
					$swatch.on('mousedown', func);

					$box.append($swatch);
				}

				var $elNone = $('<a href="#" style="display: block; clear: both; padding: 8px 5px; box-sizing: border-box; font-size: 12px; line-height: 1;"></a>').html(this.lang.get('none'));
				$elNone.on('mousedown', $.proxy(function(e)
				{
					e.preventDefault();
					this.fontcolor.remove(rule);

				}, this));

				$box.append($elNone);
				$dropdown.append($box);

				if (name == 'backcolor')
				{
    				$box.hide();
				}
			},
			set: function(rule, type)
			{
				this.inline.format('span', 'style', rule + ': ' + type + ';');
				this.dropdown.hide();
			},
			remove: function(rule)
			{
				this.inline.removeStyleRule(rule);
				this.dropdown.hide();
			}
		};
	};
})(jQuery);