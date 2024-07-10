<?php

if( ! defined('ABSPATH') )	exit;

// Database Migration for boxes

if( ! class_exists("Ph_Ups_Db_Migration") ) {
	class Ph_Ups_Db_Migration {

        // Class Variables Declaration
        public $dbBoxesMigrated;
        public $upsSettings;
        public $boxes;
        public $savedUnit;
        public $selectedPackaging;
        public $defaultBoxes;
        public $simpleRateBoxes;

		public function __construct() {
			
            
            $this->dbBoxesMigrated      = get_option( "ph_ups_box_db_migrated", false );
            $this->upsSettings          = get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null );
            $this->boxes                = isset( $this->upsSettings['boxes'] ) && !empty( $this->upsSettings['boxes'] ) ? $this->upsSettings['boxes'] : [];
            $this->savedUnit            = isset( $this->upsSettings['units'] ) && !empty( $this->upsSettings['units'] ) ? $this->upsSettings['units'] : 'imperial';
            $this->selectedPackaging    = isset( $this->upsSettings['ups_packaging'] ) && !empty( $this->upsSettings['ups_packaging'] ) ? $this->upsSettings['ups_packaging'] : [];
            $this->defaultBoxes         = [];

            if( ! $this->dbBoxesMigrated ) { 

                $this->ph_ups_migrate_boxes();
            }

        }

        /**
         * Migrate box data in DB
         */
        private function ph_ups_migrate_boxes() {

                if( $this->savedUnit == 'imperial' ) {

                    $this->defaultBoxes = PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_INCHES;
                    $this->simpleRateBoxes = PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_INCHES;

                } else {

                    $this->defaultBoxes = PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_CMS;
                    $this->simpleRateBoxes = PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_CMS;
                }

                $boxesToSave = [];  

                foreach( $this->defaultBoxes as $key => $box ) {

                    if( ! in_array( $box['code'], $this->selectedPackaging ) ) {
                        $box['box_enabled'] = false;
                    }

                    $boxesToSave[$key] = array(
                        'boxes_name'		=> $box['name'],
                        'outer_length'	=> $box['length'],
                        'outer_width'	=> $box['width'],
                        'outer_height'	=> $box['height'],
                        'inner_length'	=> $box['length'],
                        'inner_width'	=> $box['width'],
                        'inner_height'	=> $box['height'],
                        'box_weight'  	=> 0,
                        'max_weight'  	=> $box['weight'],
                        'box_enabled'	=> $box['box_enabled'],
                    );
                    

                }

                // Append previous custom boxes along with default box
                foreach( $this->boxes as $key => $box ) {

                    // Make existing custom boxes as enabled
                    $box['box_enabled'] = true;

                    $boxesToSave[$key] = $box;
                }

                // Append simple rate boxes along with default box
                foreach( $this->simpleRateBoxes as $key => $box ) {

                    $boxesToSave[$key] = array(
                        'boxes_name'		=> $box['name'],
                        'outer_length'	=> $box['length'],
                        'outer_width'	=> $box['width'],
                        'outer_height'	=> $box['height'],
                        'inner_length'	=> $box['length'],
                        'inner_width'	=> $box['width'],
                        'inner_height'	=> $box['height'],
                        'box_weight'  	=> $box['box_weight'],
                        'max_weight'  	=> $box['max_weight'],
                        'box_enabled'	=> $box['box_enabled'],
                    );
                }          

                $this->upsSettings['boxes'] = $boxesToSave;


                update_option( 'woocommerce_'.WF_UPS_ID.'_settings', $this->upsSettings );

			    update_option( "ph_ups_box_db_migrated", true );
    
        }

    }

}
