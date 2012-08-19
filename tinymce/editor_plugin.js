// Docu : http://www.tinymce.com/wiki.php/API3:tinymce.api.3.x

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('simple_wp_footnotes');
	
	tinymce.create('tinymce.plugins.simple_wp_footnotes', {

		init : function(ed, url) {

			// Register the command so that it can be invoked from the button
			ed.addCommand('mce_simple_wp_footnotes', function() {
				simple_wp_footnotes_canvas = ed;
				simple_wp_footnotes_caller = 'visual';
				jQuery( "#simple-wp-footnotes" ).dialog( "open" );
			});

			// Register example button
			ed.addButton('simple_wp_footnotes', {
				title : 'simple_wp_footnotes.desc',
				cmd : 'mce_simple_wp_footnotes',
				image : url + '/simple-wp-footnotes.png'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 */
		getInfo : function() {
			return {
					longname  : 'Simple WP Footnotes',
					author 	  : 'Finding Simple',
					authorurl : 'http://findingsimple.com/',
					infourl   : 'http://findingsimple.com/',
					version   : '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('simple_wp_footnotes', tinymce.plugins.simple_wp_footnotes);
	
})();


