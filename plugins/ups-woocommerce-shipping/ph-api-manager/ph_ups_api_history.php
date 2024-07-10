<?php

class PH_UPS_Api_History {

    public $ph_ups_api_history_tablename;

    public function __construct(){

        $this->ph_ups_api_history_tablename = 'ph_ups_api_key_history';

        add_action('admin_notices', array($this, 'ph_ups_licence_key_expire_notice'));
    }

    /**
     * Create UPS Api History table 
     */
    public function ph_create_ups_api_history_table() {
        global $wpdb;

        $tablename = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        $sql = "CREATE TABLE IF NOT EXISTS $tablename (
                id bigint(20) UNSIGNED AUTO_INCREMENT,
                master_key varchar(50) NOT NULL,
                product_order_key varchar(50) NOT NULL,
                product_id varchar(50) NOT NULL,
                access_granted varchar(191) NOT NULL,
                access_expired varchar(191) NOT NULL,
                PRIMARY KEY (product_order_key),
                KEY id (id)
            );";

        if ( !function_exists('dbDelta') ) {

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }

        dbDelta( $sql );
    }

    /**
     * Insert data into the UPS Api History table 
     * 
     * 
	 * @param array $activate_results
	 * @return bool
     */
    public function ph_insert_data_to_ups_api_history_table( $activate_results ) {

        if ( !$this->ph_ups_api_history_table_exists() ) {
            $this->ph_create_ups_api_history_table();
        }

        global $wpdb;

        $tablename          = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        // Prepare the query to check if the data already exists
        $existing_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tablename WHERE product_order_key = %s",$activate_results['data']['product_order_api_key']
            )
        );

        // Check if any matching records were found
        if (!$existing_data) {

            // Data does not exist, proceed with insertion
            $wpdb->insert(
                $tablename,
                array(
                    'master_key'        => $activate_results['data']['master_api_key'],
                    'product_order_key' => $activate_results['data']['product_order_api_key'],
                    'product_id'        => '3335',
                    'access_granted'    => $activate_results['data']['access_granted'],
                    'access_expired'    => $activate_results['data']['access_expires'],
                )
            );
        } else {
            // Data already exists, proceed with update
            $wpdb->update(
                $tablename,
                array(
                    'master_key'        => $activate_results['data']['master_api_key'],
                    'product_order_key' => $activate_results['data']['product_order_api_key'],
                    'product_id'        => '3335',
                    'access_granted'    => $activate_results['data']['access_granted'],
                    'access_expired'    => $activate_results['data']['access_expires'],
                ),
                array('product_order_key' => $activate_results['data']['product_order_api_key'])
            );
        }

        delete_option('ph_ups_closed_api_key_expire_notice');
    }

    /**
     * Get Last Activation Details from DB
     * 
	 * @return bool|object
     */
    public function ph_get_last_activation_details() {

        if ( !$this->ph_ups_api_history_table_exists() ) {
            
            return null;
        }

        global $wpdb;

        $tablename          = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        // Get the last activation details
        $last_activation_data = $wpdb->get_row(
            "SELECT * 
            FROM $tablename
            ORDER BY id DESC
            LIMIT 1 OFFSET 1"
        );
        
        return $last_activation_data;
    }

    /**
     * Check UPS Api History table exist or not
     * 
     * @return bool
     */
    public function ph_ups_api_history_table_exists() {

        global $wpdb;

        $table_name = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ) {
            return true;
        }
        return false;
    }

    /**
     * Show the UPS Api History table's data
     */
    public function ph_fetch_ups_api_history_table_data() {

        global $wpdb;

        $table_name = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        $table_data = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY id DESC"
        );

        if ( !empty($table_data) ) {
            ?>
            <style>
                .ph-ups-api-history td,.ph-ups-api-history th {
                    padding: 10px;
                }
            </style>
            <h3><?php echo __(' API History ','ups-woocommerce-shipping'); ?> </h3>
            <table class="wp-list-table widefat striped ph-ups-api-history">
                <thead>
                <tr>
                    <th><?php echo __('ID','ups-woocommerce-shipping'); ?></th>
                    <th><?php echo __('Product Order Key','ups-woocommerce-shipping'); ?></th>
                    <th><?php echo __('Product Id','ups-woocommerce-shipping'); ?></th>
                    <th><?php echo __('Access Granted Date','ups-woocommerce-shipping'); ?></th>
                    <th><?php echo __('Access Expiry Date','ups-woocommerce-shipping'); ?></th>
                </tr>
                </thead>
            <?php 

            $date_format = get_option( 'date_format' );

            foreach ($table_data as $row) {

                $access_granted = date($date_format, $row->access_granted);
                $access_expired = (0 == $row->access_expired) ? ( $row->access_granted + YEAR_IN_SECONDS ) : $row->access_expired;
                $access_expired = is_numeric($access_expired) ? date($date_format, $access_expired) : $access_expired;

                // Access row properties
                echo '<tr><td>' . $row->id . '</td>';
                echo '<td>' . $row->product_order_key . '</td>';
                echo '<td>' . $row->product_id . '</td>';
                echo '<td>' . $access_granted  . '</td>';
                echo '<td>' . $access_expired  . '</td></tr>';
            }

            echo '</table>';
        }
    }

    /**
     * Licence expire notice
     */
    public function ph_ups_licence_key_expire_notice() {

        if (isset($_POST['ph_ups_close_api_expire_notice'])) {

            update_option('ph_ups_closed_api_key_expire_notice', strtotime(date('Y-m-d')));
        }

        $notice_closed = get_option('ph_ups_closed_api_key_expire_notice', false);

        // History table exists check and Notice closed for the day
        if ( (!empty($notice_closed) && $notice_closed == strtotime(date('Y-m-d'))) || !$this->ph_ups_api_history_table_exists() ) {
            
            return;
        }

        global $wpdb;

        $activated_api_key = get_option('ph_client_ups_product_order_api_key');

        $table_name = $wpdb->prefix.$this->ph_ups_api_history_tablename;

        $table_data = $wpdb->get_results(
            "SELECT access_expired FROM {$table_name} where product_order_key = '{$activated_api_key}'"
        );

        if ( empty($table_data) ) {

            return;
        }

        $expired_date = current($table_data)->access_expired;

        if ( empty($expired_date) ) {

            return;
        }

        if ( strtotime(date('d-m-Y')) <= $expired_date ) {
            
            $today = new DateTime('now');

            // Convert the timestamp to a DateTime object
            $compareDate = new DateTime();
            $compareDate->setTimestamp($expired_date);

            // Calculate the difference in days
            $interval = $today->diff($compareDate);

            $remaining_days = abs($interval->days);

            // Checking hour and minute left to expire API Key
            if ( $interval->h > 0 || $interval->i > 0 ) {
                
                $remaining_days++;
            }
        }
        
        $notice_heading = '';
        $notice_message = '';

        if ( $remaining_days > 15 && $remaining_days < 31 ) {

            $notice_heading = __('Gentle Reminder', 'ups-woocommerce-shipping');
            $notice_message = __("This is a friendly reminder that your <b>WooCommerce UPS Shipping Plugin with Print Label</b> plugin subscription expires in <b>".$remaining_days." days</b>. To ensure uninterrupted shipping functionality, we recommend renewing your subscription at your earliest convenience.", 'ups-woocommerce-shipping');

        } else if ( $remaining_days > 7 && $remaining_days < 16 ) {
            
            $notice_heading = __('Timely Renewal Recommended', 'ups-woocommerce-shipping');
            $notice_message = __("We kindly advise that your <b>WooCommerce UPS Shipping Plugin with Print Label</b> plugin subscription is set to expire in <b>".$remaining_days." days</b>. To avoid any disruptions to your store's shipping process, we recommend renewing your subscription promptly.", 'ups-woocommerce-shipping');
        } else if ( $remaining_days > 0 && $remaining_days < 8 ) {
            
            $notice_heading = __('Urgent Action Required', 'ups-woocommerce-shipping');
            $notice_message = __("<b>Attention!</b> Your <b>WooCommerce UPS Shipping Plugin with Print Label</b> plugin subscription expires in <b>".$remaining_days." days. Immediate renewal is strongly recommended</b> to avoid any interruption to your store's shipping functionality and ensure continued access to UPS shipping services.", 'ups-woocommerce-shipping');
        } else if ( $remaining_days < 1 ) {
            
            $notice_heading = __('Your <b>WooCommerce UPS Shipping Plugin with Print Label</b> Plugin License Has Expired', 'ups-woocommerce-shipping');
            $notice_message = __("We regret to inform you that your <b>WooCommerce UPS Shipping Plugin with Print Label</b> plugin license has <b>expired</b>. This means UPS shipping functionalities are currently unavailable. To restore them and continue offering your customers a seamless shipping experience, please renew your subscription at your earliest convenience.", 'ups-woocommerce-shipping');
        }

        if ( !empty($notice_message) ) {

            $ups_logo_url = plugins_url('ups-woocommerce-shipping') . '/resources/images/ph-ups-dap-logo.jpg';
            ?>

            <div class="notice notice-warning" id="ph-ups-api-key-expire-notice">
                <div class="ph-ups-api-notice-logo">
                    <img src="<?php echo $ups_logo_url ?>"/>
                </div>

                <form method="post" class="ph-ups-api-key-notice-close-form">
                    <input type="hidden" name="ph_ups_close_api_expire_notice" value="true">
                    <button id="ph-ups-api-key-expire-close-notice">&times;</button>
                </form>
                
                <div class="ph-ups-api-key-expire-notice-details">
                    <p>
                    <strong><?php echo $notice_heading; ?></strong>
                    </p>
                    <p><?php echo $notice_message; ?></p>
                    <a href="https://www.pluginhive.com/my-account/" class="wc-update-now button-primary" target="_blank"><?php echo __("Renew Subscription",'ups-woocommerce-shipping'); ?></a>
                    <a href="https://www.pluginhive.com/support/" class="button-secondary" target="_blank"><?php echo __("Contact Our Team",'ups-woocommerce-shipping'); ?></a>
                </div>
                <div style="clear: both;"></div>
            </div>
            <?php
        }
       
        ?>
        <?php

    }
}