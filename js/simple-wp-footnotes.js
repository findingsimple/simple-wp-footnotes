jQuery(document).ready(function($){

	try {
		var target = window.location.hash;
		if (target.substr(0,4) == '#fn-') {
			var pieces = target.split('-');
			if (pieces.length == 3) {
				var pid = pieces[1];
				footnote_show(pid);
			}
		}
	} catch (ex) {
		/* Nothing... */
	}
	
});

/* Footnotes Functions */

function footnote_show(pid) {
	jQuery('#footnotes-'+pid+' ol').show();
	footnote_updatelabel(pid);
}

function footnote_togglevisible(pid) {
	jQuery('#footnotes-'+pid+' ol').toggle();
	footnote_updatelabel(pid);
	return false;
}

function footnote_updatelabel(pid) {
	if (jQuery('#footnotes-'+pid+' ol').is(':visible')) {
		jQuery('#footnotes-'+pid+' .footnoteshow').hide();
	} else {
		jQuery('#footnotes-'+pid+' .footnoteshow').show();
	}
}
