jQuery(document).ready(function($) {
	function paginationClickCallback(page_index, jq) {
		// Get number of elements per pagination page from form
		var items_per_page = jQuery('#items_per_page').val();
		var total_items = jQuery('#total_items').val();
		var max_elem = Math.min((page_index+1) * items_per_page, total_items);
		var element_prefix = "#" + jQuery('#element_prefix').val();
		
		// Show the spinner animation
		jQuery(element_prefix + "Loader").show();
	
		// POST the data and append the results to the div
		var data = {'page_index' : page_index};
		data = jQuery(element_prefix + 'Form').serialize() + '&' + $.param(data);

		jQuery.post(ajaxurl, data, function(response) {
			jQuery(element_prefix + "Loader").fadeOut();
			jQuery(element_prefix).html(response);
			});

		// Prevent click event propagation
		return false;
	}
	
	function getPaginationOptionsFromForm(){
		var opt = {callback: paginationClickCallback};
		// Collect options from the text fields - the fields are named like their option counterparts
		jQuery("input:hidden").each(function(){
			opt[this.name] = this.className.match(/numeric/) ? parseInt(this.value) : this.value;
		});
		
		// Avoid HTML injections
		var htmlspecialchars ={ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;"}
		jQuery.each(htmlspecialchars, function(k,v){
			opt.prev_text = opt.prev_text.replace(k,v);
			opt.next_text = opt.next_text.replace(k,v);
		})
		return opt;
    }

	// Create pagination element with options from form
	if (jQuery("#paginationoptions").length){
		var optInit = getPaginationOptionsFromForm();
		jQuery("#pagination").pagination(optInit.total_items, optInit);
	};	

});