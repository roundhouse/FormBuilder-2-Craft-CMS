if (typeof Craft.FormBuilder2 === typeof undefined) {
    Craft.FormBuilder2 = {};
}

Craft.FormBuilder2.EntriesIndex = Craft.BaseElementIndex.extend({
    getViewClass: function(mode) {
        switch (mode) {
            case 'table':
                return Craft.FormBuilder2.EntriesTableView;
            default:
                return this.base(mode);
        }
    },
    getDefaultSort: function() {
        return ['dateCreated', 'desc'];
    }
});

Craft.registerElementIndexClass('FormBuilder2', Craft.FormBuilder2.EntriesIndex);