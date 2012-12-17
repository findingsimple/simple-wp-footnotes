jQuery(document).ready(function($){

	try {
		var target = window.location.hash;
		if (target.substr(0,10) == '#footnote-') {
			var pieces = target.split('-');
			if (pieces.length == 3) {
				var pid = pieces[1];
				footnote_show(pid);
			}
		} else {
			if(jQuery('.footnote-toggle').hasClass('footnotes-hidden')) {
				jQuery(".simple-footnotes ol").hide();
				jQuery('.footnote-show').html('Show');
			}
		}
	} catch (ex) {
		/* Nothing... */
	}
	
	jQuery(".footnote-toggle").bind("click", function(event){
		var pid = jQuery(this).data("id");
		jQuery('#footnotes-'+pid+' ol').toggle();
		footnote_updatelabel(pid);
		return false;	
	});
	
	jQuery(".simple-wp-footnote").bind("click", function(event){
		var pid = jQuery(this).data("id");
		footnote_show(pid);
	});
	
});

/* Footnotes Functions */

function footnote_show(pid) {
	jQuery('#footnotes-'+pid+' ol').show();
	footnote_updatelabel(pid);
}

function footnote_updatelabel(pid) {
	if (jQuery('#footnotes-'+pid+' ol').is(':visible')) {
		jQuery('#footnotes-'+pid+' .footnote-show').html('Hide');
		jQuery('#footnotes-'+pid+' .footnote-toggle').removeClass('footnotes-hidden');
	} else {
		jQuery('#footnotes-'+pid+' .footnote-show').html('Show');
		jQuery('#footnotes-'+pid+' .footnote-toggle').addClass('footnotes-hidden');
	}
}
