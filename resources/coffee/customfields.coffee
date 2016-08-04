Craft.FormbuilderFields = Garnish.Base.extend({
    ASSET: 'asset'
    ASSET_SOURCE: 'assetSource'
    CATEGORY: 'category'
    CATEGORY_GROUP: 'categoryGroup'
    GLOBAL: 'global'
    GLOBAL_SET: 'globalSet'
    ENTRY: 'entry'
    ENTRY_TYPE: 'entryType'
    SINGLE_SECTION: 'singleSection'
    TAG: 'tag'
    TAG_GROUP: 'tagGroup'
    USER: 'user'
    USER_FIELDS: 'userFields'
    fields: null
    labels: null
    layouts: null

    init: ->
        @fields = {}
        @labels = {}
        @layouts = {}



})