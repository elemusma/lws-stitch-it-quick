<?php

if (!defined('ABSPATH'))    exit;

if (!class_exists("PH_UPS_WP_Post_Table_Label_Migration")) {

    class PH_UPS_WP_Post_Table_Label_Migration
    {

        public static $migration_limit  = "100";
        public static $post_table_name  = "posts";
        public static $meta_table_name  = "postmeta";
        public static $post_meta_key    = "ups_label_details_array";

        private static $ph_migration_banner_option  = "ph_ups_display_migration_banner";
        private static $ph_label_migration_option   = "ph_ups_label_migration_data";

        private static $ph_label_migration = [
            'started'               => 'no',
            'fetched_data'          => 'no',
            'table_prepared'        => 'no',
            'completed'             => 'no',
            'partial'               => 'no',
            'order_ids'             => [],
            'order_ids_left'        => [],
            'order_ids_migrated'    => [],
            'invalid_order_ids'     => [],
        ];

        public $ph_wp_postmeta_label_migrated;
        public $ph_display_migration_banner;
        public $ph_wp_postmeta_migration_data;
        public $ph_label_orders_data;

        public function __construct()
        {
            $this->ph_wp_postmeta_label_migrated    = get_option(PH_UPS_LABEL_MIGRATION_OPTION, false);
            $this->ph_display_migration_banner      = get_option(self::$ph_migration_banner_option, "yes");
            $this->ph_wp_postmeta_migration_data    = get_option(self::$ph_label_migration_option, self::$ph_label_migration);

            // Cron 
            add_action('ph_ups_wp_post_table_label_migration', [$this, 'ph_ups_run_label_migration']);

            // Ajax
            add_action('wp_ajax_ph_ups_closing_migration_banner', [$this, 'ph_ups_close_migration_info_banner'], 10);

            // Add Banner
            if ($this->ph_display_migration_banner == "yes") {

                add_action('admin_notices', [$this, 'ph_ups_add_migration_info_banner']);
            }

            // Start Label Migration
            if ($this->ph_ups_is_migration_required()) {

                add_action('wp_loaded', [$this, 'ph_ups_migrate_postmeta_ups_labels']);
            }
        }

        /**
         * Add Banner for UPS 5.0.0 Changes
         * 
         */
        function ph_ups_add_migration_info_banner()
        {

            $total_orders           = is_array($this->ph_wp_postmeta_migration_data) ? count($this->ph_wp_postmeta_migration_data['order_ids']) : 0;
            $total_orders_left      = is_array($this->ph_wp_postmeta_migration_data) ? count($this->ph_wp_postmeta_migration_data['order_ids_left']) : 0;
            $total_orders_migrated  = is_array($this->ph_wp_postmeta_migration_data) ? count($this->ph_wp_postmeta_migration_data['order_ids_migrated']) : 0;
            $migration_progress     = $total_orders_left == 0 ? "Completed" : "In Progress";
?>
            <div class="notice ph-ups-notice-banner">
                <h3><strong>&#127881; WooCommerce UPS Shipping Plugin Update 5.0.0 &#128640;</strong></h3>
                <p>Exciting news!<br /> Our plugin now fully supports <a href='https://www.pluginhive.com/woocommerce-hpos/?utm_source=plugin&utm_medium=dash&utm_id=ups_hpos' target='_blank'>WooCommerce's HPOS (High Performance Order Storage)</a> feature!</p>
                <p>With this update, we've introduced a dedicated storage system specifically designed to store shipping-related data for new orders.<br /> To ensure backward compatibility, we'll be automatically syncing past orders up to the last year in the background. The order sync will run automatically and will be completed within some time.</p>

                <div class="ph-ups-view-progress ph-ups-view-symbol">Migration Progress</div>
                <div class="ph-ups-progress-details" style="display: none;">
                    <p>&#x1F4CA; Total Orders: <?= $total_orders; ?></p>
                    <p>&#x23F3; Orders Pending: <?= $total_orders_left; ?></p>
                    <p>&#x2705; Orders Completed: <?= $total_orders_migrated; ?></p>
                    <p>&#8505; Status: <b><?= $migration_progress; ?></b></p>
                </div>

                <button class="ph-ups-close-migration-banner ph-ups-close-notice"><?php _e('Close', 'ups-woocommerce-shipping') ?></button>
                <button class="ph-ups-contact-us"><a href="https://www.pluginhive.com/support/" target='_blank'><?php _e('Contact Us', 'ups-woocommerce-shipping') ?></a></button>
            </div>
<?php
        }

        /**
         * Close Banner for UPS 5.0.0 Changes
         * 
         */
        public function ph_ups_close_migration_info_banner()
        {

            update_option(self::$ph_migration_banner_option, "no");

            wp_die(json_encode(true));
        }

        /**
         * Check if migration is required
         *
         * @return bool
         */
        private function ph_ups_is_migration_required()
        {

            if ($this->ph_wp_postmeta_label_migrated || $this->ph_wp_postmeta_migration_data['completed'] == 'yes') {

                return false;
            }

            return true;
        }

        /**
         * Migrate UPS Labels from WP Post Meta to PH Custom Table
         * 
         */
        public function ph_ups_migrate_postmeta_ups_labels()
        {

            // Schedule label migration if order ids left
            if (!empty($this->ph_wp_postmeta_migration_data['order_ids_left']) && !wp_next_scheduled('ph_ups_wp_post_table_label_migration')) {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Scheduling Migration for the remaining Orders ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($this->ph_wp_postmeta_migration_data, 1), true);

                $this->ph_schedule_ups_migration_event();

                return;
            }

            // Fetch Orders from DB for which label is already created
            if ($this->ph_wp_postmeta_migration_data['fetched_data'] != 'yes') {

                $this->ph_get_ups_labels_order_data();

                // Return if no Orders found with Label Data
                if (empty($this->ph_label_orders_data)) {

                    $this->ph_wp_postmeta_migration_data['started'] = $this->ph_wp_postmeta_migration_data['completed'] = 'yes';

                    update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
                    update_option(PH_UPS_LABEL_MIGRATION_OPTION, true);

                    return;
                }

                $this->ph_filter_ups_orders();
            }

            // Return if no order ids found
            if (empty($this->ph_wp_postmeta_migration_data['order_ids'])) {
                return;
            }

            // Return if no data is fetched
            if ($this->ph_wp_postmeta_migration_data['fetched_data'] != 'yes') {
                return;
            }

            if (!wp_next_scheduled('ph_ups_wp_post_table_label_migration')) {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Migration Data ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($this->ph_wp_postmeta_migration_data, 1), true);

                $this->ph_schedule_ups_migration_event();
            }
        }

        /**
         * Filter invalid Orders
         * 
         */
        private function ph_filter_ups_orders()
        {
            $order_ids          = [];

            foreach ($this->ph_label_orders_data as $order_data) {

                $order_id = $order_data->post_id;

                $order_ids[] = $order_id;
            }

            if (empty($order_ids)) {

                $this->ph_wp_postmeta_migration_data['order_ids'] = $this->ph_wp_postmeta_migration_data['order_ids_left'] = [];
                $this->ph_wp_postmeta_migration_data['fetched_data'] = $this->ph_wp_postmeta_migration_data['started'] = $this->ph_wp_postmeta_migration_data['completed'] = 'yes';

                update_option(PH_UPS_LABEL_MIGRATION_OPTION, true);
            } else {

                $this->ph_wp_postmeta_migration_data['order_ids'] = $this->ph_wp_postmeta_migration_data['order_ids_left'] = $order_ids;
                $this->ph_wp_postmeta_migration_data['fetched_data'] = 'yes';
            }

            update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
        }

        /**
         * Schedule Migration
         * 
         */
        private function ph_schedule_ups_migration_event()
        {
            try {

                $start_time_stamp = strtotime("now +5 minutes");
                wp_schedule_single_event($start_time_stamp, 'ph_ups_wp_post_table_label_migration');

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- UPS Label Migration Cron : Time $start_time_stamp ---#", true);
            } catch (Exception $e) {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- UPS Label Migration Cron Scheduling Error ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    true
                );
            }
        }

        /**
         * Run DB Migration
         * 
         */
        public function ph_ups_run_label_migration()
        {

            try {

                $this->ph_wp_postmeta_migration_data['started'] = 'yes';

                $order_ids          = $this->ph_wp_postmeta_migration_data['order_ids'];
                $migrated_order_ids = $this->ph_wp_postmeta_migration_data['order_ids_migrated'];

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Started Label Migration ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($this->ph_wp_postmeta_migration_data, 1), true);

                $order_ids_migrated     = array_values(array_intersect($order_ids, $migrated_order_ids));
                $order_ids_to_migrate   = $order_ids_left = array_values(array_diff($order_ids, $migrated_order_ids));

                // Partially Updated
                if (count($order_ids_migrated) > 0) {

                    $this->ph_wp_postmeta_migration_data['partial']             = 'yes';
                    $this->ph_wp_postmeta_migration_data['order_ids_migrated']  = array_values(array_unique(array_merge($migrated_order_ids, $order_ids_migrated)));
                    $this->ph_wp_postmeta_migration_data['order_ids_left']      = $order_ids_left;

                    update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
                }

                // Migration Complete
                if (count($order_ids_to_migrate) == 0) {

                    $this->ph_wp_postmeta_migration_data['partial']             = 'no';
                    $this->ph_wp_postmeta_migration_data['completed']           = 'yes';
                    $this->ph_wp_postmeta_migration_data['order_ids_migrated']  = array_values(array_unique(array_merge($migrated_order_ids, $order_ids_migrated)));
                    $this->ph_wp_postmeta_migration_data['order_ids_left']      = [];

                    update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
                    update_option(PH_UPS_LABEL_MIGRATION_OPTION, true);
                } else {

                    $ph_internal_meta_keys  = PH_UPS_WC_Storage_Handler::$ph_internal_meta_keys;
                    
                    $invalid_order_ids      = [];

                    foreach ($order_ids_to_migrate as $key => $order_id) {

                        if ($key == self::$migration_limit) {

                            break;
                        }

                        if (!empty($order_id)) {

                            $order = wc_get_order($order_id);

                            if ( !$order instanceof WC_Order ) {

                                $invalid_order_ids[] = $order_id;

                                continue;
                            }

                            $storage_handler    = new PH_UPS_WC_Storage_Handler($order);

                            foreach ($ph_internal_meta_keys as $meta_key) {

                                // Get Meta Data from Legacy Post Table for Orders
                                $meta_value = get_post_meta($order_id, $meta_key, true);

                                if (!empty($meta_value)) {

                                    $result = $storage_handler->ph_add_meta($meta_key, $meta_value);

                                    if (!$result) {

                                        Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Update failed for the Order #$order_id: Meta Key $meta_key  ---#", true);
                                    }
                                }
                            }

                            $order_ids_migrated[] = $order_id;
                        }
                    }
                }

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Invalid Order Ids ---#", true);

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($invalid_order_ids, 1), true);

                $this->ph_wp_postmeta_migration_data['invalid_order_ids'] = array_unique(
                    array_merge(
                      $this->ph_wp_postmeta_migration_data['invalid_order_ids'],
                      $invalid_order_ids
                    )
                );

                // Migration Complete
                if ((count($order_ids_migrated) > 0) && !(array_diff($order_ids, $order_ids_migrated))) {

                    $this->ph_wp_postmeta_migration_data['partial']             = 'no';
                    $this->ph_wp_postmeta_migration_data['completed']           = 'yes';
                    $this->ph_wp_postmeta_migration_data['order_ids_migrated']  = array_values(array_unique(array_merge($migrated_order_ids, $order_ids_migrated)));
                    $this->ph_wp_postmeta_migration_data['order_ids_left']      = [];

                    update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
                    update_option(PH_UPS_LABEL_MIGRATION_OPTION, true);
                }

                if (array_diff($order_ids, $order_ids_migrated)) {

                    $this->ph_wp_postmeta_migration_data['order_ids_left']      = array_diff($order_ids, $order_ids_migrated);
                    $this->ph_wp_postmeta_migration_data['order_ids_migrated']  = array_values(array_unique(array_merge($migrated_order_ids, $order_ids_migrated)));
                    $this->ph_wp_postmeta_migration_data['partial']             = 'yes';
                    $this->ph_wp_postmeta_migration_data['completed']           = 'no';
                }

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Label Migration End ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($this->ph_wp_postmeta_migration_data, 1), true);

                update_option(self::$ph_label_migration_option, $this->ph_wp_postmeta_migration_data);
                // Re-display migration banner
                update_option(self::$ph_migration_banner_option, "yes");
            } catch (Exception $e) {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- UPS Label Migration Cron Error ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    true
                );
            }
        }

        /**
         * Get Orders from DB for UPS Labels
         */
        private function ph_get_ups_labels_order_data()
        {
            global $wpdb;

            $post_table = $wpdb->prefix . self::$post_table_name;
            $meta_table = $wpdb->prefix . self::$meta_table_name;
            $last_year  = gmdate('Y-m-d 00:00:00', strtotime("-1 year"));

            try {

                $orders_list = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT DISTINCT MetaTable.post_id 
                        FROM {$meta_table} as MetaTable
                        JOIN {$post_table} as PostTable ON PostTable.ID = MetaTable.post_id AND PostTable.post_type = 'shop_order'
                        WHERE MetaTable.meta_key = %s AND PostTable.post_modified_gmt >= %s
                        ORDER BY MetaTable.meta_id",
                        self::$post_meta_key,
                        $last_year
                    )
                );

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Last one year Orders for Successful Shipments ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($orders_list, 1), true);

                $this->ph_label_orders_data = $orders_list;
            } catch (Exception $e) {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#--- Orders fetching Failed ---#", true);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    true
                );
            }
        }
    }

    new PH_UPS_WP_Post_Table_Label_Migration();
}
