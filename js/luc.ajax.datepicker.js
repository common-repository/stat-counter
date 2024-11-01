jQuery(document).ready(function($) {
	// Post & Page datepicker change
	jQuery("#postspagesdate").change(function() {
		jQuery("#postspagesdateLoader").show();

		// POST the data and append the results to the postspages div
		jQuery.post(ajaxurl, jQuery("#postspagesdateForm").serialize(), function(response) {
			jQuery("#postspagesdateLoader").fadeOut();
			jQuery("#postspages").html(response);
			});
	})
	
});