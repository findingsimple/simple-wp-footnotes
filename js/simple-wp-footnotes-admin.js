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

					var FootnoteToInsert = '[ref';

					$.each(FootnoteFields,function(id,label){
					
						if ( $('#footnote-'+id).val().length != 0 ){
						
							FootnoteToInsert += ' '+id+'="'+$('#footnote-'+id).val()+'"';
							
							$('#footnote-'+id).val('');
							
						}
						
					});

					FootnoteToInsert += ']';

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
