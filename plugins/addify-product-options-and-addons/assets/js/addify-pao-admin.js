jQuery( document ).ready(
	function($){

		var ajaxurl = addify_product_add_ons.admin_url;
		var nonce   = addify_product_add_ons.nonce;

		jQuery( '.my-color-field' ).wpColorPicker();

		jQuery( '.af_pao_tag' ).select2({
			multiple: true,
			minimumInputLength: 3,
		});

		jQuery( '.af_addon_option_depend_selector' ).select2({
			multiple: true,
			placeholder: 'Select Option',
		});

		jQuery( '.af_addon_user_role' ).select2({
			multiple: true,
		});

		jQuery( '.af_pao_cat_search_class' ).select2(
		{
			ajax: {

					url: ajaxurl, // AJAX URL is predefined in WordPress admin.
					
					dataType: 'json',
					
					type: 'POST',
					
					delay: 20, // Delay in ms while typing when to perform a AJAX search.
					
					data: function (params) {

						return {

							q: params.term, // search query.

							action: 'af_pao_cat_ajax', // AJAX action for admin-ajax.php.//aftaxsearchUsers(is function name which isused in adminn file).

							nonce: nonce // AJAX nonce for admin-ajax.php.

						};

					},
					
					processResults: function ( data ) {

						var options = [];

						if (data ) { // data is the array of arrays, and each of them contains ID and the Label of the option.

							$.each(

								data,

								function ( index, text ) {

									// do not forget that "index" is just auto incremented value.
									options.push( { id: text[0], text: text[1]  } );

								}
								);
						}

						return {

							results: options
						};
					},
					
					cache: true
				},

				multiple: true,
				
				minimumInputLength: 3 // the minimum of symbols to input before perform a search.
			}
			);

		jQuery( '.af_pao_prod_search_class' ).select2(
		{
			ajax: {

					url: ajaxurl, // AJAX URL is predefined in WordPress admin.
					
					dataType: 'json',
					
					type: 'POST',
					
					delay: 20, // Delay in ms while typing when to perform a AJAX search.
					
					data: function (params) {

						return {

							q: params.term, // search query.

							action: 'af_pao_prod_ajax', // AJAX action for admin-ajax.php.//aftaxsearchUsers(is function name which isused in adminn file).

							nonce: nonce // AJAX nonce for admin-ajax.php.

						};
					},
					
					processResults: function ( data ) {

						var options = [];

						if (data ) { // data is the array of arrays, and each of them contains ID and the Label of the option.

							$.each(

								data,

								function ( index, text ) {

									// do not forget that "index" is just auto incremented value.
									options.push( { id: text[0], text: text[1]  } );
								}
								);
						}

						return {

							results: options
						};
					},
					
					cache: true
				},
				
				multiple: true,
				
				minimumInputLength: 3 // the minimum of symbols to input before perform a search.
			}
		);

		$( document ).on( 
			'click', 
			".af_addon_import_button", 
			function (e){ 
				e.preventDefault(); 
				$( '.import_file_div' ).css( 'display', 'revert' );
			} 
		);

		$( document ).on( 'change', ".af_addon_depend_selector", function (){ af_addon_depend_selector(); } );

		$( document ).on( 'click', ".af_addon_limit_range_checkbox", function (){ af_addon_limit_range_checkbox(); } );

		$( document ).on( 'click', ".af_addon_price_range_checkbox", function (){ af_addon_price_range_checkbox(); } );

		desctextarea();

		$( document ).on( 'click', ".af_addon_desc_checkbox", function (){ desctextarea(); } );

		tooltiptextarea();

		$( document ).on( 'click', ".af_addon_tooltip_checkbox", function (){ tooltiptextarea(); } );

		af_addon_seperator_checkbox_class();

		$( document ).on( 'click', ".af_addon_seperator_checkbox_class", function (){ af_addon_seperator_checkbox_class(); } );

		function af_addon_seperator_checkbox_class(){

			if ($( '.af_addon_seperator_checkbox_class' ).prop( 'checked' ) == true) {

				$( '.af_addon_seperator_selector_div_id' ).fadeIn( 'fast' );

			} else {

				$( '.af_addon_seperator_selector_div_id' ).fadeOut( 'fast' );
			}

		}

		af_addon_title_display_as_selector();

		$( document ).on( 'change', ".af_addon_title_display_as_selector", function (){ af_addon_title_display_as_selector(); } );

		function af_addon_title_display_as_selector(){

			var selector_val = $( '.af_addon_title_display_as_selector' ).val();

			if (selector_val == "af_addon_title_display_heading") {

				$( '.af_addon_heading_type_selector_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_title_font_size_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_color_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_title_bg_checkbox_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_add_Seperator_div_id' ).fadeIn( 'fast' );

			} else if (selector_val == "af_addon_title_display_text") {

				$( '.af_addon_heading_type_selector_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_font_size_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_title_color_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_title_bg_checkbox_div_id' ).fadeIn( 'fast' );

				$( '.af_addon_add_Seperator_div_id' ).fadeIn( 'fast' );

			} else {

				$( '.af_addon_heading_type_selector_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_font_size_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_color_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_bg_checkbox_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_add_Seperator_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_seperator_selector_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_seperator_selector_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_bg_color_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_bg_radius_div_id' ).fadeOut( 'fast' );

				$( '.af_addon_title_bg_padding_div_id' ).fadeOut( 'fast' );
			}
		}

		af_addon_title_bg_class();

		$( document ).on( 'click', ".af_addon_title_bg_class", function(){ af_addon_title_bg_class() }  );

		function af_addon_title_bg_class(){

			$('.af_addon_title_bg_class').each(function(){

				if ( $( this ).is( ':checked' ) ) {

					$(this).closest('.af_addon_field_div').find( '.af_addon_title_bg_color_div_id , .af_addon_title_bg_radius_div_id  , .af_addon_title_bg_padding_div_id' ).fadeIn( 'fast' ); 

				}else{

					$(this).closest('.af_addon_field_div').find( '.af_addon_title_bg_color_div_id, .af_addon_title_bg_radius_div_id , .af_addon_title_bg_padding_div_id' ).fadeOut( 'fast' ); 

				}
			});
		}

		af_addon_field_border_class();

		$( document ).on( 'click', ".af_addon_field_border_class", function (){ af_addon_field_border_class(); } );

		function af_addon_field_border_class(){

			$('.af_addon_field_border_class').each(function(){

				if ( $( this ).is( ':checked' ) ) {

					$(this).closest('.af_addon_field_div').find( '.af_addon_field_border_color_div_id , .af_addon_title_position_div_id  , .af_addon_field_border_pixels_div_id , .af_addon_field_border_radius_div_id , .af_addon_field_border_padding_div_id' ).fadeIn( 'fast' ); 

				}else{

					$(this).closest('.af_addon_field_div').find( '.af_addon_field_border_color_div_id , .af_addon_title_position_div_id  , .af_addon_field_border_pixels_div_id , .af_addon_field_border_radius_div_id , .af_addon_field_border_padding_div_id' ).fadeOut( 'fast' ); 

				}
			});
		}

		$( document ).on( 'change', ".af_addon_field_type_selector", function (){ af_addon_field_type_selector(); } );

		function af_addon_field_type_selector(){


			$('.af_addon_field_type_selector').each(function(){

					var field_id = $( this ).data( 'current_field_id' );

				if ( $(this).closest('.af_addon_field_div').find('button.fa-sort-up').is(':visible') ) {

					$( '.' + field_id + '_af_addon_dependable_div' ).fadeIn();

					var value1 = $( '.' + field_id + '_af_addon_field_type_selector' ).children( "option:selected" ).val();

					af_addon_hide_field_on_type_selected( $, value1, field_id );

				}else {
					
					$( '.' + field_id + '_af_addon_dependable_div' ).hide();

				}

			});

		}

		function af_addon_hide_field_on_type_selected($, value1, field_id){

			if (value1 == "drop_down") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).hide();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

				$( '.' + field_id + '_af_addon_option_name_div' ).css( 'width','100%' );

			} else if (value1 == "multi_select") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).hide();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

				$( '.' + field_id + '_af_addon_option_name_div' ).css( 'width','100%' );

			} else if (value1 == "check_boxes") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).hide();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

				$( '.' + field_id + '_af_addon_option_name_div' ).css( 'width','100%' );

			} else if (value1 == "input_text") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).show();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_limit_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_limit_range_divs" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_limit_range_divs" + field_id ).fadeOut( 'fast' );

				}

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "textarea") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).show();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_limit_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_limit_range_divs" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_limit_range_divs" + field_id ).fadeOut( 'fast' );

				}

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "file_upload") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).show();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "number") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).show();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_limit_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_limit_range_divs" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_limit_range_divs" + field_id ).fadeOut( 'fast' );

				}

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "radio") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).hide();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

				$( '.' + field_id + '_af_addon_option_name_div' ).css( 'width','100%' );

			} else if (value1 == "color_swatcher") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).hide();

				$( '.' + field_id + '_af_addon_option_color_div' ).show();

			} else if (value1 == "image_swatcher") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).show();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

				$( '.' + field_id + '_af_addon_option_name_div' ).css( 'width','83%' );

			} else if (value1 == "image") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).hide();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).show();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();

				$( '.' + field_id + '_af_addon_image_div' ).show();

				$( '.' + field_id + '_af_addon_option_color_div' ).hide();

			} else if (value1 == "date_picker") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'slow' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'slow' );

				}

			} else if (value1 == "email") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "password") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).show();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_limit_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_limit_range_divs" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_limit_range_divs" + field_id ).fadeOut( 'fast' );

				}

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else if (value1 == "time_picker") {

				$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );

				}

			} else {

				$( '.' + field_id + '_af_addon_limit_range_div' ).show();

				$( '.' + field_id + '_af_addon_price_range_div' ).show();

				$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

				$( '.' + field_id + '_af_addon_option_table_div' ).hide();

				$( '.' + field_id + '_af_addon_add_optn_btn_div' ).hide();

				if ($( '.af_addon_limit_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_limit_range_divs" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_limit_range_divs" + field_id ).fadeOut( 'fast' );

				}

				if ($( '.af_addon_price_range_checkbox' + field_id ).prop( 'checked' ) == true) {

					$( ".af_addon_type_price_div" + field_id ).fadeIn( 'fast' );

				} else {

					$( ".af_addon_type_price_div" + field_id ).fadeOut( 'fast' );
				}
			}
		}

		af_addon_depend_selector();

		function af_addon_depend_selector(){

			var field_id = $( '.af_addon_depend_selector' ).data( 'current_field_id' );

			var value1 = $( '.' + field_id + '_af_addon_depend_selector' ).children( "option:selected" ).val();

			if (value1 == "af_addon_dependable") {

				$( '.' + field_id + '_af_addon_field_depend_selector_div' ).fadeIn( 'fast' );

				$( '.' + field_id + '_af_addon_option_depend_selector_div' ).fadeIn( 'fast' );

			} else {

				$( '.' + field_id + '_af_addon_field_depend_selector_div' ).fadeOut( 'fast' );

				$( '.' + field_id + '_af_addon_option_depend_selector_div' ).fadeOut( 'fast' );

			}
		}

		function af_addon_limit_range_checkbox(){

			$( '.af_addon_limit_range_checkbox' ).each(
				
				function (){

					var id = $( this ).data( 'current_field_id' );

					if ($( '.af_addon_limit_range_checkbox' + id ).prop( 'checked' ) == true) {

						$( ".af_addon_limit_range_divs" + id ).fadeIn( 'fast' );

					} else {

						$( ".af_addon_limit_range_divs" + id ).fadeOut( 'fast' );
					}

				}
				);
		}

		function af_addon_price_range_checkbox(){

			$( '.af_addon_price_range_checkbox' ).each(
				
				function (){

					var id = $( this ).data( 'current_field_id' );

					if ($( '.af_addon_price_range_checkbox' + id ).prop( 'checked' ) == true) {

						$( ".af_addon_type_price_div" + id ).fadeIn( 'fast' );

					} else {

						$( ".af_addon_type_price_div" + id ).fadeOut( 'fast' );
					}

				}
				);
		}

		function desctextarea(){

			jQuery('.af_addon_desc_checkbox ').each(function(){

				if ($(this).is(':checked')) {

					$(this).closest('.af_addon_desc_div').find('.desc_text_area').fadeIn( 'fast' );

				} else {

					$(this).closest('.af_addon_desc_div').find('.desc_text_area').fadeOut( 'fast' );

				}

			});
		}

		function tooltiptextarea(){

			jQuery('.af_addon_tooltip_checkbox').each(function(){

				if ($(this).is(':checked')) {

					$(this).closest('.af_addon_tooltip_div').find('.tooltip_text_area').fadeIn( 'fast' );

				} else {

					$(this).closest('.af_addon_tooltip_div').find('.tooltip_text_area').fadeOut( 'fast' );

				}

			});
		}

		$( document ).on(
			
			'click',
			
			'.af_addon_add_image_btn',
			
			function(e){

				e.preventDefault();

				var ajaxurl = addify_product_add_ons.admin_url;

				var id = $( this ).closest( "tr.option_tr" ).find( "input[name='af_hidden_id']" ).val();

				var fid = $( this ).closest( "tr.option_tr" ).find( "input[name='af_field_id']" ).val();

				"use strict";

				var image = wp.media(
				{
					title: 'Upload Image',

					multiple: false
				}
				).open()
				.on(
					'select',
					
					function(){

						// This will return the selected image from the Media Uploaded, the result is an object.
						var uploaded_image = image.state().get( 'selection' ).first();

						// We convert uploaded_image to a JSON object to make accessing it easier.
						// Output to the console uploaded_image.
						var image_url = uploaded_image.toJSON().url;

						// Let's assign the url value to the input field.
						jQuery( '#' + fid + 'af_addon_image_upload' + id ).val( image_url );

						jQuery( '.' + fid + 'af_addon_option_image' + id ).attr( "src", image_url );

						jQuery( '.' + fid + '_af_addon_add_image_btn_' + id ).css( 'display','none' );

						jQuery( '#remove_option_image' + id ).css( 'display','revert' );

						jQuery( '.' + fid + 'af_addon_option_image' + id ).show();

						$( '.af_addon_field_options_image[' + fid + '][' + id + ']' ).val( image_url );
					}
					);
			}
			)

		$( document ).on(
			
			'click',
			'.remove_option_image' ,
			
			function (){

				var id = $( this ).closest( "tr.option_tr" ).find( "input[name='af_hidden_id']" ).val();

				var fid = $( this ).closest( "tr.option_tr" ).find( "input[name='af_field_id']" ).val();

				jQuery( '#' + fid + 'af_addon_image_upload' + id ).val( '' );

				jQuery( '.' + fid + 'af_addon_option_image' + id ).attr( 'src', '' );

				jQuery( '.' + fid + 'af_addon_option_image' + id ).css( 'display', 'none' );

				jQuery( '.' + fid + '_af_addon_add_image_btn_' + id ).css( 'display','revert' );

				jQuery( '#remove_option_image' + id ).css( 'display','none' );
			}
			);

		$( document ).on(
			'click',
			'.af_addon_expand_all_btn',
			
			function (e){

				e.preventDefault();

				$( '.fa-sort-down' ).hide();

				$( '.fa-sort-up' ).show();

				$( '.af_addon_dependable_div' ).slideDown( 'slow' );

				$( '.af_addon_type_and_title_div' ).slideDown( 'slow' );

				$( '.af_addon_desc_div' ).slideDown( 'slow' );

				$( '.af_addon_tooltip_div' ).slideDown( 'slow' );

				$( '.af_addon_req_div' ).slideDown( 'slow' );

				// $( '.af_addon_field_type_selector' ).each(

				// 	function(){

				// 		var field_id = $( this ).data( 'current_field_id' );

				// 		var value1 = $( '.' + field_id + '_af_addon_field_type_selector' ).children( "option:selected" ).val();

				// 		af_addon_hide_field_on_type_selected( $, value1, field_id );

				// 	}
				// 	);
				af_addon_field_type_selector();
			}
		);

		$( document ).on(
			'click',
			'.af_addon_close_all_btn',
			
			function (e){

				e.preventDefault();

				$( '.fa-sort-down' ).show();

				$( '.fa-sort-up' ).hide();

				$( '.af_addon_dependable_div' ).slideUp( 'slow' );

				$( '.af_addon_type_and_title_div' ).slideUp( 'slow' );

				$( '.af_addon_desc_div' ).slideUp( 'slow' );

				$( '.af_addon_tooltip_div' ).slideUp( 'slow' );

				$( '.af_addon_req_div' ).slideUp( 'slow' );

				$( '.af_addon_limit_range_div' ).slideUp( 'slow' );

				$( '.af_addon_price_range_div' ).slideUp( 'slow' );

				$( '.af_addon_file_extention_div' ).slideUp( 'slow' );

				$( '.af_addon_option_table_div' ).slideUp( 'slow' );

				$( '.af_addon_add_optn_btn_div' ).slideUp( 'slow' );
			}
		);

		var fid = '';

		close_field_div( $, fid );

		$( document ).on( 'click', ".fa-sort-up", function (e){ e.preventDefault(); var fid = $( this ).data( 'field_id' ); close_field_div( $, fid ); } );

		function close_field_div( $, fid ){

			if ( $( '.' + fid + 'fa-sort-up' ).data( 'clicked', true )) {

				$( '.' + fid + 'fa-sort-down' ).show();

				$( '.' + fid + 'fa-sort-up' ).hide();

				$( '.' + fid + '_af_addon_dependable_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_type_and_title_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_desc_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_tooltip_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_req_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_limit_range_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_price_range_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_option_table_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_add_optn_btn_div' ).slideUp( 'slow' );

				$( '.' + fid + '_af_addon_file_extention_div' ).slideUp( 'slow' );

			}
		}

		$( document ).on(
			
			'click',
			
			".style_up",
			
			function (e){

				e.preventDefault();

				if ( $( this ).data( 'clicked', true ) ) {

					$( '.style_down' ).show();

					$( '.style_up' ).hide();

					$( '.prod_style_tab_main_class' ).slideUp( 'slow' );
				}
			}
		);

		$( document ).ready(
			function(){

				af_addon_field_type_selector();

				af_addon_depend_selector();

				af_addon_limit_range_checkbox();

				af_addon_price_range_checkbox();

				$( "tr.option_tr" ).each(
					
					function(){

						var id = $( this ).data( "option_id_value" );

						var fid = $( this ).data( "field_id_value" );

						var img_src = jQuery( '.' + fid + 'af_addon_option_image' + id ).attr( 'src' );

						if (img_src) {

							jQuery( '.' + fid + '_af_addon_add_image_btn_' + id ).css( 'display','none' );

						} else {

							jQuery( '#remove_option_image' + id ).css( 'display','none' );

							jQuery( '.' + fid + 'af_addon_option_image' + id ).hide();
						}
					}
				)

				$( '.style_down' ).show();

				$( '.style_up' ).hide();

				$( '.prod_style_tab_main_class' ).hide();

				$( '.fa-sort-down' ).show();

				$( '.fa-sort-up' ).hide();

				$( '.af_addon_dependable_div' ).hide();

				$( '.af_addon_type_and_title_div' ).hide();

				$( '.af_addon_desc_div' ).hide();

				$( '.af_addon_tooltip_div' ).hide();

				$( '.af_addon_req_div' ).hide();

				$( '.af_addon_limit_range_div' ).hide();

				$( '.af_addon_price_range_div' ).hide();

				$( '.af_addon_file_extention_div' ).hide();

				$( '.af_addon_option_table_div' ).hide();

				$( '.af_addon_add_optn_btn_div' ).hide();
			}
			);

		var fid = '';

		close_field_div( $, fid );

		$( document ).on(
			'click',
			".style_down",
			
			function (e){

				e.preventDefault();

				if ($( this ).data( 'clicked', true )) {

					$( '.style_down' ).hide();

					$( '.style_up' ).show();

					$( '.prod_style_tab_main_class' ).slideDown( 'slow' );
				}

			}
		);

		open_field_div( $,fid );

		$( document ).on(
			'click',
			".fa-sort-down",
			
			function (e){

				e.preventDefault();

				var fid = $( this ).data( 'field_id' );

				open_field_div( $, fid );
			}
		);

		function open_field_div( $, fid ){

			if ($( '.' + fid + 'fa-sort-down' ).data( 'clicked', true )) {

				$( '.' + fid + 'fa-sort-down' ).hide();

				$( '.' + fid + 'fa-sort-up' ).show();

				$( '.' + fid + '_af_addon_type_and_title_div' ).slideDown( 'slow' );

				$( '.' + fid + '_af_addon_dependable_div' ).slideDown( 'slow' );

				$( '.' + fid + '_af_addon_desc_div' ).slideDown( 'slow' );

				$( '.' + fid + '_af_addon_tooltip_div' ).slideDown( 'slow' );

				$( '.' + fid + '_af_addon_req_div' ).slideDown( 'slow' );

				var value1 = $( '.' + fid + '_af_addon_field_type_selector' ).children( "option:selected" ).val();

				af_addon_hide_field_on_type_selected( $, value1, fid );
			}
		}

		$( document ).on(
			'click',
			'.af_addon_add_option_btn',
			
			function (e){

				e.preventDefault();

				var current_field_id = $( this ).data( 'current_field_id' );

				var current_rule_id = $( this ).data( 'current_rule_id' );

				jQuery.ajax(
				{

					url: ajaxurl,

					type: 'POST',

					data: {

						current_rule_id   : current_rule_id,

						current_field_id  : current_field_id,

						nonce 			  : nonce,

						add_file_with     : $( this ).data( 'add_file_with' ),

						action            : 'af_pao_add_option',

					},

					success: function(data){

						$( '.' + current_field_id + '_af_addon_option_table' ).append( data );

						$( '.fa-sort-down' ).hide();

						// $( '.af_addon_field_type_selector' ).each(
						// 	function(){

						// 		var field_id = $( this ).data( 'current_field_id' );

						// 		var value1 = $( '.' + field_id + '_af_addon_field_type_selector' ).children( "option:selected" ).val();

						// 		af_addon_hide_field_on_type_selected( $, value1, field_id );

						// 	}
						// 	);

						af_addon_field_type_selector();
						
						$( '.my-color-field' ).wpColorPicker();
					}
				}
				);
			}
		);

		$( document ).on(
			'click',
			'.af_addon_add_field_button',
			function (e){

				e.preventDefault();

				var current_rule_id = $( this ).data( 'current_post_id' );

				var type = $( this ).data( 'type' );

				var field_btn = $(this);

				jQuery.ajax(
				{

					url: ajaxurl,

					type: 'POST',

					data: {

						current_rule_id : current_rule_id,

						type            : type,

						action          : 'af_pao_add_field',

						nonce 			: nonce,

					},

					success: function( data ){

						field_btn.closest( '.af-pao-div-whole-data').find('.af_addon_reload_main_div' ).after( data );

						$( '.af_addon_option_depend_selector' ).select2();

						var field_id = $( '.addon_hidden' ).val();

						$( '.' + field_id + 'fa-sort-down' ).hide();

						$( '.' + field_id + 'fa-sort-up' ).show();

						$( '.' + field_id + '_af_addon_field_depend_selector_div' ).fadeOut( 'fast' );

						$( '.' + field_id + '_af_addon_option_depend_selector_div' ).fadeOut( 'fast' );

						$( '.' + field_id + '_af_addon_dependable_div' ).show();

						$( '.' + field_id + '_af_addon_type_and_title_div' ).show();

						$( '.' + field_id + '_af_addon_desc_div' ).show();

						$( '.desc_text_area' + field_id ).hide();

						$( '.tooltip_text_area' + field_id ).hide();

						$( '.' + field_id + '_af_addon_tooltip_div' ).show();

						$( '.' + field_id + '_af_addon_req_div' ).show();

						$( '.' + field_id + '_af_addon_limit_range_div' ).hide();

						$( '.' + field_id + '_af_addon_price_range_div' ).hide();

						$( '.' + field_id + '_af_addon_file_extention_div' ).hide();

						$( '.' + field_id + '_af_addon_option_table_div' ).show();

						$( '.' + field_id + '_af_addon_add_optn_btn_div' ).show();
					}
				}
				);
			}
		);

		$( document ).on(
			'click',
			'.af_addon_delete_btn',
			
			function (e){

				e.preventDefault();

				if (confirm( "Are You Sure About this?" )) {

					var remove_option_id = $( this ).data( 'remove_option_id' );

					var current_rule_id = $( this ).data( 'current_post_id' );

					var button = $( this );

					jQuery.ajax(
					{
						url: ajaxurl,

						type: 'POST',

						data: {

							remove_option_id : remove_option_id,

							current_rule_id  : current_rule_id,

							action           : 'af_pao_remove_option',

							nonce 			 : nonce,

						},

						success: function(data){

							button.closest( 'tr' ).remove();

							$( '.fa-sort-down' ).hide();
						}
					}
					);
				}
			}
		);

		$( document ).on(
			'click',
			".af_addon_remove_btn",
			
			function (e){

				e.preventDefault();

				if (confirm( "Are You Sure About this?" )) {

					var remove_field_id = $( this ).data( 'remove_field_id' );

					var current_post_id = $( this ).data( 'current_post_id' );

					var button = $( this );

					jQuery.ajax(
					{

						url: ajaxurl,

						type: 'POST',

						data: {

							remove_field_id : remove_field_id,

							current_post_id : current_post_id,

							action          : 'af_pao_remove_field',

							nonce 			: nonce,

						},

						success: function(data){

							button.closest( '.af_addon_field_div' ).remove();

							$( '.fa-sort-up' ).hide();

							$( '.fa-sort-down' ).show();
						}
					}
					);
				}
			}
		);

		$( document ).on(
			'change',
			".af_addon_field_depend_selector",
			
			function (){

				var current_field_id = $( this ).data( 'current_field_id' );

				var field_value = $( this ).children( "option:selected" ).val();

				var option_value = $( '.' + current_field_id + '_af_addon_option_depend_selector' ).val();

				jQuery.ajax(
				{

					url: ajaxurl,

					type: 'POST',

					data: {

						current_field_id : current_field_id,

						field_value  	 : field_value,

						option_value  	 : JSON.stringify( option_value ),

						action           : 'af_addon_dependable_field',

						nonce 			 : nonce,

					},

					success: function(data){

						$( '.' + current_field_id + '_af_addon_option_depend_selector' ).html( data );

						$( '.' + current_field_id + '_af_addon_option_depend_selector' ).select2();

					}
				}
				);
			}
		);

		$( document ).ajaxComplete(
			
			function(event, xhr, settings){

				if ( settings.data && settings.data.toLowerCase().includes( 'woocommerce_load_variations' ) ) {

					$( '.my-color-field' ).wpColorPicker();

					$( '.af_style_down' ).show();

					$( '.af_style_up' ).hide();

					$( '.var_style_tab_main_class' ).hide();

					$( '.fa-sort-down' ).show();

					$( '.fa-sort-up' ).hide();

					$( '.af_addon_dependable_div' ).hide();

					$( '.af_addon_type_and_title_div' ).hide();

					$( '.af_addon_desc_div' ).hide();

					$( '.af_addon_tooltip_div' ).hide();

					$( '.af_addon_req_div' ).hide();

					$( '.af_addon_limit_range_div' ).hide();

					$( '.af_addon_price_range_div' ).hide();

					$( '.af_addon_file_extention_div' ).hide();

					$( '.af_addon_option_table_div' ).hide();

					$( '.af_addon_add_optn_btn_div' ).hide();

					jQuery( '.af_addon_option_depend_selector' ).select2({
						multiple: true,
						placeholder: 'Select Option',
					});

					$( document ).on(
						'click',
						".af_style_up",
						
						function (e){

							e.preventDefault();

							if ($( this ).data( 'clicked', true )) {

								$( '.af_style_down' ).show();

								$( '.af_style_up' ).hide();

								$( '.var_style_tab_main_class' ).slideUp( 'slow' );

							}
						}
					);

					$( document ).on(
						'click',
						".af_style_down",
						
						function (e){

							e.preventDefault();

							if ($( this ).data( 'clicked', true )) {

								$( '.af_style_down' ).hide();

								$( '.af_style_up' ).show();

								$( '.var_style_tab_main_class' ).slideDown( 'slow' );

							}
						}
					);

					$( document ).on( 'click', '.var_style_tab_hide', function(){ var_style_tab_hide(); });

					var_style_tab_hide();

					function var_style_tab_hide(){

						var var_id = $( '.var_style_tab_hide' ).data( 'var_id' );

						if ($( '.var_style_tab_hide' ).is( ':checked' )) {

							$( '.var_style_div' + var_id ).fadeIn( 'fast' );

						} else {

							$( '.var_style_div' + var_id ).fadeOut( 'fast' );
						}
					}

					$( document ).on( 'change', ".af_addon_field_type_selector", function (){ af_addon_field_type_selector(); } );

					af_addon_seperator_checkbox_class();

					af_addon_title_display_as_selector();

					af_addon_title_bg_class();

					af_addon_field_border_class();

					af_addon_depend_selector();

					tooltiptextarea();

					desctextarea();
				}
			}
		);
	}
);
