jQuery(document).ready(function($) {
	// Overview
	jQuery("#overviewrows").change(function() {
		jQuery("#latesthitsLoader").show();

		// POST the data and append the results to the overview div
		jQuery.post(ajaxurl, jQuery("#overviewForm").serialize(), function(response) {
			jQuery("#overviewLoader").fadeOut();
			jQuery("#overview").html(response);
			});
	})
	
	
	// Latest Hits
	jQuery("#hitsrows").change(function() {
		jQuery("#latesthitsLoader").show();

		// POST the data and append the results to the latesthits div
		jQuery.post(ajaxurl, jQuery("#latesthitsForm").serialize(), function(response) {
			jQuery("#latesthitsLoader").fadeOut();
			jQuery("#latesthits").html(response);
			});
	})
	
	
	// Latest Search Terms
	jQuery("#searchrows").change(function() {
		jQuery("#latestsearchLoader").show();

		// POST the data and append the results to the latestsearch div
		jQuery.post(ajaxurl, jQuery("#latestsearchForm").serialize(), function(response) {
			jQuery("#latestsearchLoader").fadeOut();
			jQuery("#latestsearch").html(response);
			});
	})

	
	// Latest Referrers
	jQuery("#referrersrows").change(function() {
		jQuery("#latestreferrersLoader").show();

		// POST the data and append the results to the latestreferrers div
		jQuery.post(ajaxurl, jQuery("#latestreferrersForm").serialize(), function(response) {
			jQuery("#latestreferrersLoader").fadeOut();
			jQuery("#latestreferrers").html(response);
			});
	})

	
	// Latest Feeds
	jQuery("#feedsrows").change(function() {
		jQuery("#latestfeedsLoader").show();

		// POST the data and append the results to the latestfeeds div
		jQuery.post(ajaxurl, jQuery("#latestfeedsForm").serialize(), function(response) {
			jQuery("#latestfeedsLoader").fadeOut();
			jQuery("#latestfeeds").html(response);
			});
	})

	
	// Latest Spiders
	jQuery("#spidersrows").change(function() {
		jQuery("#latestspidersLoader").show();

		// POST the data and append the results to the latestspiders div
		jQuery.post(ajaxurl, jQuery("#latestspidersForm").serialize(), function(response) {
			jQuery("#latestspidersLoader").fadeOut();
			jQuery("#latestspiders").html(response);
			});
	})

	
	// Latest Spams Bots
	jQuery("#spambotsrows").change(function() {
		jQuery("#latestspambotsLoader").show();

		// POST the data and append the results to the latestspambots div
		jQuery.post(ajaxurl, jQuery("#latestspambotsForm").serialize(), function(response) {
			jQuery("#latestspambotsLoader").fadeOut();
			jQuery("#latestspambots").html(response);
			});
	})
	
	// Latest Undefined Agents
	jQuery("#undefagentsrows").change(function() {
		jQuery("#latestundefagentsLoader").show();

		// POST the data and append the results to the latestundefagents div
		jQuery.post(ajaxurl, jQuery("#latestundefagentsForm").serialize(), function(response) {
			jQuery("#latestundefagentsLoader").fadeOut();
			jQuery("#latestundefagents").html(response);
			});
	})
});