(function($) {

    Craft.FormBuilder2Branding = Garnish.Base.extend(
        {
            displayFooter: function (plugin) {
                brandHtml = '<ul>';
                brandHtml += '<li><a href="' + plugin.pluginUrl + '" target="_blank">' + plugin.pluginName + '</a> ' + plugin.pluginVersion + '</li>';
                brandHtml += '<li>' + plugin.pluginDescription + '</li>';
                brandHtml += '<li> Designed by <a href="' + plugin.developerUrl + '" target="_blank">' + plugin.developerName + '</a></li>';
                brandHtml += '</ul>';

                $('#footer').append(brandHtml)
            }
        });

})(jQuery);