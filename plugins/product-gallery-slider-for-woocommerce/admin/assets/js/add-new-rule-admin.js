jQuery(document).ready(function() {
	"use strict";

	jQuery('.fme_pgisfw_btn').hide();
		

	jQuery('.fme_arrow_images').on('click', function(){
		jQuery('.fme_arrow_images').removeClass("fme_selected_image");
		jQuery(this).addClass("fme_selected_image");
	});

	jQuery('.fme_lightbox_images').on('click', function(){
		jQuery('.fme_lightbox_images').removeClass("fme_selected_lightbox_image");
		jQuery(this).addClass("fme_selected_lightbox_image");

	});

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
					url: myruleajax.ajaxurl,
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
});

function save_new_rule_settings(){
	var fme_pgisfw_image_aspect_ratio= jQuery('input[name="fme_pgisfw_image_aspect_ratio"]:checked').val()|| 'off';
	
	//general
	var fme_pgisfw_rule_name= jQuery('#fme_pgisfw_rule_name').val();
	if (fme_pgisfw_rule_name=='') {
		fme_pgisfw_rule_name= 'Untitled_'+ Math.floor(Math.random() * 100);
	}

	var fme_pgisfw_rule_priority=jQuery('#fme_pgisfw_rule_priority').val();
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

	if(jQuery( "#fme_pgisfw_rule_enable_disable" ).prop( "checked")==true) {
		var fme_pgisfw_rule_enable_disable = 'yes';
	} else {
		var fme_pgisfw_rule_enable_disable = 'no';
	} 
	if (jQuery('#fme_pgisfw_numbering_enable_disable').prop("checked")==true) {
		var fme_pgisfw_numbering_enable_disable = 'yes';
	//	jQuery("#fme_pgisfw_numbering_color").removeAttr('hidden');
		
	} else {
		var fme_pgisfw_numbering_enable_disable = 'no';
		// jQuery("#fme_pgisfw_numbering_color").attr('hidden','true');	
	}
	var fme_pgisfw_numbering_color= jQuery("#fme_pgisfw_numbering_color").val();

	//thumbnail
	var fme_pgisfw_border_color = jQuery('#fme_pgisfw_border_color').val();
	var fme_pgisfw_slider_mode = jQuery('#fme_pgisfw_slider_mode').val();
	var fme_pgisfw_slider_layout = jQuery('#fme_pgisfw_slider_layout').val();
	var fme_pgisfw_slider_images_style = jQuery('#fme_pgisfw_slider_images_style').val();
	var fme_thumbnails_to_show	 = jQuery('#fme_pgisfw_thumbs').val();
	
	if(jQuery( "#fme_pgisfw_hide_thumbnails" ).prop( "checked")==true) {
		var fme_pgisfw_hide_thumbnails = 'yes';
	} else {
		var fme_pgisfw_hide_thumbnails = 'no';
	}
	//lightbox
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
	

	//arrows
	var fme_pgisfw_navigation_icon = jQuery('#fme_pgisfw_navigation_icon').val();
	var fme_pgisfw_navigation_background_color = jQuery('#fme_pgisfw_navigation_background_color').val();
	var fme_pgisfw_icon_color = jQuery('#fme_pgisfw_icon_color').val();
	var fme_selected_image = jQuery('.fme_selected_image').attr('curr_image');
	var fme_pgisfw_navigation_icon_show_on = jQuery('#fme_pgisfw_navigation_icon_show_on').val();
	var fme_pgisfw_navigation_hover_color = jQuery('#fme_pgisfw_navigation_hover_color').val();
	var fme_pgisfw_navigation_icon_shape = jQuery('#fme_pgisfw_navigation_icon_shape').val(); 

	//bullets
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

	//zoombox

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
	// check if rule is updated by checking rule id
	var rule_id=jQuery('#fme_pgisfw_update_rule_btn').attr('rule_id');
	jQuery.ajax({
		method:'POST',
		url: myruleajax.ajaxurl,
		type: 'post',
		data: {
			action: 'fme_pgisfw_save_rule_settings',
			fme_pgisfw_rule_enable_disable:fme_pgisfw_rule_enable_disable,
			fme_pgisfw_rule_name:fme_pgisfw_rule_name,
			fme_pgisfw_rule_priority:fme_pgisfw_rule_priority,
			fmeproductcategory:fmeproductcategory,
			fme_pgisfw_selected_pc:fme_pgisfw_selected_pc,
			fme_auto_play:fme_auto_play,
			fme_auto_play_timeout:fme_auto_play_timeout,
			fme_pgisfw_numbering_enable_disable:fme_pgisfw_numbering_enable_disable,
			fme_pgisfw_image_aspect_ratio:fme_pgisfw_image_aspect_ratio,
			fme_pgisfw_numbering_color:fme_pgisfw_numbering_color,
			//thumbnail
			fme_pgisfw_border_color:fme_pgisfw_border_color,
			fme_pgisfw_slider_mode : fme_pgisfw_slider_mode,
			fme_pgisfw_slider_layout:fme_pgisfw_slider_layout,
			fme_pgisfw_slider_images_style:fme_pgisfw_slider_images_style,
			fme_thumbnails_to_show:fme_thumbnails_to_show,
			fme_pgisfw_hide_thumbnails:fme_pgisfw_hide_thumbnails,
			//lightbox
			fme_pgisfw_show_lightbox:fme_pgisfw_show_lightbox,
			fme_pgisfw_Lightbox_frame_width:fme_pgisfw_Lightbox_frame_width,
			fme_pgisfw_lightbox_bg_color:fme_pgisfw_lightbox_bg_color,
			fme_pgisfw_lightbox_bg_hover_color:fme_pgisfw_lightbox_bg_hover_color,
			fme_selected_lightbox_image:fme_selected_lightbox_image,
			fme_pgisfw_lightbox_icon_color:fme_pgisfw_lightbox_icon_color,
			fme_pgisfw_lightbox_position:fme_pgisfw_lightbox_position,
			fme_pgisfw_lightbox_slide_effect:fme_pgisfw_lightbox_slide_effect,
			//arrows
			fme_pgisfw_navigation_icon:fme_pgisfw_navigation_icon,
			fme_pgisfw_navigation_background_color:fme_pgisfw_navigation_background_color,
			fme_pgisfw_icon_color:fme_pgisfw_icon_color,
			fme_selected_image:fme_selected_image,
			fme_pgisfw_navigation_icon_show_on:fme_pgisfw_navigation_icon_show_on,
			fme_pgisfw_navigation_hover_color:fme_pgisfw_navigation_hover_color,
			fme_pgisfw_navigation_icon_shape:fme_pgisfw_navigation_icon_shape,
			//bullets
			fme_pgisfw_show_bullets:fme_pgisfw_show_bullets,
			fme_pgisfw_bullets_shape:fme_pgisfw_bullets_shape,
			fme_pgisfw_bullets_color:fme_pgisfw_bullets_color,
			fme_pgisfw_bullets_hover_color:fme_pgisfw_bullets_hover_color,
			fme_pgisfw_bullets_position:fme_pgisfw_bullets_position,
			fme_pgisfw_bullets_thumbnail:fme_pgisfw_bullets_thumbnail,
			fme_pgisfw_bullets_inside_position:fme_pgisfw_bullets_inside_position,
			fme_pgisfw_counter_bullets_font_color:fme_pgisfw_counter_bullets_font_color,
			//zoom box
			fme_pgisfw_show_zoom:fme_pgisfw_show_zoom,
			fme_pgisfw_zoombox_frame_width:fme_pgisfw_zoombox_frame_width,
			fme_pgisfw_zoombox_frame_height:fme_pgisfw_zoombox_frame_height,
			fme_pgisfw_zoombox_radius:fme_pgisfw_zoombox_radius,

			//edit rule id
			rule_id:rule_id,
			fme_pgisfw_new_rule_nonce:myruleajax.fme_pgisfw_new_rule_nonce

		},
		success: function (r) {
			
			r=JSON.parse(r);
			// window.onbeforeunload = null;
			if (r!=''){
			jQuery('#fme_pgisfw_update_rule_btn').attr("rule_id",r);
		}
			
			jQuery('#fme_general_settings_msg').show();
			
			jQuery('#fme_general_settings_msg').delay(3000).fadeOut('slow');
		}   
	});


}
