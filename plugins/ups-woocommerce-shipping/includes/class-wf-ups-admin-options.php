<?php
if( !class_exists('WF_UPS_Admin_Options') ){
	class WF_UPS_Admin_Options{
		
		// Class Variables Declaration
		public $settings;
		public $commercial_invoice;
		public $nafta_co_form;
		public $eei_data;
		public $eei_shipper_filed_option;
		public $international_special_commodities;
		public $var_settings;
		
		function __construct(){
			$this->init();
		}

		function init(){

			$this->settings 			= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null );
			$this->commercial_invoice	= isset( $this->settings['commercial_invoice'] ) && !empty($this->settings['commercial_invoice']) && $this->settings['commercial_invoice'] == 'yes' ? true : false; 
			$this->nafta_co_form 		= ( isset( $this->settings['nafta_co_form'] ) && !empty($this->settings['nafta_co_form']) && $this->settings['nafta_co_form'] == 'yes' ) ? true : false;
			$this->eei_data 			= ( isset( $this->settings['eei_data'] ) && !empty($this->settings['eei_data']) && $this->settings['eei_data'] == 'yes' ) ? true : false;
			$this->eei_shipper_filed_option	= ( isset( $this->settings['eei_shipper_filed_option'] ) && !empty($this->settings['eei_shipper_filed_option']) ) ? $this->settings['eei_shipper_filed_option'] : '';
			$this->international_special_commodities = isset( $this->settings['international_special_commodities'] ) && !empty($this->settings['international_special_commodities']) && $this->settings['international_special_commodities'] == 'yes' ? true : false;

            //add a custome field in product page
			add_action( 'woocommerce_product_options_shipping', array($this,'wf_add_custom_field_ups') );

            //Saving the values
			add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_custom_field_ups' ) );

			add_action( 'woocommerce_product_after_variable_attributes', array($this, 'ph_ups_add_custom_product_fields_at_variation'), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'ph_ups_save_custom_product_fields_at_variation'), 10, 2 );
		}

		function wf_add_custom_field_ups() {

			?><hr style="border-top: 1px solid #eee;" /><p class="ph_ups_other_details">UPS Shipping Details<span class="ups_toggle_symbol" aria-hidden="true"></span></p><div class="ph_ups_hide_show_product_fields"><?php

			// Print a custom select field
			woocommerce_wp_select( array(
				'id' 		=> '_wf_ups_deliveryconfirmation',
				'label' 	=> __('Delivery Confirmation'),
				'options'	=> array(
					0	=> __( 'Confirmation Not Required', 'ups-woocommerce-shipping' ),
					1	=> __( 'Confirmation Required', 'ups-woocommerce-shipping' ),
					2	=> __( 'Confirmation With Signature', 'ups-woocommerce-shipping' ),
					3	=> __( 'Confirmation With Adult Signature', 'ups-woocommerce-shipping' )
				),
				'desc_tip' => false,
			) );

			woocommerce_wp_text_input( array(
				'id'			=> '_wf_ups_custom_declared_value',
				'label'			=> __( 'Custom Declared Value', 'ups-woocommerce-shipping' ),
				'description'	=> __('This amount will be reimbursed from UPS if products get damaged and you have opt for Insurance.','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
				'placeholder'	=> __( 'Insurance amount', 'ups-woocommerce-shipping' )
			) );

			woocommerce_wp_text_input( array(
				'id'			=> '_wf_ups_hst',
				'label'			=> __( 'H.S. Tariff', 'ups-woocommerce-shipping' ),
				'description'	=> __('H.S. tariff number','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
			) );

			woocommerce_wp_textarea_input( array(
				'id'			=> 'ph_ups_invoice_desc',
				'label'			=> __( 'Product Description', 'ups-woocommerce-shipping' ),
				'description'	=> __('Description of product','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
			) );


			//Country of Manufacture
			woocommerce_wp_text_input( array(
				'id' 			=> '_ph_ups_manufacture_country',
				'label' 		=> __('Country of Manufacture','ups-woocommerce-shipping'),
				'description' 	=> __('Country Code of Manufacture','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
				'placeholder' 	=> __('Country Code','ups-woocommerce-shipping')
			) );

			if( $this->nafta_co_form && $this->commercial_invoice )
			{
				// Net Cost Code
				woocommerce_wp_select( array(
					'id' 			=> '_ph_net_cost_code',
					'label' 		=> __('Net Cost Code','ups-woocommerce-shipping'),
					'options'		=> array(
						'NC'	=> __( 'NC', 'ups-woocommerce-shipping' ),
						'ND'	=> __( 'ND', 'ups-woocommerce-shipping' ),
						'NO'	=> __( 'NO', 'ups-woocommerce-shipping' ),
					),
					'description'	=> __('Select NC if the Regional Value Content(RVC) is calculated according to the net cost method; otherwise, select NO. If the RVC is calculated over a period of time then select ND','ups-woocommerce-shipping'),
					'desc_tip' 		=> true,
				) );

				// Preference Criteria
				woocommerce_wp_select( array(
					'id' 			=> '_ph_preference_criteria',
					'label' 		=> __('Preference Criteria','ups-woocommerce-shipping'),
					'options'		=> array(
						'A'	=> __( 'A', 'ups-woocommerce-shipping' ),
						'B'	=> __( 'B', 'ups-woocommerce-shipping' ),
						'C'	=> __( 'C', 'ups-woocommerce-shipping' ),
						'D'	=> __( 'D', 'ups-woocommerce-shipping' ),
						'E'	=> __( 'E', 'ups-woocommerce-shipping' ),
						'F'	=> __( 'F', 'ups-woocommerce-shipping' ),
					),
					'description'	=> __('Select for each good described in the Description of Goods field','ups-woocommerce-shipping'),
					'desc_tip' 		=> true,
				) );

				// Producer Info
				woocommerce_wp_select( array(
					'id' 			=> '_ph_producer_info',
					'label' 		=> __('Producer Info','ups-woocommerce-shipping'),
					'options'		=> array(
						'Yes'	=> __( 'Yes', 'ups-woocommerce-shipping' ),
						'No[1]'	=> __( 'No[1]', 'ups-woocommerce-shipping' ),
						'No[2]'	=> __( 'No[2]', 'ups-woocommerce-shipping' ),
						'No[3]'	=> __( 'No[3]', 'ups-woocommerce-shipping' ),
					),
					'description'	=> __('Yes - If shipper is the producer of the good <br/> No [1] - Knowledge of whether the good qualifies as an originating good <br/> No [2] - Reliance on the producers written representation (other than a Certificate of Origin) that the good qualifies as an originating good <br/> No [3] - A completed and signed Certificate for the good voluntarily provided to the exporter by the producer','ups-woocommerce-shipping'),
					'desc_tip' 		=> true,
				) );
			}

			if( $this->eei_data && $this->commercial_invoice )
			{
				// Export Type
				woocommerce_wp_select( array(
					'id' 			=> '_ph_eei_export_type',
					'label' 		=> __('Export Type','ups-woocommerce-shipping'),
					'options'		=> array(
						'D'	=> __( 'Domestic', 'ups-woocommerce-shipping' ),
						'F'	=> __( 'Foreign', 'ups-woocommerce-shipping' ),
					),
					'description'	=> __('Domestic: Exports that have been produced, manufactured or grown in the United States or Puerto Rico. This includes imported merchandise which has been enhanced in value or changed from the form in which imported by further manufacture or processing in the United States or Puerto Rico. <br/><br/><br/> Foreign: Merchandise that has entered the United States and is being exported again in the same condition as when imported.','ups-woocommerce-shipping'),
					'desc_tip' 		=> true,
				) );

				// Export Information
				woocommerce_wp_text_input( array(
					'id'			=> '_ph_eei_export_information',
					'label'			=> __( 'Export Information', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Required for EEI form if it is a SDL Product. <br/> Valid values are: LC, LV, SS, MS, GS, DP, HR, UG, IC, SC, DD, HH, SR, TE, TL, IS, CR, GP, RJ, TP, IP, IR, DB, CH, RS, OS.','ups-woocommerce-shipping'),
					'desc_tip'		=> 'true',
					'placeholder'	=> __( 'LC', 'ups-woocommerce-shipping' ),
				) );

				// ScheduleB Number
				woocommerce_wp_text_input( array(
					'id'			=> '_ph_eei_schedule_b_number',
					'label'			=> __( 'Commodity Classification Code', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'A unique 10-digit commodity classification code for the item being exported.','ups-woocommerce-shipping'),
					'desc_tip'		=> 'true',
				) );

				// Unit of Measure - ScheduleB
				woocommerce_wp_text_input( array(
					'id'			=> '_ph_eei_unit_of_measure',
					'label'			=> __( 'Unit of Measure', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'The unit of measure indicated on the Export License. Enter an X if there is no unit of measure in the Schedule B Unit field.','ups-woocommerce-shipping'),
					'desc_tip'		=> 'true',
					'placeholder'	=> __( 'X', 'ups-woocommerce-shipping' ),
				) );

				if( $this->eei_shipper_filed_option == 'A' )
				{

					// ITAR Exemption Number
					woocommerce_wp_text_input( array(
						'id'			=> '_ph_eei_itar_exemption_number',
						'label'			=> __( 'ITAR Exemption Number', 'ups-woocommerce-shipping' ),
						'description'	=> __( 'The specific citation (exemption number) under the International Traffic in Arms Regulations (ITAR) from the Code of Federal Register that exempts the shipment from the requirements for a license or other written authorization from the Directorate of Trade Controls (DDTC).','ups-woocommerce-shipping'),
						'desc_tip'		=> 'true',
					) );


					// DDTC Information - UOM
					woocommerce_wp_text_input( array(
						'id'			=> '_ph_eei_ddtc_information_uom',
						'label'			=> __( 'DDTC Information Unit Of Measurement', 'ups-woocommerce-shipping' ),
						'description'	=> __( 'Department of State/ Directorate of Defense Trade Control Information Unit of measurement code. The two or three alpha unit of measurement for the article being shipped.<br/> For example: BAG/BG - bags','ups-woocommerce-shipping'),
						'desc_tip'		=> 'true',
					) );

					// ACM Number
					woocommerce_wp_text_input( array(
						'id'			=> '_ph_eei_acm_number',
						'label'			=> __( 'ACM Number', 'ups-woocommerce-shipping' ),
						'description'	=> __( 'Approved Community Member Number (ACM). It is required to be provided along with ITAR Exemption Number for some License code (SGB and SAU). The ACM# for the United Kingdom (License code SGB) must begin with UK followed by 9 numbers. The ACM# for Australia (License Code SAU) must begin with DTT followed by 8 numbers.','ups-woocommerce-shipping'),
						'desc_tip'		=> 'true',
					) );

				}
			}

			//Direct Delivery Option under Product Shipping tab
			woocommerce_wp_checkbox( array(
				'id'			=> '_wf_ups_direct_delivery',
				'label'			=> __( 'Direct Delivery', 'ups-woocommerce-shipping' ),
				'description'	=> __('Check this to enable direct delivery.','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
			) );

			// Product Address Preference
			woocommerce_wp_checkbox( array(
				'id'			=> '_ph_ups_product_address_preference',
				'label'			=> __( 'Default to Origin Address', 'ups-woocommerce-shipping' ),
				'description'	=> __('By enabling this option the Ship From Address will set to Origin Address irrespective of any other product in the cart.','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
			) );

			// Pre packed product
			woocommerce_wp_checkbox( array(
				'id' 			=> '_wf_pre_packed_product',
				'label' 		=> __('Pre Packed Product','ups-woocommerce-shipping'),
				'description' 	=> __('Check this if the item comes in boxes. It will consider as a separate package and ship in its own box.', 'ups-woocommerce-shipping'),
				'desc_tip' 		=> 'true',
			) );

			$post_id 		= get_the_ID();
			$this->settings = get_post_meta( $post_id, '_ph_ups_hazardous_settings',1);
			
			// Hazardous Materials Checkbox
			woocommerce_wp_checkbox( array(
				'id' 			=> '_ph_ups_hazardous_materials',
				'label' 		=> __( 'Hazardous Materials', 'ups-woocommerce-shipping' ),
				'description' 	=> __('Check this to mark the product as a Hazardous Goods.','ups-woocommerce-shipping'),
				'desc_tip' 		=> 'true',
				'default'		=> 'no',
				'value'			=> get_post_meta( $post_id, '_ph_ups_hazardous_materials',1),
			));

			?><div class="ph_ups_hazardous_materials"><?php

			// Chemical Record Identifier
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_record_number',
				'label'			=> __( 'Chemical Record Identifier', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Chemical Record Identifier', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_record_number'])?$this->settings['_ph_ups_record_number']:'',
				'description'	=> __('Chemical Record Identifier is the chemicals in the shipment. If there are 3 chemicals then they need to be numbered in that field with 1, 2, 3 as values in the container.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Class Division Number
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_class_division_no',
				'label'			=> __( 'HazMat Class Division Number', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: 3', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_class_division_no'])?$this->settings['_ph_ups_class_division_no']:'',
				'description' 	=>__('Hazard Class/Division associated to the specified commodity.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Commodity ID Number
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_commodity_id',
				'label'			=> __( 'HazMat Commodity ID Number', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: UN1088', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_commodity_id'])?$this->settings['_ph_ups_commodity_id']:'',
				'description' 	=>__('This is the ID number (UN/NA/ID).','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Transportation Mode
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_transportaion_mode',
				'label'			=> __( 'Transportation Mode', 'ups-woocommerce-shipping'),
				'options'		=> array(
					'01'	=> __( 'Highway', 'ups-woocommerce-shipping' ),
					'02'	=> __( 'Ground', 'ups-woocommerce-shipping' ),
					'03'	=> __( 'Passenger Aircraft', 'ups-woocommerce-shipping' ),
					'04'	=> __( 'Cargo Aircraft Only', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->settings['_ph_ups_hm_transportaion_mode'])?$this->settings['_ph_ups_hm_transportaion_mode']:'01',
				'description' 	=>__('The method of transport by which a shipment is approved to move and the regulations associated with that method.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			));

			// Hazardous Materials Regulations
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_regulations',
				'label'			=> __( 'HazMat Regulation Set', 'ups-woocommerce-shipping'),
				'description'	=> __( 'Select the Regulation .', 'ups-woocommerce-shipping').'<br />'.__( 'ADR - Europe to Europe Ground Movement.', 'ups-woocommerce-shipping' ).'<br />'.__( 'CFR - HazMat regulated by US Dept. of Transportation within the U.S. or ground shipments to Canada.', 'ups-woocommerce-shipping' ).'<br />'.__( 'IATA - Worldwide Air movement.', 'ups-woocommerce-shipping' ).'<br />'.__( 'TDG - Canada to Canada ground movement or Canada to U.S. standard movement.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'options'		=> array(
					'CFR'	=> __( 'CFR', 'ups-woocommerce-shipping' ),
					'ADR'	=> __( 'ADR', 'ups-woocommerce-shipping' ),
					'IATA'	=> __( 'IATA', 'ups-woocommerce-shipping' ),
					'TDG'	=> __( 'TDG', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->settings['_ph_ups_hm_regulations'])?$this->settings['_ph_ups_hm_regulations']:'CFR',
			));

			// Package Group Type
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_package_group_type',
				'label'			=> __( 'Packaging Type', 'ups-woocommerce-shipping' ),
				'options'		=> array(
					''		=> __( 'None', 'ups-woocommerce-shipping' ),
					'I'		=> __( 'I', 'ups-woocommerce-shipping' ),
					'II'	=> __( 'II', 'ups-woocommerce-shipping' ),
					'III'	=> __( 'III', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->settings['_ph_ups_package_group_type'])?$this->settings['_ph_ups_package_group_type']:'',
				'description' 	=>__('Packing group category associated to the specified commodity.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Packaging Instruction Code
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_package_instruction_code',
				'label'			=> __( 'Packaging Instruction Code', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'Enter the Packaging instruction Code UPS uses to guide the safe packaging of hazardous materials for transportation.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'custom_attributes' => array(
					'maxlength' => 4,
				),
				'placeholder'	=> __( 'Ex: Y341', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_package_instruction_code'])?$this->settings['_ph_ups_package_instruction_code']:'',
			) );

			// Proper Shipping Name
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_shipping_name',
				'label'			=> __( 'HazMat Proper Shipping Name', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'The Shipping Name assigned by ADR, CFR or IATA', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'placeholder'	=> __( 'Ex: Acetal', 'ups-woocommerce-shipping'),
				'value' 	=> !empty($this->settings['_ph_ups_shipping_name'])?$this->settings['_ph_ups_shipping_name']:'',
			) );

			// Technical Name
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_technical_name',
				'label'			=> __( 'HazMat Technical Name', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'The technical name for the specified commodity.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'value' 		=> !empty($this->settings['_ph_ups_technical_name'])?$this->settings['_ph_ups_technical_name']:'',
			) );

			// Additional Description  
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_additional_description',
				'label'			=> __( 'HazMat Additional Description', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'Additional remarks or special provision information. Additional information that may be required by regulation about a hazardous material, such as, Limited Quantity, DOT-SP numbers, EX numbers.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'placeholder'	=> __( 'Ex: FLAMMABLE LIQUID', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_additional_description'])?$this->settings['_ph_ups_additional_description']:'',
			) );

			// Package Type
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_package_type',
				'label'			=> __( 'Package Type', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: Fiberboard Box', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->settings['_ph_ups_package_type'])?$this->settings['_ph_ups_package_type']:'',
				'description' 	=>__('The package type code identifying the type of packaging used for the commodity. (Ex: Fiberboard Box).','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Commodity Type
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_commodity',
				'label'			=> __( 'HazMat Commodity Regulation Type', 'ups-woocommerce-shipping'),
				'description'	=> __( 'Select the Regulation Type', 'ups-woocommerce-shipping').'<br />'.__( 'FR - Fully Regulated.', 'ups-woocommerce-shipping' ).'<br />'.__( 'LQ - Limited Quantity.', 'ups-woocommerce-shipping' ).'<br />'.__( 'EQ - Excepted Quantity.', 'ups-woocommerce-shipping' ).'<br />'.__( 'LR - Lightly Regulated.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'options'		=> array(
					'FR'	=> __( 'FR', 'ups-woocommerce-shipping' ),
					'LQ'	=> __( 'LQ', 'ups-woocommerce-shipping' ),
					'EQ'	=> __( 'EQ', 'ups-woocommerce-shipping' ),
					'LR'	=> __( 'LR', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->settings['_ph_ups_hm_commodity'])?$this->settings['_ph_ups_hm_commodity']:'',
			));

			?></div><?php
			
			if ($this->international_special_commodities) {

				$this->settings = get_post_meta( $post_id, '_ph_ups_restricted_settings',1);

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_restricted_article',
					'label'			=> __( 'Restricted Articles', 'ups-woocommerce-shipping' ),
					'value'			=> get_post_meta( $post_id, '_ph_ups_restricted_article',1),
				) );

				?><div class="ph_ups_restricted_article"><?php

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_diog',
					'label'			=> __( 'Diagnostic Specimens Indicator ', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package has Biological substances.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_diog'])?$this->settings['_ph_ups_diog']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_alcoholic',
					'label'			=> __( 'Alcoholic Beverages Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Alcoholic Beverages.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_alcoholic'])?$this->settings['_ph_ups_alcoholic']:'',
				) );
				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_perishable',
					'label'			=> __( 'Perishables Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Perishable items.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_perishable'])?$this->settings['_ph_ups_perishable']:'',
				) );
				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_plantsindicator',
					'label'			=> __( 'Plants Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Plants.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_plantsindicator'])?$this->settings['_ph_ups_plantsindicator']:'',

				) );
				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_seedsindicator',
					'label'			=> __( 'Seeds Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Seeds.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_seedsindicator'])?$this->settings['_ph_ups_seedsindicator']:'',

				) );
				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_specialindicator',
					'label'			=> __( 'Special Exceptions Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Special Exception items.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_specialindicator'])?$this->settings['_ph_ups_specialindicator']:'',

				) );
				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_tobaccoindicator',
					'label'			=> __( 'Tobacco Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Tobacco.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->settings['_ph_ups_tobaccoindicator'])?$this->settings['_ph_ups_tobaccoindicator']:'',

				) );

				?></div><?php

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_refrigeration',
					'label'			=> __( 'Refrigeration Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains an item that needs refrigeration.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
				) );

				woocommerce_wp_text_input( array(
					'id'			=> '_ph_ups_clinicaltrials',
					'label'			=> __( 'Clinical Trials  Id', 'ups-woocommerce-shipping' ),
					'description' 	=> __( 'Unique identifier for clinical trials','ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
				) );

			}

			?></div><?php
		}

		public function ph_ups_add_custom_product_fields_at_variation( $loop, $variation_data, $variation ){

			?><hr style="border-top: 1px solid #eee;" /><p class="ph_ups_var_other_details">UPS Shipping Details<span class="ups_var_toggle_symbol" aria-hidden="true"></span></p><div class="ph_ups_hide_show_var_product_fields"><?php

			$custom_declared_value = get_post_meta( $variation->ID, '_ph_ups_custom_declared_value_var', true );
			$hstno = get_post_meta( $variation->ID, '_ph_ups_hst_var', true );

			if( empty( $hstno ) ){
				$hstno = get_post_meta( wp_get_post_parent_id($variation->ID), '_wf_ups_hst', true );
			}
			$prod_desc = get_post_meta( $variation->ID, 'ph_ups_invoice_desc_var', true );

			if( empty( $prod_desc ) ){
				$prod_desc = get_post_meta( wp_get_post_parent_id($variation->ID), 'ph_ups_invoice_desc', true );
			}

			$is_pre_packed_var = get_post_meta( $variation->ID, '_wf_pre_packed_product_var', true );

			if( empty( $is_pre_packed_var ) ){
				$is_pre_packed_var = get_post_meta( wp_get_post_parent_id($variation->ID), '_wf_pre_packed_product', true );
			}

			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_custom_declared_value_var['.$variation->ID.']',
				'label'			=> __( 'Custom Declared Value', 'ups-woocommerce-shipping' ),
				'description'	=> __('This amount will be reimbursed from UPS if products get damaged and you have opt for Insurance.','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
				'placeholder'	=> __( 'Insurance amount', 'ups-woocommerce-shipping' ),
				'class' 		=> 'ph_ups_variation_class_text',
				'value'			=> $custom_declared_value,
			) );

			woocommerce_wp_checkbox( array(
				'id' 			=> '_wf_pre_packed_product_var[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_checkbox',
				'label' 		=> __(' Pre Packed Product (UPS)', 'ups-woocommerce-shipping'),
				'description' 	=> __('Check this if the item comes in boxes. It will override global product settings', 'ups-woocommerce-shipping'),
				'desc_tip' 		=> 'true',
				'value'			=> $is_pre_packed_var,
			) );

			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_hst_var[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'H.S. Tariff', 'ups-woocommerce-shipping' ),
				'description'	=> __('H.S. tariff number','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
				'value'			=> $hstno,
			) );

			woocommerce_wp_textarea_input( array(
				'id'			=> 'ph_ups_invoice_desc_var[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'Product Description', 'ups-woocommerce-shipping' ),
				'description'	=> __('Description of product','ups-woocommerce-shipping'),
				'desc_tip'		=> 'true',
				'value'			=> $prod_desc,
			) );

			// Hazardous Materials Checkbox
			woocommerce_wp_checkbox( array(
				'id' 			=> '_ph_ups_hazardous_materials[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_hazmat_product',
				'label' 		=> __( 'Hazardous Materials (UPS)', 'ups-woocommerce-shipping' ),
				'description' 	=> __('Check this to mark the product as a Hazardous Goods.','ups-woocommerce-shipping'),
				'desc_tip' 		=> 'true',
				'value'        	=> get_post_meta( $variation->ID, '_ph_ups_hazardous_materials', true ),
			));

			?><div class="ph_ups_var_hazardous_materials"><?php

			$this->var_settings = get_post_meta( $variation->ID, '_ph_ups_hazardous_settings', 1);

			// Chemical Record Identifier
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_record_number[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'Chemical Record Identifier (UPS)', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Chemical Record Identifier', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_record_number'])?$this->var_settings['_ph_ups_record_number']:'',
				'description'	=> __('Chemical Record Identifier is the chemicals in the shipment. If there are 3 chemicals then they need to be numbered in that field with 1, 2, 3 as values in the container.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Class Division Number
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_class_division_no[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'HazMat Class Division Number (UPS)', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: 3', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_class_division_no'])?$this->var_settings['_ph_ups_class_division_no']:'',
				'description' 	=> __('Hazard Class/Division associated to the specified commodity.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Commodity ID Number
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_commodity_id[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'HazMat Commodity ID Number (UPS)', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: UN1088', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_commodity_id'])?$this->var_settings['_ph_ups_commodity_id']:'',
				'description' 	=> __('This is the ID number (UN/NA/ID).','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Transportation Mode
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_transportaion_mode[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_select',
				'label'			=> __( 'Transportation Mode (UPS)', 'ups-woocommerce-shipping'),
				'options'		=> array(
					'01'	=> __( 'Highway', 'ups-woocommerce-shipping' ),
					'02'	=> __( 'Ground', 'ups-woocommerce-shipping' ),
					'03'	=> __( 'Passenger Aircraft', 'ups-woocommerce-shipping' ),
					'04'	=> __( 'Cargo Aircraft Only', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->var_settings['_ph_ups_hm_transportaion_mode'])?$this->var_settings['_ph_ups_hm_transportaion_mode']:'01',
				'description' 	=> __('The method of transport by which a shipment is approved to move and the regulations associated with that method.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			));

			// Hazardous Materials Regulations
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_regulations[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_select',
				'label'			=> __( 'HazMat Regulation Set (UPS)', 'ups-woocommerce-shipping'),
				'description'	=> __( 'Select the Regulation .', 'ups-woocommerce-shipping').'<br />'.__( 'ADR - Europe to Europe Ground Movement.', 'ups-woocommerce-shipping' ).'<br />'.__( 'CFR - HazMat regulated by US Dept. of Transportation within the U.S. or ground shipments to Canada.', 'ups-woocommerce-shipping' ).'<br />'.__( 'IATA - Worldwide Air movement.', 'ups-woocommerce-shipping' ).'<br />'.__( 'TDG - Canada to Canada ground movement or Canada to U.S. standard movement.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'options'		=> array(
					'CFR'	=> __( 'CFR', 'ups-woocommerce-shipping' ),
					'ADR'	=> __( 'ADR', 'ups-woocommerce-shipping' ),
					'IATA'	=> __( 'IATA', 'ups-woocommerce-shipping' ),
					'TDG'	=> __( 'TDG', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->var_settings['_ph_ups_hm_regulations'])?$this->var_settings['_ph_ups_hm_regulations']:'CFR',
			));

			// Package Group Type
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_package_group_type[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_select',
				'label'			=> __( 'Packaging Type (UPS)', 'ups-woocommerce-shipping' ),
				'options'		=> array(
					''		=> __( 'None', 'ups-woocommerce-shipping' ),
					'I'		=> __( 'I', 'ups-woocommerce-shipping' ),
					'II'	=> __( 'II', 'ups-woocommerce-shipping' ),
					'III'	=> __( 'III', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->var_settings['_ph_ups_package_group_type'])?$this->var_settings['_ph_ups_package_group_type']:'',
				'description' 	=> __('Packing group category associated to the specified commodity.','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Packaging Instruction Code
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_package_instruction_code[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'Packaging Instruction Code', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'Enter the Packaging instruction Code UPS uses to guide the safe packaging of hazardous materials for transportation.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'custom_attributes' => array(
					'maxlength' => 4,
				),
				'placeholder'	=> __( 'Ex: Y341', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_package_instruction_code'])?$this->var_settings['_ph_ups_package_instruction_code']:'',
			) );

			// Proper Shipping Name
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_shipping_name[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'HazMat Proper Shipping Name (UPS)', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'The Shipping Name assigned by ADR, CFR or IATA', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'placeholder'	=> __( 'Ex: Acetal', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_shipping_name'])?$this->var_settings['_ph_ups_shipping_name']:'',
			) );

			// Technical Name
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_technical_name[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'HazMat Technical Name (UPS)', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'The technical name for the specified commodity.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'value' 		=> !empty($this->var_settings['_ph_ups_technical_name'])?$this->var_settings['_ph_ups_technical_name']:'',
			) );

			// Additional Description  
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_additional_description[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'HazMat Additional Description (UPS)', 'ups-woocommerce-shipping' ),
				'description'	=> __( 'Additional remarks or special provision information. Additional information that may be required by regulation about a hazardous material, such as, Limited Quantity, DOT-SP numbers, EX numbers.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'placeholder'	=> __( 'Ex: FLAMMABLE LIQUID', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_additional_description'])?$this->var_settings['_ph_ups_additional_description']:'',
			) );

			// Package Type
			woocommerce_wp_text_input( array(
				'id'			=> '_ph_ups_package_type[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_text',
				'label'			=> __( 'Package Type (UPS)', 'ups-woocommerce-shipping' ),
				'placeholder'	=> __( 'Ex: Fiberboard Box', 'ups-woocommerce-shipping'),
				'value' 		=> !empty($this->var_settings['_ph_ups_package_type'])?$this->var_settings['_ph_ups_package_type']:'',
				'description' 	=>__('The package type code identifying the type of packaging used for the commodity. (Ex: Fiberboard Box).','ups-woocommerce-shipping'),
				'desc_tip'		=> true,
			) );

			// Commodity Type
			woocommerce_wp_select( array(
				'id'			=> '_ph_ups_hm_commodity[' . $variation->ID . ']',
				'class' 		=> 'ph_ups_variation_class_select',
				'label'			=> __( 'HazMat Commodity Regulation Type (UPS)', 'ups-woocommerce-shipping'),
				'description'	=> __( 'Select the Regulation Type', 'ups-woocommerce-shipping').'<br />'.__( 'FR - Fully Regulated.', 'ups-woocommerce-shipping' ).'<br />'.__( 'LQ - Limited Quantity.', 'ups-woocommerce-shipping' ).'<br />'.__( 'EQ - Excepted Quantity.', 'ups-woocommerce-shipping' ).'<br />'.__( 'LR - Lightly Regulated.', 'ups-woocommerce-shipping' ),
				'desc_tip'		=> true,
				'options'	=> array(
					'FR'	=> __( 'FR', 'ups-woocommerce-shipping' ),
					'LQ'	=> __( 'LQ', 'ups-woocommerce-shipping' ),
					'EQ'	=> __( 'EQ', 'ups-woocommerce-shipping' ),
					'LR'	=> __( 'LR', 'ups-woocommerce-shipping' )
				),
				'value' 		=> !empty($this->var_settings['_ph_ups_hm_commodity'])?$this->var_settings['_ph_ups_hm_commodity']:'',
			));

			?></div><?php

			if ($this->international_special_commodities) {

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_restricted_article[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_restricted_product',
					'label'			=> __( 'Restricted Articles', 'ups-woocommerce-shipping' ),
					'value'        	=> get_post_meta( $variation->ID, '_ph_ups_restricted_article', true ),
				) );

				?><div class="ph_ups_var_restricted_article"><?php

				$this->var_settings = get_post_meta( $variation->ID, '_ph_ups_restricted_settings', 1);

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_diog[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Diagnostic Specimens Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package has Biological substances.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_diog'])?$this->var_settings['_ph_ups_diog']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_alcoholic[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Alcoholic Beverages Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Alcoholic Beverages.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_alcoholic'])?$this->var_settings['_ph_ups_alcoholic']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_perishable[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Perishables Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Perishable items.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_perishable'])?$this->var_settings['_ph_ups_perishable']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_plantsindicator[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Plants Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Plants.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_plantsindicator'])?$this->var_settings['_ph_ups_plantsindicator']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_seedsindicator[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Seeds Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Seeds.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_seedsindicator'])?$this->var_settings['_ph_ups_seedsindicator']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_specialindicator[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Special Exceptions Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Special Exception items.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_specialindicator'])?$this->var_settings['_ph_ups_specialindicator']:'',
				) );

				woocommerce_wp_checkbox( array(
					'id'			=> '_ph_ups_tobaccoindicator[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label'			=> __( 'Tobacco Indicator', 'ups-woocommerce-shipping' ),
					'description'	=> __( 'Enable this if the package contains Tobacco.', 'ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> !empty($this->var_settings['_ph_ups_tobaccoindicator'])?$this->var_settings['_ph_ups_tobaccoindicator']:'',
				) );

				?></div><?php

				$refrigerationind 	= get_post_meta( $variation->ID, '_ph_ups_refrigeration_var', true );
				$clin 				= get_post_meta( $variation->ID, '_ph_ups_clinicaltrials_var', true );

				if ( empty( $refrigerationind ) ) {
					$refrigerationind = get_post_meta( wp_get_post_parent_id($variation->ID), '_ph_ups_refrigeration', true );
				}

				if ( empty( $clin ) ) {
					$clin = get_post_meta( wp_get_post_parent_id($variation->ID), '_ph_ups_clinicaltrials', true );
				}

				woocommerce_wp_checkbox( array(
					'id' 			=> '_ph_ups_refrigeration_var[' . $variation->ID . ']',
					'class' 		=> 'ph_ups_variation_class_checkbox',
					'label' 		=> __( ' Refrigeration Indicator', 'ups-woocommerce-shipping' ),
					'description' 	=> __( 'Enable this if the package contains an item that needs refrigeration.', 'ups-woocommerce-shipping' ),
					'desc_tip' 		=> 'true',
					'value'			=> $refrigerationind,
				) );

				woocommerce_wp_text_input( array(
					'id'			=> '_ph_ups_clinicaltrials_var[' . $variation->ID .']',
					'class' 		=> 'ph_ups_variation_class_text',
					'label'			=> __( 'Clinical Trials  Id', 'ups-woocommerce-shipping' ),
					'description' 	=> __( 'Unique identifier for clinical trials','ups-woocommerce-shipping' ),
					'desc_tip'		=> 'true',
					'value' 		=> $clin,
				) );
			}

			?></div><?php
		}


		function wf_save_custom_field_ups( $post_id ) {

            if ( isset( $_POST['_wf_ups_deliveryconfirmation'] ) ) {
				update_post_meta( $post_id, '_wf_ups_deliveryconfirmation', esc_attr( $_POST['_wf_ups_deliveryconfirmation'] ) );
			}
             
			// Update the Insurance amount on individual product page
			if( isset($_POST['_wf_ups_custom_declared_value'] ) ) {

				update_post_meta( $post_id, '_wf_ups_custom_declared_value', esc_attr( $_POST['_wf_ups_custom_declared_value'] ) );
			}

			// HST
			if( isset($_POST['_wf_ups_hst'] ) ) {
				update_post_meta( $post_id, '_wf_ups_hst', esc_attr( $_POST['_wf_ups_hst'] ) );
			}

			 // HS Description 
			if( isset($_POST['ph_ups_invoice_desc'] ) ) {
				update_post_meta( $post_id, 'ph_ups_invoice_desc', esc_attr( $_POST['ph_ups_invoice_desc'] ) );
			}

			// Country of Manufacturer
			if( isset($_POST['_ph_ups_manufacture_country'] ) ) {
				update_post_meta( $post_id, '_ph_ups_manufacture_country', esc_attr( $_POST['_ph_ups_manufacture_country'] ) );
			}

			// Update Direct Delivery option
			$is_direct_delivery =  ( isset( $_POST['_wf_ups_direct_delivery'] ) && esc_attr($_POST['_wf_ups_direct_delivery']=='yes')  ) ? esc_attr($_POST['_wf_ups_direct_delivery']) : false;
			update_post_meta( $post_id, '_wf_ups_direct_delivery', $is_direct_delivery );

			// Update Product Address Preference
			if ( isset( $_POST['_ph_ups_product_address_preference'] ) ) {
				update_post_meta( $post_id, '_ph_ups_product_address_preference', esc_attr( $_POST['_ph_ups_product_address_preference'] ) );
			} else {
				update_post_meta( $post_id, '_ph_ups_product_address_preference', '' );
			}
			
			// Update Pre Packed Product
			if ( isset( $_POST['_wf_pre_packed_product'] ) ) {
				update_post_meta( $post_id, '_wf_pre_packed_product', esc_attr( $_POST['_wf_pre_packed_product'] ) );
			} else {
				update_post_meta( $post_id, '_wf_pre_packed_product', '' );
			}

			// NAFTA NetCostCode
			if ( isset( $_POST['_ph_net_cost_code'] ) ) {
				update_post_meta( $post_id, '_ph_net_cost_code', esc_attr( $_POST['_ph_net_cost_code'] ) );
			}

			// NAFTA PreferenceCriteria
			if ( isset( $_POST['_ph_preference_criteria'] ) ) {
				update_post_meta( $post_id, '_ph_preference_criteria', esc_attr( $_POST['_ph_preference_criteria'] ) );
			}

			// NAFTA ProducerInfo
			if ( isset( $_POST['_ph_producer_info'] ) ) {
				update_post_meta( $post_id, '_ph_producer_info', esc_attr( $_POST['_ph_producer_info'] ) );
			}

			// EEI Export Type
			if ( isset( $_POST['_ph_eei_export_type'] ) ) {
				update_post_meta( $post_id, '_ph_eei_export_type', esc_attr( $_POST['_ph_eei_export_type'] ) );
			}

			// EEI Export Information
			if ( isset( $_POST['_ph_eei_export_information'] ) ) {
				update_post_meta( $post_id, '_ph_eei_export_information', esc_attr( $_POST['_ph_eei_export_information'] ) );
			}

			// EEI ScheduleB Number
			if ( isset( $_POST['_ph_eei_schedule_b_number'] ) ) {
				update_post_meta( $post_id, '_ph_eei_schedule_b_number', esc_attr( $_POST['_ph_eei_schedule_b_number'] ) );
			}

			// EEI ScheduleB Unit of Measure
			if ( isset( $_POST['_ph_eei_unit_of_measure'] ) ) {
				update_post_meta( $post_id, '_ph_eei_unit_of_measure', esc_attr( $_POST['_ph_eei_unit_of_measure'] ) );
			}

			// EEI ITAR Exemption Number
			if ( isset( $_POST['_ph_eei_itar_exemption_number'] ) ) {
				update_post_meta( $post_id, '_ph_eei_itar_exemption_number', esc_attr( $_POST['_ph_eei_itar_exemption_number'] ) );
			}

			// EEI DDTC Information UOM
			if ( isset( $_POST['_ph_eei_ddtc_information_uom'] ) ) {
				update_post_meta( $post_id, '_ph_eei_ddtc_information_uom', esc_attr( $_POST['_ph_eei_ddtc_information_uom'] ) );
			}
			
			// EEI ACM Number
			if ( isset( $_POST['_ph_eei_acm_number'] ) ) {
				update_post_meta( $post_id, '_ph_eei_acm_number', esc_attr( $_POST['_ph_eei_acm_number'] ) );
			}
			
			$is_ups_hazardous_material_enable =  ( isset( $_POST['_ph_ups_hazardous_materials']) && !is_array($_POST['_ph_ups_hazardous_materials']) && esc_attr($_POST['_ph_ups_hazardous_materials'])=='yes') ? esc_attr($_POST['_ph_ups_hazardous_materials'])  : (isset( $_POST['_ph_ups_hazardous_materials']) && is_array($_POST['_ph_ups_hazardous_materials']) ? get_post_meta( $post_id, '_ph_ups_hazardous_materials',1) : 'no' );

			update_post_meta( $post_id, '_ph_ups_hazardous_materials', $is_ups_hazardous_material_enable );
			

			if( isset($_POST['_ph_ups_hazardous_materials']) && !is_array($_POST['_ph_ups_hazardous_materials']) )
			{

				$_ph_ups_record_number 			= ( isset($_POST['_ph_ups_record_number']) && !empty($_POST['_ph_ups_record_number']) && !is_array($_POST['_ph_ups_record_number']) ) ? $_POST['_ph_ups_record_number'] :'';

				$_ph_ups_hm_commodity 			= ( isset($_POST['_ph_ups_hm_commodity']) && !empty($_POST['_ph_ups_hm_commodity']) && !is_array($_POST['_ph_ups_hm_commodity']) ) ? $_POST['_ph_ups_hm_commodity'] :'';

				$_ph_ups_commodity_id 			= ( isset($_POST['_ph_ups_commodity_id']) && !empty($_POST['_ph_ups_commodity_id']) && !is_array($_POST['_ph_ups_commodity_id']) ) ? $_POST['_ph_ups_commodity_id'] :'';

				$_ph_ups_class_division_no 		= ( isset($_POST['_ph_ups_class_division_no']) && !empty($_POST['_ph_ups_class_division_no']) && !is_array($_POST['_ph_ups_class_division_no']) ) ? $_POST['_ph_ups_class_division_no'] :'';

				$_ph_ups_package_type 			= ( isset($_POST['_ph_ups_package_type']) && !empty($_POST['_ph_ups_package_type']) && !is_array($_POST['_ph_ups_package_type']) ) ? $_POST['_ph_ups_package_type'] :'';

				$_ph_ups_package_group_type 	= ( isset($_POST['_ph_ups_package_group_type']) && !empty($_POST['_ph_ups_package_group_type']) && !is_array($_POST['_ph_ups_package_group_type']) ) ? $_POST['_ph_ups_package_group_type'] :'';

				$_ph_ups_hm_regulations 		= ( isset($_POST['_ph_ups_hm_regulations']) && !empty($_POST['_ph_ups_hm_regulations']) && !is_array($_POST['_ph_ups_hm_regulations']) ) ? $_POST['_ph_ups_hm_regulations'] :'';

				$_ph_ups_package_instruction_code = ( isset($_POST['_ph_ups_package_instruction_code']) && !empty($_POST['_ph_ups_package_instruction_code']) && !is_array($_POST['_ph_ups_package_instruction_code']) ) ? $_POST['_ph_ups_package_instruction_code'] :'';

				$_ph_ups_shipping_name 			= ( isset($_POST['_ph_ups_shipping_name']) && !empty($_POST['_ph_ups_shipping_name']) && !is_array($_POST['_ph_ups_shipping_name']) ) ? $_POST['_ph_ups_shipping_name'] :'';

				$_ph_ups_technical_name 		= ( isset($_POST['_ph_ups_technical_name']) && !empty ($_POST['_ph_ups_technical_name']) && !is_array($_POST['_ph_ups_technical_name']) ) ? $_POST['_ph_ups_technical_name'] :'';

				$_ph_ups_additional_description = ( isset($_POST['_ph_ups_additional_description']) && !empty($_POST['_ph_ups_additional_description']) && !is_array($_POST['_ph_ups_additional_description']) ) ? $_POST['_ph_ups_additional_description'] :'';

				$_ph_ups_hm_transportaion_mode 	= ( isset($_POST['_ph_ups_hm_transportaion_mode']) && !empty($_POST['_ph_ups_hm_transportaion_mode']) && !is_array($_POST['_ph_ups_record_number']) ) ? $_POST['_ph_ups_hm_transportaion_mode'] :'';

				$hazardous_settings = array(
					'_ph_ups_record_number' 		=> $_ph_ups_record_number,
					'_ph_ups_hm_commodity' 			=> $_ph_ups_hm_commodity,
					'_ph_ups_commodity_id' 			=> $_ph_ups_commodity_id,
					'_ph_ups_class_division_no' 	=> $_ph_ups_class_division_no,
					'_ph_ups_package_type' 			=> $_ph_ups_package_type,
					'_ph_ups_package_group_type' 	=> $_ph_ups_package_group_type,
					'_ph_ups_hm_regulations' 		=> $_ph_ups_hm_regulations,
					'_ph_ups_shipping_name' 		=> $_ph_ups_shipping_name,
					'_ph_ups_technical_name' 		=> $_ph_ups_technical_name,
					'_ph_ups_additional_description'=> $_ph_ups_additional_description,
					'_ph_ups_hm_transportaion_mode' => $_ph_ups_hm_transportaion_mode,
					'_ph_ups_package_instruction_code' => $_ph_ups_package_instruction_code
				);

				update_post_meta( $post_id, '_ph_ups_hazardous_settings', $hazardous_settings );
			}
	
		if ( isset( $_POST['_ph_ups_refrigeration'] ) ) {
				update_post_meta( $post_id, '_ph_ups_refrigeration', esc_attr( $_POST['_ph_ups_refrigeration'] ) );
			} else {
				update_post_meta( $post_id, '_ph_ups_refrigeration', '' );
			}

			if( isset($_POST['_ph_ups_clinicaltrials'] ) ) {
				update_post_meta( $post_id, '_ph_ups_clinicaltrials', esc_attr( $_POST['_ph_ups_clinicaltrials'] ) );
			}

			$is_ups_restricted_material_enable =  ( isset( $_POST['_ph_ups_restricted_article']) && !is_array($_POST['_ph_ups_restricted_article']) && esc_attr($_POST['_ph_ups_restricted_article'])=='yes') ? esc_attr($_POST['_ph_ups_restricted_article'])  : (isset( $_POST['_ph_ups_restricted_article']) && is_array($_POST['_ph_ups_restricted_article']) ? get_post_meta( $post_id, '_ph_ups_restricted_article',1) : 'no' );

			update_post_meta( $post_id, '_ph_ups_restricted_article', $is_ups_restricted_material_enable );

			if ( isset($_POST['_ph_ups_restricted_article']) && !is_array($_POST['_ph_ups_restricted_article']) ) {

				$_ph_ups_diog 				= ( isset($_POST['_ph_ups_diog']) && !empty($_POST['_ph_ups_diog']) && !is_array($_POST['_ph_ups_diog']) ) ? $_POST['_ph_ups_diog'] :'';

				$_ph_ups_alcoholic 			= ( isset($_POST['_ph_ups_alcoholic']) && !empty($_POST['_ph_ups_alcoholic']) && !is_array($_POST['_ph_ups_alcoholic']) ) ? $_POST['_ph_ups_alcoholic'] :'';

				$_ph_ups_perishable 		= ( isset($_POST['_ph_ups_perishable']) && !empty($_POST['_ph_ups_perishable']) && !is_array($_POST['_ph_ups_perishable']) ) ? $_POST['_ph_ups_perishable'] :'';

				$_ph_ups_plantsindicator 	= ( isset($_POST['_ph_ups_plantsindicator']) && !empty($_POST['_ph_ups_plantsindicator']) && !is_array($_POST['_ph_ups_plantsindicator']) ) ? $_POST['_ph_ups_plantsindicator'] :'';

				$_ph_ups_seedsindicator 	= ( isset($_POST['_ph_ups_seedsindicator']) && !empty($_POST['_ph_ups_seedsindicator']) && !is_array($_POST['_ph_ups_seedsindicator']) ) ? $_POST['_ph_ups_seedsindicator'] :'';

				$_ph_ups_tobaccoindicator 	= ( isset($_POST['_ph_ups_tobaccoindicator']) && !empty($_POST['_ph_ups_tobaccoindicator']) && !is_array($_POST['_ph_ups_tobaccoindicator']) ) ? $_POST['_ph_ups_tobaccoindicator'] :'';

				$_ph_ups_specialindicator 	= ( isset($_POST['_ph_ups_specialindicator']) && !empty($_POST['_ph_ups_specialindicator']) && !is_array($_POST['_ph_ups_specialindicator']) ) ? $_POST['_ph_ups_specialindicator'] :'';

				$restricted_settings = array(
					'_ph_ups_diog' 				=> $_ph_ups_diog,
					'_ph_ups_alcoholic' 		=> $_ph_ups_alcoholic,
					'_ph_ups_perishable' 		=> $_ph_ups_perishable,
					'_ph_ups_plantsindicator' 	=> $_ph_ups_plantsindicator,
					'_ph_ups_seedsindicator' 	=> $_ph_ups_seedsindicator,
					'_ph_ups_tobaccoindicator' 	=> $_ph_ups_tobaccoindicator,
					'_ph_ups_specialindicator' 	=> $_ph_ups_specialindicator,

				);

				update_post_meta( $post_id, '_ph_ups_restricted_settings', $restricted_settings );
			}
			
			

		}

		public function ph_ups_save_custom_product_fields_at_variation( $post_id ){

			// Update the Insurance amount on individual product variation
			$custom_declared_value = ( isset($_POST['_ph_ups_custom_declared_value_var'][$post_id]) ) ? $_POST['_ph_ups_custom_declared_value_var'][$post_id] :'';
			update_post_meta( $post_id, '_ph_ups_custom_declared_value_var', $custom_declared_value );

			$checkbox = isset( $_POST['_wf_pre_packed_product_var'][ $post_id ] ) ? 'yes' : 'no';
			update_post_meta( $post_id, '_wf_pre_packed_product_var', $checkbox );

			$refrigeration = isset( $_POST['_ph_ups_refrigeration_var'][ $post_id ] ) ? 'yes' : 'no';
			update_post_meta( $post_id, '_ph_ups_refrigeration_var', $refrigeration );

			$clinic = ( isset($_POST['_ph_ups_clinicaltrials_var'][$post_id]) ) ? $_POST['_ph_ups_clinicaltrials_var'][$post_id] :'';
			update_post_meta( $post_id, '_ph_ups_clinicaltrials_var', $clinic );

            $hstvar = ( isset($_POST['_ph_ups_hst_var'][$post_id]) ) ? $_POST['_ph_ups_hst_var'][$post_id] :'';
			update_post_meta( $post_id, '_ph_ups_hst_var', $hstvar );

			$prdsc = ( isset($_POST['ph_ups_invoice_desc_var'][$post_id]) ) ? $_POST['ph_ups_invoice_desc_var'][$post_id] :'';
			update_post_meta( $post_id, 'ph_ups_invoice_desc_var', $prdsc );
			// Save Hazmat Products
			$hazmat_products =  ( isset( $_POST['_ph_ups_hazardous_materials'][$post_id] ) && esc_attr($_POST['_ph_ups_hazardous_materials'][$post_id])=='yes') ? esc_attr($_POST['_ph_ups_hazardous_materials'][$post_id])  : 'no';

			update_post_meta( $post_id, '_ph_ups_hazardous_materials', $hazmat_products );

			$_ph_ups_var_record_number 			= ( isset($_POST['_ph_ups_record_number'][$post_id]) ) ? $_POST['_ph_ups_record_number'][$post_id] :'';

			$_ph_ups_var_hm_commodity 			= ( isset($_POST['_ph_ups_hm_commodity'][$post_id]) ) ? $_POST['_ph_ups_hm_commodity'][$post_id] :'';

			$_ph_ups_var_commodity_id 			= ( isset($_POST['_ph_ups_commodity_id'][$post_id]) ) ? $_POST['_ph_ups_commodity_id'][$post_id] :'';

			$_ph_ups_var_class_division_no 		= ( isset($_POST['_ph_ups_class_division_no'][$post_id]) ) ? $_POST['_ph_ups_class_division_no'][$post_id] :'';

			$_ph_ups_var_package_type 			= ( isset($_POST['_ph_ups_package_type'][$post_id]) ) ? $_POST['_ph_ups_package_type'][$post_id] :'';

			$_ph_ups_var_package_group_type 	= ( isset($_POST['_ph_ups_package_group_type'][$post_id]) ) ? $_POST['_ph_ups_package_group_type'][$post_id] :'';

			$_ph_ups_var_hm_regulations 		= ( isset($_POST['_ph_ups_hm_regulations'][$post_id]) ) ? $_POST['_ph_ups_hm_regulations'][$post_id] :'';

			$_ph_ups_package_instruction_code 	= ( isset($_POST['_ph_ups_package_instruction_code'][$post_id]) ) ? $_POST['_ph_ups_package_instruction_code'][$post_id] :'';

			$_ph_ups_var_shipping_name 			= ( isset($_POST['_ph_ups_shipping_name'][$post_id]) ) ? $_POST['_ph_ups_shipping_name'][$post_id] :'';

			$_ph_ups_var_technical_name 		= ( isset($_POST['_ph_ups_technical_name'][$post_id]) ) ? $_POST['_ph_ups_technical_name'][$post_id] :'';

			$_ph_ups_var_additional_description = ( isset($_POST['_ph_ups_additional_description'][$post_id]) ) ? $_POST['_ph_ups_additional_description'][$post_id] :'';

			$_ph_ups_var_hm_transportaion_mode 	= ( isset($_POST['_ph_ups_hm_transportaion_mode'][$post_id]) ) ? $_POST['_ph_ups_hm_transportaion_mode'][$post_id] :'';

			$hazardous_settings = array(
				'_ph_ups_record_number' 		=> $_ph_ups_var_record_number,
				'_ph_ups_hm_commodity' 			=> $_ph_ups_var_hm_commodity,
				'_ph_ups_commodity_id' 			=> $_ph_ups_var_commodity_id,
				'_ph_ups_class_division_no' 	=> $_ph_ups_var_class_division_no,
				'_ph_ups_package_type' 			=> $_ph_ups_var_package_type,
				'_ph_ups_package_group_type' 	=> $_ph_ups_var_package_group_type,
				'_ph_ups_hm_regulations' 		=> $_ph_ups_var_hm_regulations,
				'_ph_ups_shipping_name' 		=> $_ph_ups_var_shipping_name,
				'_ph_ups_technical_name' 		=> $_ph_ups_var_technical_name,
				'_ph_ups_additional_description'=> $_ph_ups_var_additional_description,
				'_ph_ups_hm_transportaion_mode' => $_ph_ups_var_hm_transportaion_mode,
				'_ph_ups_package_instruction_code' => $_ph_ups_package_instruction_code
			);

			update_post_meta( $post_id, '_ph_ups_hazardous_settings', $hazardous_settings );
			
			$is_ups_restricted_material_enable =  ( isset( $_POST['_ph_ups_restricted_article'][$post_id] ) && esc_attr($_POST['_ph_ups_restricted_article'][$post_id])=='yes') ? esc_attr($_POST['_ph_ups_restricted_article'][$post_id])  : 'no';

			update_post_meta( $post_id, '_ph_ups_restricted_article', $is_ups_restricted_material_enable );

			$_ph_ups_diog 				= ( isset($_POST['_ph_ups_diog'][$post_id]) ) ? $_POST['_ph_ups_diog'][$post_id] :'';

			$_ph_ups_alcoholic 			= ( isset($_POST['_ph_ups_alcoholic'][$post_id]) ) ? $_POST['_ph_ups_alcoholic'][$post_id] :'';

			$_ph_ups_perishable 		= ( isset($_POST['_ph_ups_perishable'][$post_id]) ) ? $_POST['_ph_ups_perishable'][$post_id] :'';

			$_ph_ups_plantsindicator 	= ( isset($_POST['_ph_ups_plantsindicator'][$post_id]) ) ? $_POST['_ph_ups_plantsindicator'][$post_id] :'';

			$_ph_ups_seedsindicator 	= ( isset($_POST['_ph_ups_seedsindicator'][$post_id]) ) ? $_POST['_ph_ups_seedsindicator'][$post_id] :'';

			$_ph_ups_tobaccoindicator 	= ( isset($_POST['_ph_ups_tobaccoindicator'][$post_id]) ) ? $_POST['_ph_ups_tobaccoindicator'][$post_id] :'';

			$_ph_ups_specialindicator 	= ( isset($_POST['_ph_ups_specialindicator'][$post_id]) ) ? $_POST['_ph_ups_specialindicator'][$post_id] :'';

			$restricted_settings = array(
				'_ph_ups_diog' 				=> $_ph_ups_diog,
				'_ph_ups_alcoholic' 		=> $_ph_ups_alcoholic,
				'_ph_ups_perishable' 		=> $_ph_ups_perishable,
				'_ph_ups_plantsindicator' 	=> $_ph_ups_plantsindicator,
				'_ph_ups_seedsindicator' 	=> $_ph_ups_seedsindicator,
				'_ph_ups_tobaccoindicator' 	=> $_ph_ups_tobaccoindicator,
				'_ph_ups_specialindicator' 	=> $_ph_ups_specialindicator,
			);

			update_post_meta( $post_id, '_ph_ups_restricted_settings', $restricted_settings );
		}
	}
	new WF_UPS_Admin_Options();
}