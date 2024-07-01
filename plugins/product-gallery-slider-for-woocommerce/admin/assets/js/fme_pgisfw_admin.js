jQuery(document).ready(function(e) {
	"use strict";

	// jQuery("a.nav-tab:contains('Gallery Slider Rules')").hide();
	jQuery('#fme_pgisfw_thumbs').keypress(function(event) {
		if(event.keyCode> '54' ){
			alert('Enter number between 0 and 6');
			return false;
		}
		else if( event.keyCode< '48'){

			alert('Enter number between 0 and 6');
			return false;
		}
		else if(jQuery(this).val()>6){
			alert('Enter number between 0 and 6');
			return false;	
		}
		

	});
	jQuery('#delete_all_rules_inbulk').click(function(e){
		e.preventDefault();
		
		var rule_id="fme_pgisfw_delete_all_rules";
		jQuery.ajax({
			url:ajaxurl,
			type:"post",
			data: {action:"fme_pgisfw_delete_rule",
				  rule_id:rule_id,
				  fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
			 },
			 success : function(){
			 	location.reload();
								
			}
		});

	});

	jQuery('.rule_delete').click(function(){
		var curr_rule=jQuery(this);
		var rule_id=jQuery(this).attr("rule_id");
		jQuery.ajax({
			url: ajaxurl,
			type:"post",
			data: {action:"fme_pgisfw_delete_rule",
				rule_id:rule_id,
				fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
			},
			success : function(r){
				curr_rule.parents("table").hide();
				curr_rule.parents("div").prev(".accordion").hide();
			}

		});
		

	})

	jQuery('.fme_arrow_images').on('click', function(){
		jQuery('.fme_arrow_images').removeClass("fme_selected_image");
		jQuery(this).addClass("fme_selected_image");

	});


	jQuery('.fme_lightbox_images').on('click', function(){
		jQuery('.fme_lightbox_images').removeClass("fme_selected_lightbox_image");
		jQuery(this).addClass("fme_selected_lightbox_image");

	});

	/******rule tab page js***********/

	jQuery('#add_new_rule_pgisfw').click( function(e){
		e.preventDefault();
		let href=jQuery(this).attr('href');
		window.location.href = href; 
	})

	
	jQuery('#fme_pgisfw_show_bullets').on('change', function(){

		if(jQuery(this).prop('checked')){

			jQuery('#fme_pgisfw_bullets_shape').prop("disabled", false);
			jQuery('#fme_pgisfw_bullets_color').prop("disabled", false);
			jQuery('#fme_pgisfw_bullets_hover_color').prop("disabled", false);
			jQuery('#fme_pgisfw_bullets_thumbnail').prop("disabled", false);
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", false);
			if(jQuery('#fme_pgisfw_hide_thumbnails').prop('checked')){
				jQuery('#fme_pgisfw_bullets_position').prop("disabled", false);
			}
		} else {
			jQuery('#fme_pgisfw_bullets_shape').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_color').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_hover_color').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_thumbnail').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_position').prop("disabled", true);
		}
	});
jQuery('#fme_pgisfw_show_bullets').change();	

	if(jQuery('#fme_pgisfw_show_bullets').prop('checked')){
		
		jQuery('#fme_pgisfw_bullets_shape').prop("disabled", false);
		jQuery('#fme_pgisfw_bullets_color').prop("disabled", false);
		jQuery('#fme_pgisfw_bullets_hover_color').prop("disabled", false);
		jQuery('#fme_pgisfw_bullets_thumbnail').prop("disabled", false);
		jQuery('#fme_pgisfw_bullets_position').prop("disabled", false);
	} else {
		jQuery('#fme_pgisfw_bullets_shape').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_color').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_hover_color').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_thumbnail').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_position').prop("disabled", true);
	}


	jQuery('#fme_pgisfw_bullets_position').on('change', function(){
		if(jQuery(this).val() == 'bellow_image') {
			jQuery('#fme_pgisfw_bullets_thumbnail').prop('disabled', false);
			jQuery('#fme_pgisfw_bullets_inside_position').prop('disabled', true);
		} else {
			jQuery('#fme_pgisfw_bullets_thumbnail').prop('disabled', true);
			jQuery('#fme_pgisfw_bullets_thumbnail').prop('checked',false);
			jQuery('#fme_pgisfw_bullets_inside_position').prop('disabled',false);
		}
	});

	if(jQuery('#fme_pgisfw_bullets_position').val() == 'bellow_image') {
		jQuery('#fme_pgisfw_bullets_thumbnail').prop('disabled', false);
		jQuery('#fme_pgisfw_bullets_inside_position').prop('disabled', true);

	} else {
		jQuery('#fme_pgisfw_bullets_thumbnail').prop('disabled', true);
		jQuery('#fme_pgisfw_bullets_thumbnail').prop('checked',false);
		jQuery('#fme_pgisfw_bullets_inside_position').prop('disabled', false);

	}


	//haseeb changed
	jQuery("#fme_pgisfw_bullets_shape").on('change', function(){
		
		if (jQuery(this).val()=='counter_bullets' || jQuery(this).val()=='bar_counter_bullets'  ) {
			// jQuery('#fme_pgisfw_bullets_position').val('')
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_inside_position').val("bottom_center");
			jQuery("#fme_pgisfw_counter_bullets_font_color_row").removeAttr("hidden");

		} else {
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", false);
			jQuery("#fme_pgisfw_counter_bullets_font_color_row").attr("hidden",'true');			
		}

		if (jQuery(this).val()=='bottom_bar'){
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_inside_position').val("bottom_center");

		}
		
	})

	//bullet inside position disbaled on load if counter bullets enabled
	if (jQuery("#fme_pgisfw_bullets_shape").val()=='counter_bullets' || jQuery("#fme_pgisfw_bullets_shape").val()=='bar_counter_bullets') {
			// jQuery('#fme_pgisfw_bullets_position').val('')
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", true);
			// jQuery('#fme_pgisfw_bullets_inside_position').val("bottom_center");
			jQuery("#fme_pgisfw_counter_bullets_font_color_row").removeAttr("hidden");
		}
		else {

			jQuery("#fme_pgisfw_counter_bullets_font_color_row").attr("hidden",'true');
		}

		if (jQuery('#fme_pgisfw_bullets_shape').val()=='bottom_bar'){
			jQuery('#fme_pgisfw_bullets_inside_position').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_inside_position').val("bottom_center");

		}
	//
	jQuery('.Fme_choosen').select2();


if(200 < jQuery('#fme_pgisfw_prd_count').val()){

			jQuery('#fme_pgisfw_product').select2({
				ajax: {
					url: ajaxurl,
					dataType: 'json',
					delay: 250,
					data: function (params, page) {
						return {
							action: 'fme_pgisfw_get_products_array',
							term: params.term, 
							page: 10
						};
					},
					processResults: function (response) {
						return {
							results:response
						};
					},
					cache: true
				},
        
				minimumInputLength: 1,
			});
		}else{
			jQuery('#fme_pgisfw_product').select2();
		}


	if(jQuery('#fme_pgisfw_autoPlay').val() == 'false'){
		jQuery('#fme_pgisfw_range_slider').prop("disabled", true);
	}else{
		jQuery('#fme_pgisfw_range_slider').prop("disabled", false);		
	}

	jQuery('#fme_pgisfw_autoPlay').on('change', function(){

		if(jQuery(this).val() == 'false'){
			jQuery('#fme_pgisfw_range_slider').prop("disabled", true);
		}else {
			jQuery('#fme_pgisfw_range_slider').prop("disabled", false);
		}
	});

	if(jQuery('#fme_pgisfw_show_zoom').prop('checked')){
		jQuery('#fme_pgisfw_zoombox_frame_width').prop("disabled", false);
		jQuery('#fme_pgisfw_zoombox_frame_height').prop("disabled", false);
		jQuery('#fme_pgisfw_zoombox_radius').prop("disabled", false);
	} else {
		jQuery('#fme_pgisfw_zoombox_frame_width').prop("disabled", true);
		jQuery('#fme_pgisfw_zoombox_frame_height').prop("disabled", true);
		jQuery('#fme_pgisfw_zoombox_radius').prop("disabled", true);
	}


	jQuery('#fme_pgisfw_show_zoom').on('change', function(){
		if(jQuery(this).prop('checked')){
			jQuery('#fme_pgisfw_zoombox_frame_width').prop("disabled", false);
			jQuery('#fme_pgisfw_zoombox_frame_height').prop("disabled", false);
			jQuery('#fme_pgisfw_zoombox_radius').prop("disabled", false);
		}else {
			jQuery('#fme_pgisfw_zoombox_frame_width').prop("disabled", true);
			jQuery('#fme_pgisfw_zoombox_frame_height').prop("disabled", true);
			jQuery('#fme_pgisfw_zoombox_radius').prop("disabled", true);
		}
	});


	if(jQuery('#fme_pgisfw_hide_thumbnails').prop('checked')){
		jQuery('#fme_pgisfw_thumbs').prop("disabled", true);
		jQuery('#fme_pgisfw_slider_layout').prop("disabled", true);
		jQuery('#fme_pgisfw_border_color').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_position').prop("disabled", false);
	} else {
		jQuery('#fme_pgisfw_thumbs').prop("disabled", false);
		jQuery('#fme_pgisfw_slider_layout').prop("disabled", false);
		jQuery('#fme_pgisfw_border_color').prop("disabled", false);
		jQuery('#fme_pgisfw_bullets_position').prop("disabled", true);
		jQuery('#fme_pgisfw_bullets_position').val("inside_image");
		jQuery('#fme_pgisfw_bullets_position').trigger('change');
	}


	jQuery('#fme_pgisfw_hide_thumbnails').on('change', function(){
		if(jQuery(this).prop('checked')){
			jQuery('#fme_pgisfw_thumbs').prop("disabled", true);
			jQuery('#fme_pgisfw_slider_layout').prop("disabled", true);
			jQuery('#fme_pgisfw_border_color').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_position').prop("disabled", false);
		}else {
			jQuery('#fme_pgisfw_thumbs').prop("disabled", false);
			jQuery('#fme_pgisfw_slider_layout').prop("disabled", false);
			jQuery('#fme_pgisfw_border_color').prop("disabled", false);
			jQuery('#fme_pgisfw_bullets_position').prop("disabled", true);
			jQuery('#fme_pgisfw_bullets_position').val("inside_image");
			jQuery('#fme_pgisfw_bullets_position').trigger('change');
		}
	});




	jQuery('#fme_pgisfw_navigation_icon').on('change', function(){
		if(jQuery(this).val() == 'false'){
			jQuery('#fme_pgisfw_navigation_icon_show_on').prop("disabled", true);
			jQuery('#fme_pgisfw_navigation_icon_shape').prop("disabled", true);
			jQuery('#fme_pgisfw_navigation_background_color').prop("disabled", true);
			jQuery('#fme_pgisfw_navigation_hover_color').prop("disabled", true);
			jQuery('#fme_pgisfw_icon_color').prop("disabled", true);
			jQuery('.fme_arrows_options').css("pointer-events", 'none');
		}else {
			jQuery('#fme_pgisfw_navigation_icon_show_on').prop("disabled", false);
			jQuery('#fme_pgisfw_navigation_icon_shape').prop("disabled", false);
			jQuery('#fme_pgisfw_navigation_background_color').prop("disabled", false);
			jQuery('#fme_pgisfw_navigation_hover_color').prop("disabled", false);
			jQuery('#fme_pgisfw_icon_color').prop("disabled", false);
			jQuery('.fme_arrows_options').css("pointer-events", 'all');
		}
	});


	if(jQuery(this).val() == 'false'){
		jQuery('#fme_pgisfw_navigation_icon_show_on').prop("disabled", true);
		jQuery('#fme_pgisfw_navigation_icon_shape').prop("disabled", true);
		jQuery('#fme_pgisfw_navigation_background_color').prop("disabled", true);
		jQuery('#fme_pgisfw_navigation_hover_color').prop("disabled", true);
		jQuery('#fme_pgisfw_icon_color').prop("disabled", true);
		jQuery('.fme_arrows_options').css("pointer-events", 'none');
	} else {
		jQuery('#fme_pgisfw_navigation_icon_show_on').prop("disabled", false);
		jQuery('#fme_pgisfw_navigation_icon_shape').prop("disabled", false);
		jQuery('#fme_pgisfw_navigation_background_color').prop("disabled", false);
		jQuery('#fme_pgisfw_navigation_hover_color').prop("disabled", false);
		jQuery('#fme_pgisfw_icon_color').prop("disabled", false);
		jQuery('.fme_arrows_options').css("pointer-events", 'all');
	}




	if(jQuery('#fme_pgisfw_show_lightbox').prop('checked')){
		jQuery('#fme_pgisfw_Lightbox_frame_width').prop("disabled", false);
		jQuery('#fme_pgisfw_lightbox_bg_color').prop("disabled", false);
		jQuery('#fme_pgisfw_lightbox_bg_hover_color').prop("disabled", false);
		jQuery('#fme_pgisfw_lightbox_icon_color').prop("disabled", false);
		jQuery('#fme_pgisfw_lightbox_position').prop("disabled", false);
		jQuery('.fme_lightbox_options').css("pointer-events", 'all');
	} else {
		jQuery('#fme_pgisfw_Lightbox_frame_width').prop("disabled", true);
		jQuery('#fme_pgisfw_lightbox_bg_color').prop("disabled", true);
		jQuery('#fme_pgisfw_lightbox_bg_hover_color').prop("disabled", true);
		jQuery('#fme_pgisfw_lightbox_icon_color').prop("disabled", true);
		jQuery('#fme_pgisfw_lightbox_position').prop("disabled", true);
		jQuery('.fme_lightbox_options').css("pointer-events", 'none');
	}
	jQuery('#fme_pgisfw_show_lightbox').on('change', function(){
		if(jQuery(this).prop('checked')){
			jQuery('#fme_pgisfw_Lightbox_frame_width').prop("disabled", false);
			jQuery('#fme_pgisfw_lightbox_bg_color').prop("disabled", false);
			jQuery('#fme_pgisfw_lightbox_bg_hover_color').prop("disabled", false);
			jQuery('#fme_pgisfw_lightbox_icon_color').prop("disabled", false);
			jQuery('#fme_pgisfw_lightbox_position').prop("disabled", false);
			jQuery('.fme_lightbox_options').css("pointer-events", 'all');
		} else {
			jQuery('#fme_pgisfw_Lightbox_frame_width').prop("disabled", true);
			jQuery('#fme_pgisfw_lightbox_bg_color').prop("disabled", true);
			jQuery('#fme_pgisfw_lightbox_bg_hover_color').prop("disabled", true);
			jQuery('#fme_pgisfw_lightbox_icon_color').prop("disabled", true);
			jQuery('#fme_pgisfw_lightbox_position').prop("disabled", true);
			jQuery('.fme_lightbox_options').css("pointer-events", 'none');
		}
	});
	
	jQuery("#fme_pgisfw_numbering_enable_disable").change(function(e) {
		
		if (jQuery(this).prop('checked')==true){
			jQuery(".fme_pgisfw_numbering_color").removeAttr('hidden');
		} else {
			jQuery(".fme_pgisfw_numbering_color").attr('hidden','true');
		}

	});

	if (jQuery("#fme_pgisfw_numbering_enable_disable").prop('checked')==true) {
		
		jQuery(".fme_pgisfw_numbering_color").removeAttr('hidden');
	} else {
		jQuery(".fme_pgisfw_numbering_color").attr('hidden','true');
	}
/*************quick save rule******/
	jQuery(".quick_rule_save_btn").click(function(){
		var rule_id=jQuery(this).attr('rule_id');
		
		var rule_enable=jQuery(this).parents("tr").find('.fme_quick_enable_disable').val();
		var rule_priority=jQuery(this).parents("tr").find('.fme_quick_rule_priority').val(); 
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_quick_save_rule',
			fme_quick_rule_id:rule_id,
			fme_quick_rule_priority:rule_priority,
			fme_quick_enable_disable:rule_enable,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('.fme_quick_success_alert').show();
			jQuery('.fme_quick_success_alert').show();
			jQuery('.fme_quick_success_alert').delay(3000).fadeOut('slow');
		}   
	});

	});

/**************/
/*************quick save rule******/
	function quick_save_rule(){
		"use strict";
		var fmeproductcategory = jQuery('#fmeproductcategory').val();
	if(fmeproductcategory=='fme_pgisfw_product') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_product').val();
	} else if(fmeproductcategory=='fme_pgisfw_category') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_categorys').val();
	} else {
		var fme_pgisfw_selected_pc = [];
	}

	var  fme_auto_play = jQuery('#fme_pgisfw_autoPlay').val();
	var fme_auto_play_timeout = jQuery('#fme_pgisfw_range_slider').val();

	if(jQuery( "#fme_pgisfw_enable_disable" ).prop( "checked")==true) {
		var fme_pgisfw_enable_disable = 'yes';
	} else {
		var fme_pgisfw_enable_disable = 'no';
	} 
	if (jQuery('#fme_pgisfw_numbering_enable_disable').prop("checked")==true) {
		var fme_pgisfw_numbering_enable_disable = 'yes';
	//	jQuery("#fme_pgisfw_numbering_color").removeAttr('hidden');
		
	} else {
		var fme_pgisfw_numbering_enable_disable = 'no';
		// jQuery("#fme_pgisfw_numbering_color").attr('hidden','true');	
	}
	var fme_pgisfw_numbering_color= jQuery("#fme_pgisfw_numbering_color").val(); 
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_general_setting',
			fme_pgisfw_enable_disable:fme_pgisfw_enable_disable,
			fmeproductcategory:fmeproductcategory,
			fme_pgisfw_selected_pc:fme_pgisfw_selected_pc,
			fme_auto_play:fme_auto_play,
			fme_auto_play_timeout:fme_auto_play_timeout,
			fme_pgisfw_numbering_enable_disable:fme_pgisfw_numbering_enable_disable,
			fme_pgisfw_numbering_color:fme_pgisfw_numbering_color,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_general_settings_msg').show();
			jQuery('#fme_general_settings_msg').delay(3000).fadeOut('slow');
		}   
	});

	}

/**************/
});

function Fme_pgisfw_choosen_product_cateory(fme_pgisfw_type) {
	"use strict";
	if('fme_create' == fme_pgisfw_type) {
		var fme_product_category = jQuery('#fmeproductcategory').val();
		if('fme_pgisfw_category' == fme_product_category) {
			jQuery('#fme_pgisfw_Products').hide();
			jQuery('#fme_pgisfw_category').css('display','contents');
		} else if('fme_pgisfw_product' == fme_product_category) {
			jQuery('#fme_pgisfw_Products').css('display','contents');
			jQuery('#fme_pgisfw_category').hide();
		} else {
			jQuery('#fme_pgisfw_Products').hide();
			jQuery('#fme_pgisfw_category').hide();
		}
	} 
}

function save_settings() {
	"use strict";
	var fme_pgisfw_navigation_icon = jQuery('#fme_pgisfw_navigation_icon').val();
	var fme_pgisfw_navigation_background_color = jQuery('#fme_pgisfw_navigation_background_color').val();
	var fme_pgisfw_icon_color = jQuery('#fme_pgisfw_icon_color').val();
	var fme_pgisfw_border_color = jQuery('#fme_pgisfw_border_color').val();
	var fme_pgisfw_slider_layout = jQuery('#fme_pgisfw_slider_layout').val();
	var fmeproductcategory = jQuery('#fmeproductcategory').val();
	if(fmeproductcategory=='fme_pgisfw_product') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_product').val();
	} else if(fmeproductcategory=='fme_pgisfw_category') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_categorys').val();
	} else {
		var fme_pgisfw_selected_pc = [];
	}
	var fme_thumbnails_to_show	 = jQuery('#fme_pgisfw_thumbs').val();
	var  fme_auto_play = jQuery('#fme_pgisfw_autoPlay').val();
	var fme_auto_play_timeout = jQuery('#fme_pgisfw_range_slider').val();

	if(jQuery( "#fme_pgisfw_show_zoom" ).prop( "checked")==true) {
		var fme_pgisfw_show_zoom = 'yes';
	} else {
		var fme_pgisfw_show_zoom = 'no';
	} 

	if(jQuery( "#fme_pgisfw_hide_thumbnails" ).prop( "checked")==true) {
		var fme_pgisfw_hide_thumbnails = 'yes';
	} else {
		var fme_pgisfw_hide_thumbnails = 'no';
	} 

	if(jQuery( "#fme_pgisfw_show_lightbox" ).prop( "checked")==true) {
		var fme_pgisfw_show_lightbox = 'yes';
	} else {
		var fme_pgisfw_show_lightbox = 'no';
	} 
	var fme_pgisfw_Lightbox_frame_width = jQuery('#fme_pgisfw_Lightbox_frame_width').val();
	var fme_pgisfw_zoombox_frame_width = jQuery('#fme_pgisfw_zoombox_frame_width').val();
	var fme_pgisfw_zoombox_frame_height = jQuery('#fme_pgisfw_zoombox_frame_height').val();

	if(!fme_thumbnails_to_show) {
		fme_thumbnails_to_show = 4;
	}

	if(!fme_pgisfw_Lightbox_frame_width) {
		fme_pgisfw_Lightbox_frame_width = 600;
	}

	if(!fme_pgisfw_zoombox_frame_width) {
		fme_pgisfw_zoombox_frame_width = 75;
	}

	if(!fme_pgisfw_zoombox_frame_height) {
		fme_pgisfw_zoombox_frame_height = 75;
	}




	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_setting',
			fme_pgisfw_navigation_icon:fme_pgisfw_navigation_icon,
			fme_pgisfw_navigation_background_color:fme_pgisfw_navigation_background_color,
			fme_pgisfw_icon_color:fme_pgisfw_icon_color,
			fme_pgisfw_border_color:fme_pgisfw_border_color,
			fme_pgisfw_slider_layout:fme_pgisfw_slider_layout,
			fmeproductcategory:fmeproductcategory,
			fme_pgisfw_selected_pc:fme_pgisfw_selected_pc,
			fme_thumbnails_to_show:fme_thumbnails_to_show,
			fme_auto_play:fme_auto_play,
			fme_auto_play_timeout:fme_auto_play_timeout,
			fme_pgisfw_show_zoom:fme_pgisfw_show_zoom,
			fme_pgisfw_hide_thumbnails:fme_pgisfw_hide_thumbnails,
			fme_pgisfw_show_lightbox:fme_pgisfw_show_lightbox,
			fme_pgisfw_Lightbox_frame_width:fme_pgisfw_Lightbox_frame_width,
			fme_pgisfw_zoombox_frame_width:fme_pgisfw_zoombox_frame_width,
			fme_pgisfw_zoombox_frame_height:fme_pgisfw_zoombox_frame_height,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_settings_msg').show();
			jQuery('#fme_settings_msg').delay(3000).fadeOut('slow');
		}   
	});
}


function save_general_settings() {
	"use strict";
	var fmeproductcategory = jQuery('#fmeproductcategory').val();

	// input[name="fma_popup_icons"]:checked
	var fme_pgisfw_image_aspect_ratio= jQuery('input[name="fme_pgisfw_image_aspect_ratio"]:checked').val()|| 'off';
	if(fmeproductcategory=='fme_pgisfw_product') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_product').val();
	} else if(fmeproductcategory=='fme_pgisfw_category') {
		var fme_pgisfw_selected_pc = jQuery('#fme_pgisfw_categorys').val();
	} else {
		var fme_pgisfw_selected_pc = [];
	}

	var  fme_auto_play = jQuery('#fme_pgisfw_autoPlay').val();
	var fme_auto_play_timeout = jQuery('#fme_pgisfw_range_slider').val();

	if(jQuery( "#fme_pgisfw_enable_disable" ).prop( "checked")==true) {
		var fme_pgisfw_enable_disable = 'yes';
	} else {
		var fme_pgisfw_enable_disable = 'no';
	} 
	if (jQuery('#fme_pgisfw_numbering_enable_disable').prop("checked")==true) {
		var fme_pgisfw_numbering_enable_disable = 'yes';
	//	jQuery("#fme_pgisfw_numbering_color").removeAttr('hidden');
		
	} else {
		var fme_pgisfw_numbering_enable_disable = 'no';
		// jQuery("#fme_pgisfw_numbering_color").attr('hidden','true');	
	}
	var fme_pgisfw_numbering_color= jQuery("#fme_pgisfw_numbering_color").val(); 
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_general_setting',
			fme_pgisfw_enable_disable:fme_pgisfw_enable_disable,
			fmeproductcategory:fmeproductcategory,
			fme_pgisfw_selected_pc:fme_pgisfw_selected_pc,
			fme_auto_play:fme_auto_play,
			fme_auto_play_timeout:fme_auto_play_timeout,
			fme_pgisfw_image_aspect_ratio:fme_pgisfw_image_aspect_ratio,
			fme_pgisfw_numbering_enable_disable:fme_pgisfw_numbering_enable_disable,
			fme_pgisfw_numbering_color:fme_pgisfw_numbering_color,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			
			window.onbeforeunload = null;
			jQuery('#fme_general_settings_msg').show();
			jQuery('#fme_general_settings_msg').delay(3000).fadeOut('slow');
		}   
	});

}

function save_thumbnail_settings() {
	"use strict";
	var fme_pgisfw_border_color = jQuery('#fme_pgisfw_border_color').val();
	var fme_pgisfw_slider_layout = jQuery('#fme_pgisfw_slider_layout').val();
	var fme_pgisfw_slider_mode = jQuery('#fme_pgisfw_slider_mode').val();
	var fme_thumbnails_to_show	 = jQuery('#fme_pgisfw_thumbs').val();
	var fme_pgisfw_slider_images_style = jQuery('#fme_pgisfw_slider_images_style').val();

	if(jQuery( "#fme_pgisfw_hide_thumbnails" ).prop( "checked")==true) {
		var fme_pgisfw_hide_thumbnails = 'yes';
	} else {
		var fme_pgisfw_hide_thumbnails = 'no';
	} 

	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_thumbnail_settings',
			fme_pgisfw_border_color:fme_pgisfw_border_color,
			fme_pgisfw_slider_mode :fme_pgisfw_slider_mode,
			fme_pgisfw_slider_layout:fme_pgisfw_slider_layout,
			fme_pgisfw_slider_images_style: fme_pgisfw_slider_images_style,
			fme_thumbnails_to_show:fme_thumbnails_to_show,
			fme_pgisfw_hide_thumbnails:fme_pgisfw_hide_thumbnails,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_thumbnail_settings_msg').show();
			jQuery('#fme_thumbnail_settings_msg').delay(3000).fadeOut('slow');
		}   
	});



}

function save_lightbox_settings() {
	"use strict";

	if(jQuery( "#fme_pgisfw_show_lightbox" ).prop( "checked")==true) {
		var fme_pgisfw_show_lightbox = 'yes';
	} else {
		var fme_pgisfw_show_lightbox = 'no';
	} 
	var fme_pgisfw_Lightbox_frame_width = jQuery('#fme_pgisfw_Lightbox_frame_width').val();


	if(!fme_pgisfw_Lightbox_frame_width) {
		fme_pgisfw_Lightbox_frame_width = 600;
	}

	var fme_pgisfw_lightbox_bg_color = jQuery('#fme_pgisfw_lightbox_bg_color').val();
	var fme_pgisfw_lightbox_bg_hover_color = jQuery('#fme_pgisfw_lightbox_bg_hover_color').val();
	var fme_pgisfw_lightbox_icon_color = jQuery('#fme_pgisfw_lightbox_icon_color').val();
	var fme_pgisfw_lightbox_position = jQuery('#fme_pgisfw_lightbox_position').val();

	var fme_selected_lightbox_image = jQuery('.fme_selected_lightbox_image').attr('curr_image');

	var fme_pgisfw_lightbox_slide_effect = jQuery('#fme_pgisfw_lightbox_slide_effect').val();
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_lightbox_settings',
			fme_pgisfw_show_lightbox:fme_pgisfw_show_lightbox,
			fme_pgisfw_Lightbox_frame_width:fme_pgisfw_Lightbox_frame_width,
			fme_pgisfw_lightbox_bg_color:fme_pgisfw_lightbox_bg_color,
			fme_pgisfw_lightbox_bg_hover_color:fme_pgisfw_lightbox_bg_hover_color,
			fme_selected_lightbox_image:fme_selected_lightbox_image,
			fme_pgisfw_lightbox_icon_color:fme_pgisfw_lightbox_icon_color,
			fme_pgisfw_lightbox_position:fme_pgisfw_lightbox_position,
			fme_pgisfw_lightbox_slide_effect:fme_pgisfw_lightbox_slide_effect,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_lightbox_settings_msg').show();
			jQuery('#fme_lightbox_settings_msg').delay(3000).fadeOut('slow');
		}   
	});



}


function save_arrows_settings() {
	"use strict";
	var fme_pgisfw_navigation_icon = jQuery('#fme_pgisfw_navigation_icon').val();
	var fme_pgisfw_navigation_background_color = jQuery('#fme_pgisfw_navigation_background_color').val();
	var fme_pgisfw_icon_color = jQuery('#fme_pgisfw_icon_color').val();
	var fme_selected_image = jQuery('.fme_selected_image').attr('curr_image');
	var fme_pgisfw_navigation_icon_show_on = jQuery('#fme_pgisfw_navigation_icon_show_on').val();
	var fme_pgisfw_navigation_hover_color = jQuery('#fme_pgisfw_navigation_hover_color').val();
	var fme_pgisfw_navigation_icon_shape = jQuery('#fme_pgisfw_navigation_icon_shape').val();

	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_arrows_settings',
			fme_pgisfw_navigation_icon:fme_pgisfw_navigation_icon,
			fme_pgisfw_navigation_background_color:fme_pgisfw_navigation_background_color,
			fme_pgisfw_icon_color:fme_pgisfw_icon_color,
			fme_selected_image:fme_selected_image,
			fme_pgisfw_navigation_icon_show_on:fme_pgisfw_navigation_icon_show_on,
			fme_pgisfw_navigation_hover_color:fme_pgisfw_navigation_hover_color,
			fme_pgisfw_navigation_icon_shape:fme_pgisfw_navigation_icon_shape,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_arrows_settings_msg').show();
			jQuery('#fme_arrows_settings_msg').delay(3000).fadeOut('slow');
		}   
	});
}


function save_bullets_settings() {
	"use strict";
	if(jQuery( "#fme_pgisfw_show_bullets" ).prop( "checked")==true) {
		var fme_pgisfw_show_bullets = 'yes';
	} else {
		var fme_pgisfw_show_bullets = 'no';
	} 

	var fme_pgisfw_bullets_shape = jQuery('#fme_pgisfw_bullets_shape').val();
	var fme_pgisfw_bullets_color = jQuery('#fme_pgisfw_bullets_color').val();
	var fme_pgisfw_bullets_hover_color = jQuery('#fme_pgisfw_bullets_hover_color').val();
	var fme_pgisfw_bullets_position = jQuery('#fme_pgisfw_bullets_position').val();
	var fme_pgisfw_bullets_inside_position = jQuery('#fme_pgisfw_bullets_inside_position').val();
	var fme_pgisfw_counter_bullets_font_color=jQuery('#fme_pgisfw_counter_bullets_font_color').val();
	// var fme_pgisfw_bullets_thumbnail = jQuery('#fme_pgisfw_bullets_thumbnail').val();
	if(jQuery( "#fme_pgisfw_bullets_thumbnail" ).prop( "checked")==true) {
		var fme_pgisfw_bullets_thumbnail = 'yes';
	} else {
		var fme_pgisfw_bullets_thumbnail = 'no';
	} 

	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_bullets_settings',
			fme_pgisfw_show_bullets:fme_pgisfw_show_bullets,
			fme_pgisfw_bullets_shape:fme_pgisfw_bullets_shape,
			fme_pgisfw_bullets_color:fme_pgisfw_bullets_color,
			fme_pgisfw_bullets_hover_color:fme_pgisfw_bullets_hover_color,
			fme_pgisfw_bullets_position:fme_pgisfw_bullets_position,
			fme_pgisfw_bullets_thumbnail:fme_pgisfw_bullets_thumbnail,
			fme_pgisfw_bullets_inside_position:fme_pgisfw_bullets_inside_position,
			fme_pgisfw_counter_bullets_font_color:fme_pgisfw_counter_bullets_font_color,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce

		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_bullets_settings_msg').show();
			jQuery('#fme_bullets_settings_msg').delay(3000).fadeOut('slow');
		}   
	});
}



function save_zoom_settings() {
	"use strict";

	if(jQuery( "#fme_pgisfw_show_zoom" ).prop( "checked")==true) {
		var fme_pgisfw_show_zoom = 'yes';
	} else {
		var fme_pgisfw_show_zoom = 'no';
	} 

	var fme_pgisfw_zoombox_frame_width = jQuery('#fme_pgisfw_zoombox_frame_width').val();
	var fme_pgisfw_zoombox_frame_height = jQuery('#fme_pgisfw_zoombox_frame_height').val();
	var fme_pgisfw_zoombox_radius = jQuery('#fme_pgisfw_zoombox_radius').val();

	if(!fme_pgisfw_zoombox_frame_width) {
		fme_pgisfw_zoombox_frame_width = 75;
	}

	if(!fme_pgisfw_zoombox_frame_height) {
		fme_pgisfw_zoombox_frame_height = 75;
	}
	
	if(!fme_pgisfw_zoombox_radius) {
		fme_pgisfw_zoombox_radius = 10;
	}



	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_zoom_settings',
			fme_pgisfw_show_zoom:fme_pgisfw_show_zoom,
			fme_pgisfw_zoombox_frame_width:fme_pgisfw_zoombox_frame_width,
			fme_pgisfw_zoombox_frame_height:fme_pgisfw_zoombox_frame_height,
			fme_pgisfw_zoombox_radius:fme_pgisfw_zoombox_radius,
			fme_pgisfw_nonce :ewcpm_php_vars.fme_pgisfw_nonce
		},
		success: function (data) {
			window.onbeforeunload = null;
			jQuery('#fme_zoom_settings_msg').show();
			jQuery('#fme_zoom_settings_msg').delay(3000).fadeOut('slow');
		}   
	});
}
