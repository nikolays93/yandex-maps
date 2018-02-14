/* global tinymce */
(function() {
    tinymce.PluginManager.add('cd_ya_maps', function( editor, url ) {
        editor.addButton( 'cd_ya_maps', {
            // type: 'menubutton',
            text: '{Ya Maps}',
            onclick: function() {
                wp.mce.cd_ya_maps.popupwindow(editor);
            }
        });
    });
})();
