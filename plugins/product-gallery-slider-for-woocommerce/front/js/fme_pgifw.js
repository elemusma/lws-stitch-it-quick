jQuery(document).ready(function(){
		
	var width = jQuery('.fme_images').width();
	
	var height = 0;
	jQuery('.fme_small').css('height', width);
	jQuery('.fme_small').css('width', width);
	jQuery('.fme_video_size').attr('width', width);
	jQuery('.fme_video_size').attr('height', width);
	jQuery('.tc_video_slide_primary').css('width', width);
	jQuery('.tc_video_slide_primary').css('height', width);

	var lightbox_frame_width= fme_pgisfw_settings_data.fme_pgisfw_Lightbox_frame_width;
	var curr_select_arrow = fme_pgisfw_settings_data.fme_selected_image;
	var show_arrow_on = fme_pgisfw_settings_data.fme_pgisfw_navigation_icon_show_on;
	var arrow_hover_color = fme_pgisfw_settings_data.fme_pgisfw_navigation_hover_color;
	var arrow_shape = fme_pgisfw_settings_data.fme_pgisfw_navigation_icon_shape;
	var lightbox_icon_color = fme_pgisfw_settings_data.fme_pgisfw_lightbox_icon_color;
	var bullet_enabled = fme_pgisfw_settings_data.fme_pgisfw_show_bullets;
	var bullet_shape = fme_pgisfw_settings_data.fme_pgisfw_bullets_shape;
	var bullet_bg_color = fme_pgisfw_settings_data.fme_pgisfw_bullets_color;
	var bullet_hover_color = fme_pgisfw_settings_data.fme_pgisfw_bullets_hover_color;
	var bullets_position = fme_pgisfw_settings_data.fme_pgisfw_bullets_position;
	var bullets_thumbnail = fme_pgisfw_settings_data.fme_pgisfw_bullets_thumbnail;
	var bullets_inside_position = fme_pgisfw_settings_data.fme_pgisfw_bullets_inside_position;
	var bullet_counter_font_color=fme_pgisfw_settings_data.fme_pgisfw_counter_bullets_font_color;
	var fme_pgisfw_numbering_enable_disable=fme_pgisfw_settings_data.fme_pgisfw_numbering_enable_disable;
	
	var lightbox_slide_effect = fme_pgisfw_settings_data.fme_pgisfw_lightbox_slide_effect;

	if ( lightbox_slide_effect=='' || lightbox_slide_effect==null || lightbox_slide_effect==undefined) {
		lightbox_slide_effect='slide';
	}

	//////////////////////////////// arrow_paths bellow contain svg path for slider arrows dont change them //////////////////////////////////////

	var arrows_paths = ['M34.95,18.86l-11.1-11.1c-0.32-0.32-0.74-0.49-1.19-0.49c-0.45,0-0.87,0.17-1.19,0.49l-1.01,1.01	c-0.32,0.32-0.49,0.74-0.49,1.19c0,0.45,0.17,0.89,0.49,1.2l6.47,6.49H6.37c-0.93,0-1.66,0.73-1.66,1.65v1.42 c0,0.93,0.73,1.73,1.66,1.73h20.64l-6.55,6.52c-0.32,0.32-0.49,0.73-0.49,1.18c0,0.45,0.17,0.87,0.49,1.18l1.01,1	c0.32,0.32,0.74,0.49,1.19,0.49c0.45,0,0.87-0.17,1.19-0.49l11.1-11.1c0.32-0.32,0.49-0.74,0.49-1.19 C35.44,19.6,35.26,19.18,34.95,18.86z M20,40.01C8.97,40.01,0,31.04,0,20S8.97,0,20,0c11.03,0,20,8.97,20,20S31.04,40.01,20,40.01z M20,0.01c-11.03,0-20,8.97-20,20c0,11.03,8.97,20,20,20s20-8.97,20-20C40,8.98,31.03,0.01,20,0.01z',
	'M34.66,19.39l-8.34-9.26c-0.18-0.2-0.43-0.31-0.69-0.31h-5.56c-0.36,0-0.7,0.21-0.85,0.55c-0.15,0.34-0.09,0.73,0.16,1	L27.17,20l-7.78,8.64c-0.24,0.27-0.31,0.66-0.16,1c0.15,0.34,0.48,0.55,0.85,0.55h5.56c0.26,0,0.51-0.11,0.69-0.31l8.34-9.26 C34.98,20.27,34.98,19.74,34.66,19.39z M22.62,19.39l-8.34-9.26c-0.18-0.2-0.43-0.31-0.69-0.31H8.04c-0.36,0-0.7,0.21-0.85,0.55 c-0.15,0.34-0.09,0.73,0.16,1L15.13,20l-7.78,8.64c-0.24,0.27-0.31,0.66-0.16,1c0.15,0.34,0.48,0.55,0.85,0.55h5.56 c0.26,0,0.51-0.11,0.69-0.31l8.34-9.26C22.94,20.27,22.94,19.74,22.62,19.39z M20,40.01c-11.03,0-20-8.97-20-20C0,8.97,8.97,0,20,0	c11.03,0,20,8.97,20,20C40.01,31.04,31.04,40.01,20,40.01z M20,0.01C8.98,0.01,0.01,8.98,0.01,20S8.98,40,20,40 c11.03,0,20-8.97,20-20S31.03,0.01,20,0.01z',
	'M17.95,6.26c-0.91-0.91-2.38-0.91-3.28,0c-0.91,0.91-0.91,2.38,0,3.28l9.97,9.97l-9.97,9.97c-0.91,0.91-0.91,2.38,0,3.28 s2.38,0.91,3.28,0l11.61-11.61c0.91-0.91,0.91-2.38,0-3.28L17.95,6.26z M20,40.01c-11.03,0-20-8.97-20-20C0,8.97,8.97,0,20,0 c11.03,0,20,8.97,20,20C40.01,31.04,31.04,40.01,20,40.01z M20,0.01C8.98,0.01,0.01,8.98,0.01,20S8.98,40,20,40	c11.03,0,20-8.97,20-20S31.03,0.01,20,0.01z',
	'M11.7,6.5c0.64-0.21,1.3,0.04,1.84,0.39c4.87,2.85,9.8,5.59,14.66,8.45c1.64,0.91,3.26,1.86,4.91,2.74 c0.39,0.24,0.8,0.51,1.05,0.91c0,0.41,0.09,0.86-0.07,1.26c-0.67,0.73-1.65,1.07-2.48,1.57c-5.9,3.29-11.67,6.8-17.54,10.14 c-0.64,0.36-1.3,0.86-2.08,0.71c-0.47,0.04-0.8-0.37-0.9-0.79c-0.04-0.7,0.25-1.36,0.45-2.02c1.07-3.22,2.29-6.38,3.42-9.58 c0.14-0.4,0.13-0.85,0.01-1.25c-1.25-3.47-2.57-6.91-3.68-10.43C11.05,7.89,10.88,6.87,11.7,6.5z M40.01,20c0-11.03-8.97-20-20-20 S0,8.97,0,20s8.97,20,20,20S40.01,31.04,40.01,20z M40,20c0,11.03-8.97,20-20,20s-20-8.97-20-20s8.97-20,20-20S40,8.98,40,20z',
	'M20,40.01C8.97,40.01,0,31.04,0,20S8.97,0,20,0c11.03,0,20,8.97,20,20S31.04,40.01,20,40.01z M20,0.01 c-11.03,0-20,8.97-20,20c0,11.03,8.97,20,20,20s20-8.97,20-20C40,8.98,31.03,0.01,20,0.01z M15.32,9.78 c3.02,2.65,6.05,5.29,9.06,7.96c0.76,0.66,1.62,1.23,2.16,2.08c-3.56,2.93-6.92,6.09-10.44,9.07c-0.37,0.35-0.93,0.69-0.78,1.3 c-0.02,2.04,0,4.08,0.01,6.12c6.35-5.54,12.7-11.08,18.98-16.69C27.92,14.17,21.64,8.58,15.34,3C15.31,5.26,15.34,7.52,15.32,9.78z M15.37,26.71c2.33-2.05,4.65-4.09,6.98-6.12c0.27-0.34,0.84-0.57,0.85-1.04c-2.6-2.33-5.27-4.59-7.86-6.92 C15.34,17.32,15.28,22.02,15.37,26.71z'
	];

	//////////////////////////////// arrow_paths above contain svg path for slider arrows dont change them /////////////////////////////////////



	jQuery('.fme_lightbox_icons').css('color', lightbox_icon_color);

	var elements = [];
	jQuery("input[name='fme_all_urls[]']").map(
		function(){

			var elem =	{
				'href': jQuery(this).val(),
				'type': jQuery(this).attr('url_type'),
			}

			elements.push(elem);
		}).get();

	const myGallery = GLightbox({
		elements: elements,
		autoplayVideos: false,
		slideEffect:lightbox_slide_effect,
		loop:true,

	});

	var navigation_icon= fme_pgisfw_settings_data.fme_pgisfw_navigation_icon_status;
	if (navigation_icon=='true') {
		navigation_icon=true;
	} else {
		navigation_icon=false;
	}
	var navigation_background_color= fme_pgisfw_settings_data.fme_pgisfw_navigation_background_color;
	var icon_color= fme_pgisfw_settings_data.fme_pgisfw_icon_color;
	var fme_pgisfw_slider_mode = fme_pgisfw_settings_data.fme_pgisfw_slider_mode;
	var slider_layout= fme_pgisfw_settings_data.fme_pgisfw_slider_layout;
	var thumbnails_to_show= fme_pgisfw_settings_data.fme_thumbnails_to_show;
	var auto_play= fme_pgisfw_settings_data.fme_auto_play;
	var numbering_color=fme_pgisfw_settings_data.fme_pgisfw_numbering_color;
	if (auto_play=='true') {
		auto_play=true;
	} else {
		auto_play=false;
	}
	
	var fme_pgifw_admin_url = fme_pgisfw_settings_data.fme_pgifw_admin_url;
	jQuery( ".woocommerce" ).on( "reset_data", function ( event, variation ) {
		var flag = 99;
		var Autoplay = secondary_slider.Components.Autoplay;
		var Autoplay2 = primary_slider.Components.Autoplay;
if(typeof Autoplay != 'undefined' && Autoplay != null){
		Autoplay.play(flag);
	}
		if(typeof Autoplay2 != 'undefined' && Autoplay2 != null){
		Autoplay2.play(flag);
	}
		

	})
	jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
	  var flag = 99;
	  var Autoplay = secondary_slider.Components.Autoplay;
	  var Autoplay2 = primary_slider.Components.Autoplay;
if(typeof Autoplay != 'undefined' && Autoplay != null){
	    Autoplay.pause(flag);
	}
	if(typeof Autoplay2 != 'undefined' && Autoplay2 != null){
	    Autoplay2.pause(flag);
	}
		jQuery.ajax({
			url : fme_pgifw_admin_url,
			type : 'post',
			data : {
				action : 'fme_pgisfw_check_variation_product_image',
				variation_id : variation.image_id, 
			},
			success : function( response ) {
				
				var count = 0;
				var i = -1;
				jQuery('.splide__slide').each(function(index) {
					selectedimgurl=jQuery(this).find('.fme_pgifw_image_url_check').attr('src');

					if(!jQuery(this).hasClass('splide__slide--clone'))
					{
						
							selectedimgurl=selectedimgurl.replace('http://','https://');
							response=response.replace('http://','https://');
						
						i=i+1;
						if (response===selectedimgurl) {
						
							secondary_slider.go( i );
							jQuery('.splide__pagination__page').css('background', bullet_bg_color);
							jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
							return false;
						} else {
							jQuery(this).removeClass('is-active');
						}
					}
					count++;
				});
				fme_pgifw_stop_video();
			}
		});
	});

	jQuery('body').on('click','.splide__slide', function() {

		response=jQuery(this).find('.fme_pgifw_image_url_check').attr('src');

		var count = 0;
		var i = -1;
		jQuery('.splide__slide').each(function(index) {
			
			selectedimgurl=jQuery(this).find('.fme_pgifw_image_url_check').attr('src');
			
			if(!jQuery(this).hasClass('splide__slide--clone') )
			{
			

				i=i+1;
				if (response===selectedimgurl) {
				
					secondary_slider.go( i );
					jQuery('.splide__pagination__page').css('background', bullet_bg_color);
					jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
					return false;
				} else {
					jQuery(this).removeClass('is-active');
				}
			}
			count++;
		});

		fme_pgifw_stop_video();

	});

	jQuery('body').on('click', '.splide__arrow', function(){

		fme_pgifw_stop_video();

	});

	/****numbering enable disable***/
	if (fme_pgisfw_numbering_enable_disable=='yes') {
		jQuery('.fme-slider-numbering').show();
		jQuery('.fme-slider-numbering').css("color", numbering_color);
		
	} else {
		jQuery('.fme-slider-numbering').hide();
	}

	/*******/
	var auto_play_timeout= fme_pgisfw_settings_data.fme_auto_play_timeout;
	auto_play_timeout = parseInt(auto_play_timeout);
	var show_zoom= fme_pgisfw_settings_data.fme_pgisfw_show_zoom;
	var hide_thumbnails= fme_pgisfw_settings_data.fme_pgisfw_hide_thumbnails;
	var show_lightbox= fme_pgisfw_settings_data.fme_pgisfw_show_lightbox;
	var lightbox_bg_color= fme_pgisfw_settings_data.fme_pgisfw_lightbox_bg_color;
	var lightbox_bg_hover_color= fme_pgisfw_settings_data.fme_pgisfw_lightbox_bg_hover_color;
	
	setTimeout(function(){
		var img_width = jQuery('.fme_small').width();
		var img_height = jQuery('.fme_small').height();
		jQuery('.fme-primary-next').css('left', (parseInt(img_width)-jQuery('.fme-primary-next').width())+'px');

		jQuery('.fme-primary-arrow').css('top', (parseInt(img_height)/2)+'px');
		if(slider_layout != 'horizontal'){
			jQuery('.fme_images').css('display','-webkit-box');
		}
		jQuery('.secondary-class-arrows').css('display','none');
		jQuery('.fme-primary-arrows').css('display','none');
		jQuery('.splide__arrow--next').css('background',navigation_background_color);
		jQuery('.splide__arrow--prev').css('background',navigation_background_color);
		jQuery('.splide__arrow--next').css('fill',icon_color);
		jQuery('.splide__arrow--prev').css('fill',icon_color);



		if (show_arrow_on == 'hover') {
			jQuery(".primary-slider").hover(function(e){
				jQuery('.fme-primary-arrows').css('display','block');
			}, function(e){
				jQuery('.fme-primary-arrows').css('display','none');
			});


			jQuery("#secondary-slider").hover(function(){
				jQuery('.secondary-class-arrows').css('display','block');
			}, function(){
				jQuery('.secondary-class-arrows').css('display','none');
			});


		} else {
			jQuery('.fme-primary-arrows').css('display','block');
			jQuery('.secondary-class-arrows').css('display','block');
		}



		jQuery('.splide__arrow').hover(function(){
			jQuery(this).css('background',arrow_hover_color);
		}, function(){
			jQuery(this).css('background',navigation_background_color);
		});	

		if (arrow_shape == 'square') {
			jQuery('.splide__arrow').css('border-radius', '0%');
			jQuery('.fme-primary-arrows').css('border-radius', '0%');
		} else if (arrow_shape == 'rounded') {
			jQuery('.splide__arrow').css('border-radius', '50%');
			jQuery('.fme-primary-arrows').css('border-radius', '50%');
		}
		if(bullet_enabled == 'yes') {

			if(bullet_shape == 'square') {
				jQuery('.splide__pagination__page').css('border-radius', 0);
			} else if(bullet_shape == 'lines') {
				jQuery('.splide__pagination__page').css('border-radius', 20);
				jQuery('.splide__pagination__page').css('width', 17);
				jQuery('.splide__pagination__page').css('height', 5);
			} else if (bullet_shape == 'counter_bullets') {
				// jQuery('.splide__pagination').css('counter-reset', 'pagination-num');
				// jQuery('.splide__pagination__page:before').css({'counter-increment':'pagination-num','content':'counter( pagination-num )'});
				jQuery('.splide__pagination').addClass('splide__pagination_fme');
				jQuery('.splide__pagination__page').toggleClass('splide__pagination__page_fme')
				jQuery('.splide__pagination__page').css({"width":"30px","height":"25px","border-radius":"0","opacity":'1 !important','line-height':'1.5px', 'color':''+bullet_counter_font_color+''})
			} else if (bullet_shape== 'bar_counter_bullets') {
				
				jQuery('.splide__pagination').addClass('splide__pagination_fme');
				jQuery('.splide__pagination__page').toggleClass('splide__pagination__page_fme');
				jQuery('.splide__pagination__page').css({"width":"30px","height":"3px","border-radius":"0","opacity":'1 !important','margin':'0px', 'color':''+bullet_counter_font_color+''})
				// jQuery('.splide__pagination__page.is-active').css({"jQuery('.splide__pagination__page.is-active').addClasstransform":"scale(1.1) !important"});
				jQuery('.splide__pagination__page.is-active').addClass('splide__pagination__page_fme_active');
			} else if (bullet_shape== 'bottom_bar'){
				jQuery('.splide__pagination').addClass('splide__pagination_fme');
				jQuery('.splide__pagination__page').removeClass('splide__pagination__page_fme');
				jQuery('.splide__pagination__page').css({"width":"30px","height":"3px","border-radius":"0","opacity":'1 !important','margin':'0px', 'color':''+bullet_counter_font_color+''})
				// jQuery('.splide__pagination__page.is-active').css({"jQuery('.splide__pagination__page.is-active').addClasstransform":"scale(1.1) !important"});
				jQuery('.splide__pagination__page.is-active').addClass('splide__pagination__page_fme_active');
			}

			jQuery('.splide__pagination__page').css('background', bullet_bg_color);
			jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
			jQuery('.splide__pagination__page').hover(function(){
				jQuery(this).css('background', bullet_hover_color);
				jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
			}, function(){
				jQuery(this).css('background', bullet_bg_color);
				jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
			});

			jQuery('.splide__pagination__page').css('min-height', 'unset');
			if (slider_layout == 'horizontal' && hide_thumbnails == 'no') {
				// jQuery('.splide__pagination').css('margin-bottom', jQuery('#secondary-slider-list').height()+19);

				if(bullets_position == 'inside_image') {
					if (bullets_inside_position == 'bottom_left') {
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_left');
						jQuery('.splide__pagination').css('margin-bottom', jQuery('#secondary-slider-list').height()+19);
					} else if (bullets_inside_position == 'bottom_right') {
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_right');
						jQuery('.splide__pagination').css('margin-bottom', jQuery('#secondary-slider-list').height()+19);
					} else if (bullets_inside_position == 'top_left') {
						jQuery('.splide__pagination').addClass('splide__pagination_top_left');
					} else if (bullets_inside_position == 'top_center') {
						jQuery('.splide__pagination').addClass('splide__pagination_top_center');
					} else if (bullets_inside_position == 'top_right') {
						jQuery('.splide__pagination').addClass('splide__pagination_top_right');
					} else {
						jQuery('.splide__pagination').css('margin-bottom', jQuery('#secondary-slider-list').height()+19);
					}
				}


			} else if( slider_layout == 'left' && hide_thumbnails == 'no' ) {
				jQuery('.splide__pagination').css('width', jQuery('.fme_small').width());
				// jQuery('.splide__pagination').css('left', '59%');

				if(bullets_position == 'inside_image') {
					if (bullets_inside_position == 'bottom_left') {
						jQuery('.splide__pagination').css('margin-left', '82px');
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_left');
					} else if (bullets_inside_position == 'bottom_right') {
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_right');
					} else if (bullets_inside_position == 'top_left') {
						jQuery('.splide__pagination').css('margin-left', '82px');
						jQuery('.splide__pagination').addClass('splide__pagination_top_left');
					} else if (bullets_inside_position == 'top_center') {
						jQuery('.splide__pagination').css('left', '59%');
						jQuery('.splide__pagination').addClass('splide__pagination_top_center');
					} else if (bullets_inside_position == 'top_right') {
						jQuery('.splide__pagination').addClass('splide__pagination_top_right');
					} else {
						jQuery('.splide__pagination').css('left', '59%');	
					}

				}


			} else if( slider_layout == 'right' && hide_thumbnails == 'no' ) {
				jQuery('.splide__pagination').css('width', jQuery('.fme_small').width());

				if(bullets_position == 'inside_image') {
					if (bullets_inside_position == 'bottom_left') {
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_left');
					} else if (bullets_inside_position == 'bottom_right') {
						jQuery('.splide__pagination').css('margin-right', '82px');
						jQuery('.splide__pagination').addClass('splide__pagination_bottom_right');
					} else if (bullets_inside_position == 'top_left') {
						jQuery('.splide__pagination').addClass('splide__pagination_top_left');
					} else if (bullets_inside_position == 'top_center') {
						jQuery('.splide__pagination').css('left', '41%');
						jQuery('.splide__pagination').addClass('splide__pagination_top_center');
					} else if (bullets_inside_position == 'top_right') {
						jQuery('.splide__pagination').css('margin-right', '82px');
						jQuery('.splide__pagination').addClass('splide__pagination_top_right');
					} else {
						jQuery('.splide__pagination').css('left', '41%');	
					}

				}

			}
			

			jQuery('body').on('click','.splide__pagination__page', function() {
				jQuery('.splide__pagination__page').css('background', bullet_bg_color);
			});

			jQuery('body').on('click','.splide__arrow', function() {
				jQuery('.splide__pagination__page').css('background', bullet_bg_color);
				jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);
			});
			jQuery('.splide__pagination__page').css('padding', '0');
		}

		jQuery('.splide__arrow').css('position', 'absolute');
		jQuery('.splide__arrow').css('display', 'flex');
		jQuery('.splide__arrow').css('padding', '0');



		if(hide_thumbnails == 'yes') {
			if ( bullets_position == 'bellow_image' ) {
				jQuery('.splide__pagination').css('margin-bottom', '-30px');
				jQuery('.splide__pagination').css('bottom', '0em');


				if(bullet_shape == 'lines') {
					jQuery('.splide__pagination__page').css('width', 20);
					jQuery('.splide__pagination__page').css('height', 6);
				} else {
					jQuery('.splide__pagination__page').css('width', 10);
					jQuery('.splide__pagination__page').css('height', 10);
					jQuery('.splide__pagination__page').css('margin', 5);
				}
			} else if ( bullets_position == 'inside_image' ) {
				if (bullets_inside_position == 'bottom_center') {
					jQuery('.splide__pagination').css('margin-bottom', '0em');
				}
			}  
		}

		jQuery('.splide__pagination__page').mouseover(function(){
			jQuery('#'+jQuery(this).attr('aria-controls')).find('.fme_pgifw_image_url_check').attr('src');
		});	

		if(bullets_thumbnail == 'yes') {
			jQuery('.splide__pagination__page').each(function(){

				var pop_up_bg_color = bullet_bg_color.slice(0,7);
				var images_links = jQuery('#'+jQuery(this).attr('aria-controls')).find('.fme_pgifw_image_url_check').attr('src');
				
				var last_5_char = images_links.slice(images_links.length - 5);

				last_5_char = last_5_char.toLowerCase();

				if(last_5_char.indexOf('.mp4') > -1 || last_5_char.indexOf('.ogg') > -1 || last_5_char.indexOf('.webm') > -1) {
					jQuery(this).parent().append('<video class="fme_hover_image_popup" style="margin-left:-70px !important; background-color:'+pop_up_bg_color+'"> <source src="'+images_links+'"></video>');

				} else if(images_links.indexOf('www.youtube.com/embed') > -1) {
					
					images_links = images_links.replace('https://www.youtube.com/embed/','');
					images_links = 'http://img.youtube.com/vi/' + images_links + '/default.jpg';
					
					jQuery(this).parent().append('<img class="fme_hover_image_popup" style="background-color:'+pop_up_bg_color+';" src="'+images_links+'">');

				} else {
					jQuery(this).parent().append('<img class="fme_hover_image_popup" style="background-color:'+pop_up_bg_color+';" src="'+images_links+'">');
				}
			});
		}

		jQuery('.splide__pagination__page').hover(function(){

			jQuery(this).parent().find('.fme_hover_image_popup').css('visibility', 'visible');
			jQuery(this).parent().find('.fme_hover_image_popup').css('opacity', '1');
		}, function(){
			jQuery(this).parent().find('.fme_hover_image_popup').css('visibility', 'hidden');
			jQuery(this).parent().find('.fme_hover_image_popup').css('opacity', '0');
		});

		if(bullets_position == 'inside_image') {
			if (bullets_inside_position == 'bottom_left') {
				jQuery('.splide__pagination').addClass('splide__pagination_bottom_left');
			} else if (bullets_inside_position == 'bottom_right') {
				jQuery('.splide__pagination').addClass('splide__pagination_bottom_right');
			} else if (bullets_inside_position == 'top_left') {
				jQuery('.splide__pagination').addClass('splide__pagination_top_left');
			} else if (bullets_inside_position == 'top_center') {
				jQuery('.splide__pagination').addClass('splide__pagination_top_center');
			} else if (bullets_inside_position == 'top_right') {
				jQuery('.splide__pagination').addClass('splide__pagination_top_right');
			}

		}

	}, 200);

jQuery('.fme_lightbox_icon').css('background', lightbox_bg_color);

jQuery(".fme_lightbox_icon").hover(function(){
	jQuery(this).css('background', lightbox_bg_hover_color);
}, function(){
	jQuery(this).css('background', lightbox_bg_color);
});

jQuery('.fme_small').hover(function(){
	if(show_zoom == 'yes') {
		jQuery(this).blowup({
			"width" :fme_pgisfw_settings_data.fme_pgisfw_zoombox_frame_width,
			"height" :fme_pgisfw_settings_data.fme_pgisfw_zoombox_frame_height,
			round      : false,
			cursor     : true,
			scale      : 1,
			display      : 'block',
		});
	}
}, function(){
	if(show_zoom == 'yes') {
		jQuery(this).blowup({
			"width" :fme_pgisfw_settings_data.fme_pgisfw_zoombox_frame_width,
			"height" :fme_pgisfw_settings_data.fme_pgisfw_zoombox_frame_height,
			round      : false,
			cursor     : true,
			scale      : 1,
			display      : 'none',
		});
	}
});

jQuery('.fme_lightbox_icon').click(function () {
	myGallery.settings.startAt = jQuery(this).attr('index');
	myGallery.open();
	jQuery('.ginner-container').find('img').css('width', lightbox_frame_width+'px');
});
jQuery(".fme_lightbox_icon").hover(function() {
	jQuery('.fme_lightbox_icon').css('cursor','pointer');
}, function(){
	jQuery('.fme_lightbox_icon').css('cursor','default');
});


var is_pagination = false;
if(bullet_enabled == 'yes') {
	is_pagination = true;
}


if(fme_pgisfw_settings_data.fme_pgisfw_image_aspect_ratio == 'on'){
			
			jQuery('.fme_small').css('object-fit','contain');

			}

if(slider_layout == 'horizontal' || hide_thumbnails == 'yes') {
	
	var secondary_slider = new Splide('.splide', {
		gap: 2,
		pagination : false,
		// type : 'loop',
		type : fme_pgisfw_slider_mode,
		cover: true,
		focus: 'center',
		speed: 2,
		isNavigation: true,
		autoplay: auto_play,
		perPage: thumbnails_to_show,
		interval: auto_play_timeout,
		heightRatio: 0.2,
		trimSpace:true,
		pauseOnHover: true,
		rewind: true,
		classes: {
			arrows: 'splide__arrows secondary-class-arrows',
		}
	});

	var primary_slider = new Splide( '.primary-slider', {
		type       : 'fade',
		fixedWidth : width,
		speed: 1000,
		rewind : true,
		fixedHeight : height,
		pagination : is_pagination,
		autoplay: auto_play,
		interval: auto_play_timeout,
		pauseOnHover: true,
		pauseOnFocus: true,
		
		focus: 'center',
		drag: true,
		arrows: navigation_icon,
		'arrowPath': arrows_paths[curr_select_arrow],
		cover: true,
		classes: {
			arrows: 'splide__arrows fme-primary-arrows',
			arrow : 'splide__arrow fme-primary-arrow',
			prev  : 'splide__arrow--prev fme-primary-prev',
			next  : 'splide__arrow--next fme-primary-next',
		}
	}).mount();

	if(hide_thumbnails == 'no' ) {
		jQuery('.primary-slider').css('padding-bottom', '15px');
	}
} else {
			
	var secondary_slider = new Splide('.splide', {
		fixedWidth: 70,
		type : fme_pgisfw_slider_mode,
		gap: 2,
		speed: 2,
		pagination : false,
		height: width,
		cover: true,
		focus: 'center',
		isNavigation: true,
		autoplay: auto_play,
		perPage: thumbnails_to_show,
		interval: auto_play_timeout,
		direction: 'ttb',
		pauseOnHover: true,
		'arrowPath': arrows_paths[curr_select_arrow],
		arrows : navigation_icon,
		classes: {
			arrows: 'splide__arrows secondary-class-arrows',
		}
	});

	var primary_slider = new Splide( '.primary-slider', {
		type       : 'fade',
		fixedWidth : width-75,
		fixedHeight : height,
		height: width,
		rewind : true,
		speed: 1000,
		arrows     : navigation_icon,
		transition : 'transform 800ms cubic-bezier(.44,.65,.07,1.01)',
		autoplay: auto_play,
		pauseOnHover: true,
		pauseOnFocus: true,
		pagination : is_pagination,
		cover : true,
		'arrowPath': arrows_paths[curr_select_arrow],
		classes: {
			arrows: 'splide__arrows fme-primary-arrows',
			arrow : 'splide__arrow fme-primary-arrow',
			prev  : 'splide__arrow--prev fme-primary-prev',
			next  : 'splide__arrow--next fme-primary-next',
		}
	}).mount();

	if(hide_thumbnails == 'no' && slider_layout == 'left') {

		jQuery('.primary-slider').css('padding-left', '5px');
		setTimeout(function(){
			var img_width = jQuery('.fme_small').width();
			jQuery('.fme-primary-next').css('left', (parseInt(img_width)+40)+'px');	
		},300);
	}

	if(hide_thumbnails == 'no' && slider_layout == 'right') {
		jQuery('.primary-slider').css('padding-right', '5px');
	}
jQuery('.tc_video_slide_primary').css('width',width-75);
}

primary_slider.sync( secondary_slider );
secondary_slider.sync(primary_slider).mount();


primary_slider.on('active', function(elementInfo) {
	
	jQuery('.splide__pagination__page').css('background', bullet_bg_color);
	jQuery('.splide__pagination__page.is-active').css('background', bullet_hover_color);

});

if( 'style2' == fme_pgisfw_settings_data.fme_pgisfw_thumbnail_images_style){
secondary_slider.on('active', function(elementInfo) {

	let element = elementInfo.slide

	jQuery(element).parent().children().css('transform', 'scale(1.1)');

	jQuery(element).next().css('transform', 'scale(0.8,0.8)');
	jQuery(element).prev().css('transform', 'scale(0.8,0.8)');


	jQuery(element).prev().prev().css('transform', 'scale(0.6,0.6)');
	jQuery(element).next().next().css('transform', 'scale(0.6,0.6)');

	jQuery(element).prev().prev().prev().css('transform', 'scale(0.4,0.4)');
	jQuery(element).next().next().next().css('transform', 'scale(0.4,0.4)');

});

}
});

setTimeout( function(){

if(jQuery('.secondary-class-arrows').find('.splide__arrow').attr('disabled')){
jQuery('.secondary-class-arrows').find('.splide__arrow').removeAttr('disabled');
jQuery('body').find('.splide__arrow').click( function() {

   jQuery('.secondary-class-arrows').find('.splide__arrow').removeAttr('disabled');
});
}
} ,1000);

function fme_pgifw_stop_video () {
	jQuery('.fme_video_size').each(function(){

		if (jQuery(this).is('iframe')) {
			this.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*')
		}
		if (jQuery(this).is('video')) {
			jQuery(this).get(0).pause();
		}

	});
}