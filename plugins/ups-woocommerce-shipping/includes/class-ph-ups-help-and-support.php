<?php

if (!defined('ABSPATH')) {
	exit;
}

class PH_UPS_Help_and_Support {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_action( 'wp_ajax_ph_ups_get_ups_log_data', array( $this, 'ph_ups_get_ups_log_data') );
		add_action( 'wp_ajax_ph_ups_submit_support_ticket', array( $this, 'ph_ups_submit_support_ticket') );
	}

	public function ph_ups_get_ups_log_data() {	

		$result 		= array();

		if( class_exists('WC_Log_Handler_File') ){

			$all_log_file_list 		= WC_Log_Handler_File::get_log_files();
			$log_directory 			= 'wp-content/uploads/wc-logs/';
			$get_latest_log_file	= strtolower(PH_UPS_DEBUG_LOG_FILE_NAME) . '-' . date( 'Y-m-d' );

			$is_log_available	= false;
			foreach ( $all_log_file_list as $log_file ) {
				if ( str_contains( strtolower( $log_file ), $get_latest_log_file ) ) {
					$is_log_available = true;
					$get_latest_log_file = $log_file;
					break;
				}
			}

			if( !empty($get_latest_log_file) && !empty($all_log_file_list) && $is_log_available ) {

				$log_file_path = ABSPATH.$log_directory.$get_latest_log_file;

				$file_content = file_get_contents($log_file_path);

				if( !empty($file_content) ) {

					$result		= array(
						'status'	=>	true,
						'code'		=>	200,
						'message'	=>	"Success",
						'file_path'	=>	$log_file_path,
					);

				} else {

					$result		= array(
						'status'	=>	false,
						'code'		=>	500,
						'message'	=>	"<br/>Oops! The Diagnostic Report is empty.<br/>Please read the instructions and try again.",
						'file_path'	=>	$log_file_path,
					);
				}

			} else {

				$result		= array(
					'status'	=>	false,
					'code'		=>	500,
					'message'	=>	"<br/>Oops! The Diagnostic Report is empty.<br/>Please read the instructions and try again.",
					'file_path'	=>	'',
				);
			}

		} else {

			$result		= array(
				'status'	=>	false,
				'code'		=>	500,
				'message'	=>	"WooCommerce Log Handler class doesn't exists",
				'file_path'	=>	'',
			);

		}
		
		echo print_r( json_encode($result),true);

		exit;
	}

	public function ph_ups_submit_support_ticket() {

		$attachments 	= array();
		$ticket_num 	= isset($_POST['ticket_num']) ? $_POST['ticket_num'] 	 : '';
		$log_file 		= isset($_POST['log_file']) ? $_POST['log_file'] 	 : '';
		$headers 		= array('Content-Type: text/html; charset=UTF-8');
		$to_email		= 'support@pluginhive.zendesk.com';
		$site_url		= get_home_url();
		$ups_settings 	= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null );

		// Add Log file as Attachment
		$attachments[]	= $log_file;

		// Create file of UPS Settings
		$setting_file 		= WP_CONTENT_DIR."/uploads/PluginHive-UPS-Settings.txt";
		file_put_contents( $setting_file, print_r($ups_settings, true) );

		// Add Settings file as Attachment
		$attachments[]	= $setting_file;

		$subject 		= 'UPS Settings & Debug Info';
		
		$content 		=	'Hi Support Team
							<br/><br/>
							Reference Ticket Number: <a href="https://pluginhive.zendesk.com/agent/tickets/'.$ticket_num.'">'.$ticket_num.'</a>
							<br/><br/>
							Customer Website URL: '.$site_url.'
							<br/><br/>';

		if( defined( 'PH_UPS_PLUGIN_VERSION' ) ) {

			$content 	.= 'UPS Plugin Version: '.PH_UPS_PLUGIN_VERSION.'<br/><br/>';
		}

		wp_mail( $to_email, $subject, $content, $headers, $attachments );

		unlink($setting_file);

		$result		= array( 'status'	=>	true );

		echo print_r( json_encode($result),true);

		exit;
	}

}

new PH_UPS_Help_and_Support();