jQuery(document).ready(function($){

	var ajaxurl = addify_product_add_ons.admin_url;

	var nonce = addify_product_add_ons.nonce;

	jQuery( document ).on(
		
		'change',
		
		'input.variation_id',
		
		function(){

			setTimeout(function(){

				jQuery.ajax(
				{

					url: ajaxurl,

					type: 'POST',

					data: {

						action  	: 'af_vari_addons',
						
						nonce 		: nonce,
						
						form_data 	: $('form.variations_form').serialize(),
					},

					success: function(data){

						$('.af_addon_field_show').html(data['addons']);

						jQuery( '.af_addon_multi_select' ).select2({
	
							multiple: true,
							placeholder: 'Select Option',

						});

						$("#product_option_selected_name ul").html('');

						$("#product_option_selected_price ul").html('');

						$('.product-sub-total-final').remove();

						var quantity = $('.qty').val();

						var prod_title = data['prod_title'];
						
						var prod_price = parseFloat( data['prod_price'] );

						$('.af_pao_real_time_product_sub_total_calculation').closest('div').html( data['p_tag'] );

						var currency_sym = data['currency_symbol'];

						var final_price = parseFloat( ( quantity * prod_price ).toFixed( 2 ) );

						final_price = parseFloat( final_price.toFixed( 2 ) );

						$('.product-name-and-quantity').html( quantity + ' X ' + prod_title );

						$('.product-sub-total-1st-tr').html(currency_sym + ' ' + ( parseFloat( quantity * prod_price ).toFixed( 2 ) ) );

						var tr2 = '<tr class="product-sub-total-final"> <td></td> <td class="product_sub_total_final_td"> <b> Subtotal </b> ' + currency_sym + ' ' + final_price + ' </td></tr>';

						$('#addon-tbody').append(tr2);

						af_addon_front_dep_class();

					}
				});

			} , 700 );
		}
	);

	jQuery( '.af_addon_multi_select' ).select2({
	
		multiple: true,
		placeholder: 'Select Option',

	});

	$('.optn_name_price').css('display','none');

	select_on_change();

	$(document).on('change','.af_addon_drop_down, .af_addon_multi_select, .qty, .addon_file_upload_option, .addon_date_picker_option, .addon_time_picker_option, .addon_front_quantity' , function(){ select_on_change(); });

	$(document).on('click','.af_pao_options_radio_buttons, .af_pao_options_checkbox, .af_addon_images, .af_addon_color_swatcher ' , function() {select_on_change(); } );

	var thumbnail_img = $('.woocommerce-product-gallery__image a').attr('href');

	$(document).on('click', '.af_addon_image_swatcher', function(){

		if ($(this).val() != 0) {

			var img_link = $(this).closest('div.addons_div_selection').find('img').attr('src');

			$('.woocommerce-product-gallery__image a').attr('href', img_link);

			$('.woocommerce-product-gallery__image img').attr('src', img_link);

			$('.zoomImg').attr('src', img_link);

			$('.woocommerce-product-gallery__image a img').attr('srcset', img_link);

			$(document).on('click', '.woocommerce-product-gallery__trigger', function(){
				
				var img_link = $('.af_addon_image_swatcher:checked').closest('div.addons_div_selection').find('img').attr('src');

				// Check if the img element is found
				var $imgElement = $('.pswp__item .pswp__img');
				
				if ($imgElement.length) {
				
					$imgElement.attr('src', img_link);
				}
			});

		}
		select_on_change();
	});

	$(document).on('keyup', '.addon_front_short_text, .addon_front_number, .addon_telephone_option, .addon_front_long_text', function(){ select_on_change(); });


	$(document).on('input', '.addon_password_option', function(){ select_on_change(); });

	$(document).on('keypress', '.addon_email_option', function(){ select_on_change(); });


	function select_on_change(){

		var select_drop_down = new Object();

		$('.optn_name_price').css('display','revert');

		var quantity = $('.qty').val();	

		var counter = 0;

		var selected_option_val = 0;

		$('#addon-tbody').fadeIn('fast');

		if ( $('.qty').val() == '' || $('.qty').val() == 0  ) {

			$('#addon-tbody').fadeOut('fast');
			quantity = 0;	

		}

		var prod_price   = parseFloat( $('.af_pao_real_time_product_sub_total_calculation').data('prod_price') );
		
		var currency_sym = $('.af_pao_real_time_product_sub_total_calculation').data('currency_sym');

		$("#product_option_selected_name ul li").remove();
		
		$("#product_option_selected_price ul li").remove();

		$('.af_addon_drop_down').each(function(){

			if ( ! $(this).closest('.af_addon_field_class').is(':visible') ) {

				return;
			}

			if ($(this).val() == '') {

				return;
			}

			var current_optn_price = 0;

			counter++;

			var price_type = $(this).children('option:selected').data('price_type');

			var option_price = parseFloat( $(this).children('option:selected').data('option_price') );

			var option_name = $(this).children('option:selected').data('option_name');

			if (option_name != "undefined" && option_name) {

				if (price_type == 'free_selecter') {

					option_price = 0;
				}

				select_drop_down['free_selecter'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_selecter'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_selecter'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_selecter'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_selecter'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var drop_down = "<li>"+option_name + "</li>";

				var drop_down1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append("<li>"+option_name + "</li>");

				$("#product_option_selected_price ul").append("<li>"+ currency_sym + ' ' + select_drop_down[price_type].toFixed(2) + "</li>");
			}
		});

		$('.af_addon_multi_select').each(function(){

			var current_optn_price = 0;

			$(this).children('option:selected').each(function(){

				var price_type = $(this).data('price_type');

				var option_price = parseFloat( $(this).data('option_price') );

				var option_name = $(this).data('option_name');

				if (option_name != "undefined" && option_name) {

					if (price_type == 'free_multi_selecter') {

						option_price = 0;
					}

					select_drop_down['free_multi_selecter'] = parseFloat(option_price);

					select_drop_down['flat_fixed_fee_multi_selecter'] = parseFloat(option_price);

					select_drop_down['flat_percentage_fee_multi_selecter'] = parseFloat((option_price/100) * prod_price);

					select_drop_down['fixed_fee_based_on_quantity_multi_selecter'] = parseFloat(quantity * option_price);

					select_drop_down['Percentage_fee_based_on_quantity_multi_selecter'] = parseFloat((option_price/100) * prod_price) * quantity;

					selected_option_val += select_drop_down[price_type];

					var multi_selector = "<li>"+option_name + "</li>";

					var multi_selector1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type].toFixed(2) + "</li>";

					$("#product_option_selected_name ul").append(multi_selector);

					$("#product_option_selected_price ul").append(multi_selector1);
				}

			});
		});

		$('.af_pao_options_radio_buttons').each(function(){

			var current_optn_price = 0;

			var price_type = $(this).data('price_type');

			var option_price = parseFloat( $(this).data('option_price') );

			var option_name = $(this).data('option_name');

			if (option_name != "undefined" && option_name && $(this).is(':checked')) {

				if (price_type == 'free_radio_btn') {

					option_price = 0;
				}

				select_drop_down['free_radio_btn'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_radio_btn'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_radio_btn'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_radio_btn'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_radio_btn'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var radio_btn = "<li>"+option_name + "</li>";

				var radio_btn1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(radio_btn);

				$("#product_option_selected_price ul").append(radio_btn1);
			}
		});

		$('.af_addon_images').each(function(){

			var price_type = $(this).data('price_type');

			var option_price = parseFloat( $(this).data('option_price') );

			var option_name = $(this).data('option_name');

			if (option_price && option_price >= 0 && option_name != "undefined" && option_name && $(this).is(':checked')) {

				$(this).closest('.addons_div_selection').addClass('addon_selected_image_border_class');

				if (price_type == 'free_image') {

					option_price = 0;
				}

				select_drop_down['free_image'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_image'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_image'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_image'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_image'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var images = "<li>"+option_name + "</li>";

				var images1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(images);

				$("#product_option_selected_price ul").append(images1);

			} else {

				$(this).closest('.addons_div_selection').removeClass('addon_selected_image_border_class');

			}
		});

		$('.af_addon_image_swatcher').each(function(){

			var price_type = $(this).data('price_type');

			var option_price = parseFloat( $(this).data('option_price') );

			var option_name = $(this).data('option_name');

			if (option_price >= 0 && option_name != "undefined" && option_name && $(this).is(':checked')) {

				$(this).closest('.addons_div_selection').addClass('addon_selected_image_border_class');

				if (price_type == 'free_image_swatcher') {

					option_price = 0;
				}

				select_drop_down['free_image_swatcher'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_image_swatcher'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_image_swatcher'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_image_swatcher'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_image_swatcher'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var img_swatcher = "<li>"+option_name + "</li>";

				var img_swatcher1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(img_swatcher);

				$("#product_option_selected_price ul").append(img_swatcher1);

			} else {

				$(this).closest('.addons_div_selection').removeClass('addon_selected_image_border_class');

			}
		});

		$('.af_addon_color_swatcher').each(function(){

			var price_type = $(this).data('price_type');

			var option_price = parseFloat( $(this).data('option_price') );

			var option_name = $(this).data('option_name');

			if (price_type == 'free_color_swatcher') {

				option_price = 0;
			}

			if (option_price >= 0 && option_name != "undefined" && option_name && $(this).is(':checked')) {

				$(this).closest('.addons_div_selection').addClass('addon_selected_image_border_class');

				select_drop_down['free_color_swatcher'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_color_swatcher'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_color_swatcher'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_color_swatcher'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_color_swatcher'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var color_swatcher = "<li>"+option_name + "</li>";

				var color_swatcher1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(color_swatcher);

				$("#product_option_selected_price ul").append(color_swatcher1);

			} else {

				$(this).closest('.addons_div_selection').removeClass('addon_selected_image_border_class');

			}
		});

		$('.af_pao_options_checkbox').each( function(){	

			var checkbox_counter = 0;

			var total_checkboxes = $(this).data('total_checkboxes');

			var req_field = $(this).data('req_field');

			var field_id = $(this).data('field_id');
			var is_req   =  true;

			$('.af_pao_options_checkbox_' + field_id).each(function(){
				if ($('.af_pao_options_checkbox_' + field_id).is(':visible')) {
					if ($(this).is(':checked')) {
						is_req = false;
					}

					if ( is_req && $('.af_pao_options_checkbox_' + field_id).data('is_field_req') == 1 ) {

						$('.af_pao_options_checkbox_' + field_id).prop('required', true);

					} else {

						$('.af_pao_options_checkbox_' + field_id).prop('required', false);
					}
				}

			});

			if ($(this).is(':checked') && 'none' != $(this).data('option_name')) {

				$(this).prop('required', false);

				var option_name = $(this).data('option_name');

				var option_price = $(this).data('option_price');

				var price_type = $(this).data('price_type');

				var current_optn_price = 0;

				if (price_type == 'free_checkbox') {

					option_price = 0;
				}

				select_drop_down['free_checkbox'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_checkbox'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_checkbox'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_checkbox'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_checkbox'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var checkbox = "<li>"+option_name + "</li>";

				var checkbox1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(checkbox);

				$("#product_option_selected_price ul").append(checkbox1);
			}
		});

		$('.addon_front_short_text').each( function(){

			var input_text_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (input_text_price_type == 'free_input_text') {

					option_price = 0;
				}

				select_drop_down['0_input_text'] = option_price;

				select_drop_down['free_input_text'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_input_text'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_input_text'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_input_text'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_input_text'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[input_text_price_type];

				var short_text = "<li>"+$(this).val() + "</li>";

				var short_text1 = "<li>"+ currency_sym + ' ' + select_drop_down[input_text_price_type] + "</li>";

				$("#product_option_selected_name ul").append(short_text);

				$("#product_option_selected_price ul").append(short_text1);
			}
		});

		$('.addon_telephone_option').each( function(){

			var telephone_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (telephone_price_type == 'free_telephone') {

					option_price = 0;
				}

				select_drop_down['0_telephone'] = option_price;

				select_drop_down['free_telephone'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_telephone'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_telephone'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_telephone'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_telephone'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[telephone_price_type];

				var telephone = "<li>"+$(this).val() + "</li>";

				var telphone1 = "<li>"+ currency_sym + ' ' + select_drop_down[telephone_price_type] + "</li>";

				$("#product_option_selected_name ul").append(telephone);

				$("#product_option_selected_price ul").append(telphone1);
			}
		});

		$('.addon_password_option').each( function(){

			var price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (price_type == 'free_password') {

					option_price = 0;
				}

				select_drop_down['0_password'] = option_price;

				select_drop_down['free_password'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_password'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_password'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_password'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_password'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[price_type];

				var password = "<li>"+$(this).val() + "</li>";

				var password1 = "<li>"+ currency_sym + ' ' + select_drop_down[price_type] + "</li>";

				$("#product_option_selected_name ul").append(password);

				$("#product_option_selected_price ul").append(password1);
			}
		});

		$('.addon_email_option').each( function(){

			var email_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (email_price_type == 'free_email') {

					option_price = 0;
				}

				select_drop_down['0_email'] = option_price;

				select_drop_down['free_email'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_email'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_email'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_email'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_email'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[email_price_type];

				var email = "<li>"+$(this).val() + "</li>";

				var email1 = "<li>"+ currency_sym + ' ' + select_drop_down[email_price_type] + "</li>";

				$("#product_option_selected_name ul").append(email);

				$("#product_option_selected_price ul").append(email1);
			}
		});

		$('.addon_front_long_text').each( function(){

			var long_text_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (long_text_price_type == 'free_long_text') {

					option_price = 0;
				}

				select_drop_down['0_long_text'] = option_price;

				select_drop_down['free_long_text'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_long_text'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_long_text'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_long_text'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_long_text'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[long_text_price_type];

				var long_text = "<li>"+$(this).val() + "</li>";

				var long_text1 = "<li>"+ currency_sym + ' ' + select_drop_down[long_text_price_type] + "</li>";

				$("#product_option_selected_name ul").append(long_text);

				$("#product_option_selected_price ul").append(long_text1);
			}
		});

		$('.addon_file_upload_option').each(function(){

			var file_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if (  $(this).prop('files') &&  $(this).prop('files')[0] ) {

				if (file_price_type == 'free_file_upload') {

					option_price = 0;
				}

				select_drop_down['0_file_upload'] = option_price;

				select_drop_down['free_file_upload'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_file_upload'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_file_upload'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_file_upload'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_file_upload'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[file_price_type];

				var file_upload = "<li>"+$(this).prop('files')[0]['name']+ "</li>";

				var file_upload1 = "<li>"+ currency_sym + ' ' + select_drop_down[file_price_type] + "</li>";

				$("#product_option_selected_name ul").append(file_upload);

				$("#product_option_selected_price ul").append(file_upload1);
			}
		});

		$('.addon_date_picker_option').each(function(){

			if ($(this).val()) {

				var date_price_type = $(this).data('price_type');

				var option_price = $(this).data('price');

				select_drop_down['0_date_picker'] = option_price;

				select_drop_down['free_date_picker'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_date_picker'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_date_picker'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_date_picker'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_date_picker'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[date_price_type];

				var date_picker = "<li>"+$(this).val()+ "</li>";

				var date_picker1 = "<li>"+ currency_sym + ' '  + select_drop_down[date_price_type] + "</li>";

				$("#product_option_selected_name ul").append(date_picker);

				$("#product_option_selected_price ul").append(date_picker1);
			}
		});

		$('.addon_time_picker_option').each(function(){

			if ($(this).val()) {

				var date_price_type = $(this).data('price_type');

				var option_price = $(this).data('price');

				if (date_price_type == 'free_time_picker') {

					option_price = 0;
				}

				select_drop_down['0_time_picker'] = option_price;

				select_drop_down['free_time_picker'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_time_picker'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_time_picker'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_time_picker'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_time_picker'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[date_price_type];

				var time_picker = "<li>"+$(this).val()+ "</li>";

				var time_picker1 = "<li>"+ currency_sym + ' ' + select_drop_down[date_price_type] + "</li>";

				$("#product_option_selected_name ul").append(time_picker);

				$("#product_option_selected_price ul").append(time_picker1);
			}
		});

		$('.addon_front_number').each( function(){

			var number_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (number_price_type == 'free_number') {

					option_price = 0;
				}

				select_drop_down['0_number'] = option_price;

				select_drop_down['free_number'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_number'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_number'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_number'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_number'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[number_price_type];

				var number = "<li>"+$(this).val() + "</li>";

				var number1 = "<li>"+ currency_sym + ' ' + select_drop_down[number_price_type] + "</li>";

				$("#product_option_selected_name ul").append(number);

				$("#product_option_selected_price ul").append(number1);
			}
		});

		$('.addon_front_quantity').each(function(){

			var heading = $(this).data('heading');

			var quantity_price_type = $(this).data('price_type');

			var option_price = $(this).data('price');

			if ( $(this).val() != '' ) {

				if (quantity_price_type == 'free_quantity') {

					option_price = 0;
				}

				select_drop_down['0_quantity'] = option_price;

				select_drop_down['free_quantity'] = parseFloat(option_price);

				select_drop_down['flat_fixed_fee_quantity'] = parseFloat(option_price);

				select_drop_down['flat_percentage_fee_quantity'] = parseFloat((option_price/100) * prod_price);

				select_drop_down['fixed_fee_based_on_quantity_quantity'] = parseFloat(quantity * option_price);

				select_drop_down['Percentage_fee_based_on_quantity_quantity'] = parseFloat((option_price/100) * prod_price) * quantity;

				selected_option_val += select_drop_down[quantity_price_type];

				var addon_quantity = "<li>" + heading + " - " + $(this).val() + "</li>";

				var addon_quantity1 = "<li>"+ currency_sym + ' ' + select_drop_down[quantity_price_type] + "</li>";

				$("#product_option_selected_name ul").append(addon_quantity);

				$("#product_option_selected_price ul").append(addon_quantity1);
			}
		});

		$('.optn_name_price').hide();
		if ( $('.optn_name_price td ul').children().length >= 1 ) {

			$('.optn_name_price').show();

		}

		calc(selected_option_val);
	}

	function calc(selected_option_val){

		$('.product-sub-total-final').remove();

		var quantity = $('.qty').val();

		var prod_title = $('.af_pao_real_time_product_sub_total_calculation').data('prod_title');
		
		var prod_price = parseFloat( $('.af_pao_real_time_product_sub_total_calculation').data('prod_price') );

		var currency_sym = $('.af_pao_real_time_product_sub_total_calculation').data('currency_sym');

		var final_price = parseFloat( (quantity*prod_price).toFixed(2));

		final_price += parseFloat( selected_option_val );

		final_price = parseFloat(final_price.toFixed(2));

		$('.product-name-and-quantity').html(quantity+' X '+prod_title);

		$('.product-sub-total-1st-tr').html(currency_sym + ' ' + (parseFloat(quantity * prod_price).toFixed(2)));

		var tr2 = '<tr class="product-sub-total-final"> <td></td> <td class="product_sub_total_final_td"> <b> Subtotal </b> ' + currency_sym +' '+ final_price + ' </td></tr>';

		$('#addon-tbody').append(tr2);
	}

	af_addon_front_dep_class();

	// $(document).on('click change' , '.af_addon_front_dep_class' , af_addon_front_dep_class );
	
	function af_addon_front_dep_class(){

		$('.af_addon_front_dep_class').each(function() {

			$(this).closest('div.af_addon_field_class').hide();

			var depend_on_field_id = $(this).data('dependent_on');

			var depend_on_field_value = $(this).data('dependent_val');

			var radio = false;

			if (!depend_on_field_value || depend_on_field_value.length < 1) {
				return;
			}

			var current_element = $(this);

			current_element.closest('div.af_addon_field_class').hide();

			if (current_element.is('select')) {

				let options = current_element.find('option');

				options.each( function(key, value){

					$(this).prop("selected", false);
					$(this).removeAttr("selected");

				});
				current_element.trigger('change');
			}

				// console.log('af_addon_field_' + depend_on_field_id);

			var parent_element = $(document).find('#af_addon_field_' + depend_on_field_id);

			var values = parent_element.val();

			if ('checkbox' == parent_element.attr('type')) {

				var parent_elements = parent_element.closest('div.af_addon_field_class').find('input[type="checkbox"]');

				if (parent_elements.length > 1) {

					values = [];

					var selected_elements = parent_element.closest('div.af_addon_field_class').find('input[type="checkbox"]:checked');

					selected_elements.each(function() {

						values.push($(this).val());

					});

				} else {


					if ( parent_element.is(':checked') ) {


						current_element.closest('div.af_addon_field_class').show();

					} else {

						current_element.closest('div.af_addon_field_class').hide();

					}
				}

			} else if ('radio' == parent_element.attr('type')) {

				var parent_elements = parent_element.closest('div.af_addon_field_class').find('input[type="radio"]');

				if (parent_elements.length > 1) {

					values = parent_element.closest('div.af_addon_field_class').find('input[type="radio"]:checked').val();

				} else {

					if (parent_element.is(':checked')) {

							// Radio button is checked, do something with the value
							// values = parent_element.val();

						current_element.closest('div.af_addon_field_class').show();

					} else {

							// Radio button is unchecked, clear the value
							// values = null;

						current_element.closest('div.af_addon_field_class').hide();

					}
				}
			}

			if ( 'string' != typeof depend_on_field_value ) {
				depend_on_field_value = depend_on_field_value.toString();
			}

			if ( !depend_on_field_value ) {
				return;
			}

			var values_array = depend_on_field_value.split(',');

			if (values_array.length < 1) {

				current_element.closest('div.af_addon_field_class').hide();
				return;

			}

			if (Array.isArray(values)) {

				let intersection = values_array.filter(x => values.includes(x));

				if (intersection.length < 1) {

					current_element.closest('div.af_addon_field_class').hide();

					if (current_element.prop('required')) {

						current_element.attr('data-required', 'required');

						current_element.prop('required', false);
					}


				} else {

					if (current_element.data('required')) {

						current_element.prop('required', true);
					}

					current_element.closest('div.af_addon_field_class').show();
				}

			} else {

				if (values_array.includes(values)) {

					if (current_element.data('required')) {

						current_element.prop('required', true);
					}

						// console.log(current_element);

						// current_element.closest('div.af_addon_field_class').show();

				} else {

					if (current_element.prop('required')) {

						current_element.attr('data-required', 'required');
						current_element.prop('required', false);
						current_element.attr('required', false);

					}

					current_element.closest('div.af_addon_field_class').hide();

				}
			}

			if (!parent_element.is(':visible')) {

				current_element.closest('div.af_addon_field_class').hide();

			}

			$(document).on('change', '#af_addon_field_' + depend_on_field_id, function() {

				var values         = $(this).val();
				var parent_element = $(this);

				var values_array = depend_on_field_value.split(',');

				if ('checkbox' == parent_element.attr('type')) {

					var parent_elements = parent_element.closest('div.af_addon_field_class').find('input[type="checkbox"]');

					if (parent_elements.length > 1) {

						values = [];

						var selected_elements = parent_element.closest('div.af_addon_field_class').find('input[type="checkbox"]:checked');

						selected_elements.each(function() {

							values.push($(this).val());
						});


					} else {
							// console.log('yes');

						if (parent_element.is(':checked')) {
								// console.log('checked');
							current_element.closest('div.af_addon_field_class').show();

						} else {

							current_element.closest('div.af_addon_field_class').hide();

						}

						return;
					}

				} else if ('radio' == parent_element.attr('type')) {

					var parent_elements = parent_element.closest('div.af_addon_field_class').find('input[type="radio"]');

					if (parent_elements.length > 1) {

						values = parent_element.closest('div.af_addon_field_class').find('input[type="radio"]:checked').val();

					} else {

						if (parent_element.is(':checked')) {
							current_element.closest('div.af_addon_field_class').show();
						} else {
							current_element.closest('div.af_addon_field_class').hide();
						}
					}
				}

				if (values_array.length < 1) {
					current_element.hide();
					return;
				}

				if (Array.isArray(values)) {

					let intersection = values_array.filter(x => values.includes(x));

					if (intersection.length < 1) {

						current_element.closest('div.af_addon_field_class').hide();

						if (current_element.prop('required')) {

							current_element.attr('data-required', 'required');
							current_element.prop('required', false);
						}

					} else {

						if (current_element.data('required')) {

							current_element.prop('required', true);
						}

						current_element.closest('div.af_addon_field_class').show();
					}

				} else {

					if (values_array.includes(values)) {

						if (current_element.data('required')) {

							current_element.prop('required', true);
						}

						current_element.closest('div.af_addon_field_class').show();

					} else {
						if (current_element.prop('required')) {
							current_element.attr('data-required', 'required');
							current_element.prop('required', false);
						}

						current_element.closest('div.af_addon_field_class').hide();
					}
				}
			});
		});
	}
}
);
