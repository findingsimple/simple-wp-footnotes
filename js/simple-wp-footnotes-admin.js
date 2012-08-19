jQuery(document).ready(function($){

	$(function() {
		$( '#simple-wp-footnotes' ).dialog({
			autoOpen: false,
			modal: true,
			dialogClass: 'wp-dialog',
			buttons: {
				Cancel: function() {
				
					$( this ).dialog('close');
					
				},
				Insert: function() {
				
					$(this).dialog('close');

					var FootnoteToInsert = '[footnote]';

					$.each( FootnoteFields , function(id,label){
					
						if ( $('#simple-'+id).val().length != 0 ){
						
							FootnoteToInsert += $('#simple-'+id).val();
														
						}
						
					});

					FootnoteToInsert += '[/footnote]';

					// HTML editor
					if (simple_wp_footnotes_caller == 'html') {
					
						QTags.insertContent( FootnoteToInsert );
						
					} else { // Visual Editor
					
						simple_wp_footnotes_canvas.execCommand( 'mceInsertContent', false, FootnoteToInsert );
						
					}
				}
			},
			width: 400,
		});
	});
	
	QTags.addButton('simple_wp_footnotes_id','footnote',function(){
	
		simple_wp_footnotes_caller = 'html';
		
		jQuery('#simple-wp-footnotes').dialog('open');
		
	});	
	
});

// Global variables to keep track on the canvas instance and from what editor
// that opened the Simple WP Footnotes popup.
var simple_wp_footnotes_canvas;
var simple_wp_footnotes_caller = '';
