jQuery(document).ready(function(){

	jQuery('.fme_pgisfw_nav_tab').each(function(){

		if (jQuery(this).hasClass('fme_current_tab')) {
			jQuery('#'+jQuery(this).attr('aria-controls')).show();
		} else {
			jQuery('#'+jQuery(this).attr('aria-controls')).hide();
		}

	});

	jQuery('.fme_pgisfw_nav_tab').on('click', function(){

		jQuery('.fme_pgisfw_nav_tab').removeClass('fme_current_tab');
		jQuery(this).addClass('fme_current_tab');
		
		var cur_tab_id = jQuery(this).attr('aria-controls');

		jQuery('.fme_pgisfw_main').hide();
		jQuery('#'+cur_tab_id).show();


	});

});
