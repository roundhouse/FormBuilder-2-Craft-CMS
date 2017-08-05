if $ and window.Garnish
    $('.form-actions').each (index, value) ->
        formId = $(value).data 'form-id'
        formHandle = $(value).data 'form-handle'
        formName = $(value).data 'form-name'
        $menu = $('<div class="form"/>').html(
            '<ul class="form-item-menu">' +
                '<li>' +
                    '<a href="#" class="copy-handle" data-clipboard-text="'+formHandle+'">' +
                        # '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 32 32"><path d="M18 0H2C.896 0 0 .896 0 2v20c0 1.104.896 2 2 2h16c1.104 0 2-.896 2-2V2c0-1.104-.896-2-2-2z" fill="#CCC"/><path d="M17 2H3c-.553 0-1 .448-1 1.001v18c0 .552.447.999 1 .999h14c.552 0 1-.447 1-.999v-18C18 2.448 17.552 2 17 2z" fill="#FFF"/><path d="M11 10H7c-.553 0-1 .448-1 1 0 .553.447 1 1 1h4c.552 0 1-.447 1-1 0-.552-.448-1-1-1zm2-4H7c-.553 0-1 .448-1 1 0 .553.447 1 1 1h6c.552 0 1-.447 1-1 0-.552-.448-1-1-1z" fill="#CCC"/><path d="M30 8H14c-1.104 0-2 .896-2 2v20.001c0 1.103.896 1.999 2 1.999h11l7-7V10c0-1.104-.896-2-2-2z" fill="#88C057"/><path d="M29 10H15c-.553 0-1 .448-1 1v18c0 .553.447 1.001 1 1.001h9L30 24V11c0-.552-.448-1-1-1z" fill="#FFF"/><path d="M25 14h-6c-.553 0-1 .447-1 1s.447 1 1 1h6c.552 0 1-.447 1-1s-.448-1-1-1zm-2 4h-4c-.553 0-1 .448-1 1 0 .553.447 1 1 1h4c.552 0 1-.447 1-1 0-.552-.448-1-1-1z" fill="#CCC"/><path d="M26 24c-1.104 0-2 .896-2 2v6l8-8h-6z" fill="#638C3F"/></svg>' +
                        'Copy Handle' +
                    '</a>' +
                '</li>' +
                '<li>' +
                    '<a href="#" class="delete">' +
                    # '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 28 32"><path d="M2 8v22c0 1.104.896 2 2 2h20c1.104 0 2-.896 2-2V8H2z" fill="#CCC"/><path d="M8 12c-.553 0-1 .447-1 1v14c0 .552.447 1 1 1 .552 0 1-.448 1-1V13c0-.553-.448-1-1-1zm6 0c-.553 0-1 .447-1 1v14c0 .552.447 1 1 1 .552 0 1-.448 1-1V13c0-.553-.448-1-1-1zm6 0c-.553 0-1 .447-1 1v14c0 .552.447 1 1 1 .552 0 1-.448 1-1V13c0-.553-.448-1-1-1z" fill-rule="evenodd" clip-rule="evenodd" fill="#999"/><path fill="#999" d="M2 9h24v2H2z"/><path d="M26 4H2C.896 4 0 4.896 0 6v3h28V6c0-1.104-.896-2-2-2zm-8-4h-8C8.896 0 8 .896 8 2v2h2V3c0-.552.447-1 1-1h6c.552 0 1 .448 1 1v1h2V2c0-1.104-.896-2-2-2z" fill="#CCC"/></svg>' +
                    'Delete</a>' +
                '</li>' +
            '</ul>')

        $(value).on 'click', (e) ->
            e.preventDefault()
            formbuilderForms = new (Garnish.HUD)($(value).find('.form-action-trigger'), $menu,
                hudClass: 'hud fb-hud formhud'
                closeOtherHUDs: false)

        $menu.find('.copy-handle').on 'click', (e) ->
            e.preventDefault()
            new Clipboard('.copy-handle', text: (trigger) ->
                formHandle
            )
            for hudID of Garnish.HUD.activeHUDs
                Garnish.HUD.activeHUDs[hudID].hide()

            Craft.cp.displayNotice Craft.t('Form handle copied')

        $menu.find('.delete').on 'click', (e) ->
            e.preventDefault()
            console.log formId
            data = id: formId
            if confirm Craft.t("Are you sure you want to delete #{formName} and all its entries?")
                Craft.postActionRequest 'formBuilder2/form/deleteForm', data, $.proxy(((response, textStatus) ->
                    console.log 'Response: ', response
                    console.log 'Text Status: ', textStatus
                    if textStatus == 'success'
                        $row = $('#formbuilder-form-'+formId)
                        formListTable.sorter.removeItems($row)
                        $row.remove()
                        for hudID of Garnish.HUD.activeHUDs
                            Garnish.HUD.activeHUDs[hudID].hide()
                            Craft.cp.displayNotice Craft.t('Form deleted')

                ), this)

enableHoneypotProtection = (el) ->
    el = $(el)
    if el.hasClass('on')
        $('.spam-protection-honeypot').removeClass 'hidden'
    else
        $('.spam-protection-honeypot').addClass 'hidden'

enableTimedProtection = (el) ->
    el = $(el)
    if el.hasClass('on')
        $('.spam-protection-timed').removeClass 'hidden'
    else
        $('.spam-protection-timed').addClass 'hidden'

enableCustomRedirect = (el) ->
    el = $(el)
    if el.hasClass('on')
        $('.custom-redirect-container').removeClass 'hidden'
    else
        $('.custom-redirect-container').addClass 'hidden'

$(document).ready ->
    $('#spamHoneypotMethod').on 'change', ->
        enableHoneypotProtection(this)

    $('#spamTimeMethod').on 'change', ->
        enableTimedProtection(this)

    $('#customRedirectMethod').on 'change', ->
        enableCustomRedirect(this)