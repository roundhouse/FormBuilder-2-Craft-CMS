(function($)
{
	var CustomField = {
		setup: function() {}
	};

	if($ && window.Garnish && window.Craft)
	{
		CustomField = new (Garnish.Base.extend({

			FORM_BUILDER:   'formBuilder',

			fields:  null,
			labels:  null,

			init: function()
			{
				this.fields  = {};
				this.labels  = {};
			},

			setup: function()
			{
				if(Craft.FieldLayoutDesigner)
				{
					var FLD = Craft.FieldLayoutDesigner;
					var FLD_init = FLD.prototype.init;
					var FLD_field = FLD.prototype.initField;
					var FLD_options = FLD.prototype.onFieldOptionSelect;

					FLD.prototype.init = function()
					{
						FLD_init.apply(this, arguments);
						this.customfield = new window.CustomField.Editor(this);
					};

					FLD.prototype.initField = function($field)
					{
						FLD_field.apply(this, arguments);

						var $editBtn = $field.find('.settings');
						var menuBtn = $editBtn.data('menubtn');
						var menu = menuBtn.menu;
						var $menu = menu.$container;
						var $ul = $menu.children('ul');
						var $customfield = $('<li><a data-action="customfield">' + Craft.t('Custom Template') + '</a></li>').appendTo($ul);

						menu.addOptions($customfield.children('a'));
					};

					FLD.prototype.onFieldOptionSelect = function(option)
					{
						FLD_options.apply(this, arguments);

						var $option = $(option);
						var $field = $option.data('menu').$anchor.parent();
						var action = $option.data('action');

						switch(action)
						{
							case 'customfield':
							{
								this.trigger('customfieldOptionSelected', {
									target:  $option[0],
									$target: $option,
									$field:  $field,
									fld:     this,
									id:      $field.data('id') | 0
								});
								break;
							}
						}
					};
				}
			},

			getFieldInfo: function(id)
			{
				return this.fields[id];
			},

			getLabelId: function(fieldId, fieldLayoutId)
			{
				return this.getLabel(fieldId, fieldLayoutId).id;
			},

			getLabel: function(fieldId, fieldLayoutId)
			{
				for(var id in this.labels) if(this.labels.hasOwnProperty(id))
				{
					var label = this.labels[id];

					if(label.fieldId == fieldId && label.fieldLayoutId == fieldLayoutId)
					{
						return label;
					}
				}

				return false;
			},

			getLabelsOnFieldLayout: function(fieldLayoutId)
			{
				fieldLayoutId = isNaN(fieldLayoutId) ? this.getFieldLayoutId() : fieldLayoutId;

				var labels = {};

				for(var labelId in this.labels) if(this.labels.hasOwnProperty(labelId))
				{
					var label = this.labels[labelId];

					if(label.fieldLayoutId == fieldLayoutId)
					{
						labels[labelId] = label;
					}
				}

				return labels;
			}
		}))();
	}

	window.CustomField = CustomField;

})(window.jQuery);