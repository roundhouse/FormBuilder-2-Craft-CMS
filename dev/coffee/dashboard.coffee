if $ and window.Garnish
    $('.tout-action-trigger').on 'click', (e) ->
        e.preventDefault()
        type = $(this).data 'type'

        $menu = $('<div class="tout-dropdown"/>').html(
            '<ul class="form-item-menu">' +
            '</ul>')
        
        if type == 'forms'
            $('<li><a href="/admin/formbuilder2/forms/new">Create New</a></li>').appendTo($menu.find('ul'))
            $('<li><a href="/admin/formbuilder2/forms">View All</a></li>').appendTo($menu.find('ul'))
        else if type == 'entries'
            $('<li><a href="/admin/formbuilder2/entries">View All</a></li>').appendTo($menu.find('ul'))

        new (Garnish.HUD)($(this), $menu,
            hudClass: 'hud fb-hud touthud'
            closeOtherHUDs: false)