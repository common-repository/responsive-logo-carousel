jQuery(document).ready(function($) {

    tinymce.create('rlc.tinymce.plugins.rlc_plugin', {
        init: function(ed, url) {
            ed.addCommand('rlc_insert_shortcode', function() {
                content = '[responsive-logo-carousel category="logo-carousel-name"]';
                tinymce.execCommand('mceInsertContent', false, content);
            });
            ed.addButton('rlc_button', {title: 'Insert shortcode', cmd: 'rlc_insert_shortcode', image: url + '/../images/rl1.png'});
        }
    });
    
    tinymce.PluginManager.add('rlc_button', rlc.tinymce.plugins.rlc_plugin);

});