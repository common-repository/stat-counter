jQuery(document).ready(function($) {
	// GeoIP database update
	var datacountry = {
        action: 'geoipdbupdate',
        edition: 'country'
    }
	var datacity = {
        action: 'geoipdbupdate',
        edition: 'city'
    }
	jQuery("#dogeoipdbupdate").click(function() {
		jQuery("#geoipupdatedbLoader").show();
		
		// POST the data and append the results to the geoipupdatedbResult div
		jQuery.post(ajaxurl, datacountry, function(response) {
			jQuery("#geoipupdatedbResultCountry").html(response);
			});
		
		// POST the data and append the results to the geoipupdatedbResult div
		jQuery.post(ajaxurl, datacity, function(response) {
			jQuery("#geoipupdatedbResultCity").html(response);
			});
		
		jQuery("#geoipupdatedbLoader").fadeOut();
	})
});