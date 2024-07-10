<?php
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	$current_tab = (!empty($_GET['subtab'])) ? esc_attr($_GET['subtab']) : 'general';

	echo '
		<style>
			a.nav-tab{
				cursor: default;
			}
			.nav-tab-active{
				height: 24px;
			}
		</style>
		<hr class="wp-header-end">';

	$tabs = array(
		'general'			=> __("General", 'ups-woocommerce-shipping'),
		'rates'				=> __("Rates & Services", 'ups-woocommerce-shipping'),
		'labels'			=> __("Shipping Labels", 'ups-woocommerce-shipping'),
		'int_forms'			=> __("International Forms", 'ups-woocommerce-shipping'),
		'spl_services'		=> __("Special Services", 'ups-woocommerce-shipping'),
		'packaging'			=> __("Packaging", 'ups-woocommerce-shipping'),
		'freight'			=> __("Freight", 'ups-woocommerce-shipping'),
		'pickup'			=> __("Pickup", 'ups-woocommerce-shipping'),
		'advanced_settings'	=> __("Advanced", 'ups-woocommerce-shipping'),
		'help'				=> __("Help & Support", 'ups-woocommerce-shipping'),
	);

	if ( Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() ) {

		unset($tabs['freight']);
	}

	$html = '<h2 class="nav-tab-wrapper">';

	foreach ($tabs as $stab => $name) {
		$class = ($stab == $current_tab) ? 'nav-tab-active' : '';
		$html .= '<a style="text-decoration:none !important;" class="nav-tab ph-ups-tabs ' . $class . " tab_" . $stab . '" >' . $name . '</a>';
	}

	$html .= '</h2>';

	echo $html;