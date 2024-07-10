<?php

if (!defined('ABSPATH')) {
    exit;
}

class PH_UPS_Document_Upload_Rest {

    // Class Variables Declaration
    public $upsUserId;
    public $upsPassword;
    public $upsAccessKey;
    public $upsShipperNumber;
    public $apiMode;
    public $debugEnabled;

    public function __construct() {

        $upsSettings            = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
        $this->upsUserId        = (isset($upsSettings['user_id']) && !empty($upsSettings['user_id'])) ? $upsSettings['user_id'] : '';
        $this->upsPassword      = (isset($upsSettings['password']) && !empty($upsSettings['password'])) ? $upsSettings['password'] : '';
        $this->upsAccessKey     = (isset($upsSettings['access_key']) && !empty($upsSettings['access_key'])) ? $upsSettings['access_key'] : '';
        $this->upsShipperNumber = (isset($upsSettings['shipper_number']) && !empty($upsSettings['shipper_number'])) ? $upsSettings['shipper_number'] : '';
        $this->apiMode          = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode'])) ? $upsSettings['api_mode'] : 'Test';
        $this->debugEnabled     = (isset($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;
    }

    /**
     * Upload document
     */
    public function ph_ups_upload_document() {

        // Get attachments from the WP Media chooser
        $documentId                 = '';
        $errorMessage               = '';
        $response                   = '';
        $uploadedDocumentDetails    = [];
        $attachment                 = (isset($_POST['attachment']) && !empty($_POST['attachment'])) ? $_POST['attachment'] : '';
        $docType                    = (isset($_POST['docType']) && !empty($_POST['docType'])) ? $_POST['docType'] : '';
        $orderId                    = (isset($_POST['orderId']) && !empty($_POST['orderId'])) ? $_POST['orderId'] : '';

        // Return if no attachment found
        if (empty($attachment)) {
            return;
        }

        $uploadDocData = wp_json_encode($this->create_ups_upload_doc_request($orderId, $attachment, $docType), JSON_UNESCAPED_SLASHES);

        //Check for active license
        if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
            $this->admin_diagnostic_report("------------------------------- UPS Document Upload -------------------------------");
            $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");
            return;
        } else {

            $api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

            if (!$api_access_details) {
                return;
            }

            $endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/document-paperless/documents');

            $headers = array(
                'shippernumber'  => $this->upsShipperNumber
            );

            $response = Ph_Ups_Api_Invoker::phCallApi(
                PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
                $api_access_details['token'],
                $uploadDocData,
                $headers
            );
        }

        // In case of any issues with remote post.
        if (is_wp_error($response) && is_object($response)) {
            $error_message = $response->get_error_message();
            wf_admin_notice::add_notice('Order #' . $orderId . ': Sorry. Something went wrong: ' . $error_message);
        }

        $response_obj  = json_decode($response['body'], true);

        // It is an error response.
        if (isset($response_obj['response']['errors'])) {

            $error_code = (string)$response_obj['response']['errors'][0]['code'];
            $error_desc = (string)$response_obj['response']['errors'][0]['message'];

            $message = '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']';

            wf_admin_notice::add_notice('Order #' . $orderId . ': ' . $message);

            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Upload REST Fault ---------------');
                $this->admin_diagnostic_report($error_desc);
            }

            return false;
        }

        // Debug logs for Upload requests & response
        if ($this->debugEnabled) {

            $this->admin_diagnostic_report('--------------- UPS Document Upload Request ---------------');
            $this->admin_diagnostic_report(wp_json_encode($uploadDocData));

            $this->admin_diagnostic_report('--------------- UPS Document Upload Response ---------------');
            $this->admin_diagnostic_report($response['body']);
        }

        // Get label details
        $isLabelGeneratedOrder      = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_rest_label_details_array');

        $response       = json_decode($response['body'], true);
        $uploadDocData  = json_decode($uploadDocData, true);
        
        // Handle document upload response
        if (isset($response['UploadResponse']['Response']['ResponseStatus']['Code']) && isset($response['UploadResponse']['Response']['ResponseStatus']['Description']) && $response['UploadResponse']['Response']['ResponseStatus']['Description'] == 'Success') {

            $documentId = is_array($response['UploadResponse']['FormsHistoryDocumentID']['DocumentID']) ? current($response['UploadResponse']['FormsHistoryDocumentID']['DocumentID']) : $response['UploadResponse']['FormsHistoryDocumentID']['DocumentID'];

            $uploadedDocumentDetails = [
                'success'               => true,
                'documentID'            => $documentId,
                'fileName'              => $uploadDocData['fileName'],
                'docType'               => $uploadDocData['docType'],
                'uploadType'            => !empty($isLabelGeneratedOrder) ? 'Post Upload' : 'Pre Upload',
                'uploadDatetime'        => $uploadDocData['uploadDateTime']
            ];

            // Push to image respository if label is already generated ( Post Upload )            
            $this->ph_ups_push_to_image_repository($orderId, $documentId, $uploadedDocumentDetails);

            wp_die(json_encode($uploadedDocumentDetails));
        } else {

            // Add error notice and return false to ajax call
            WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while Uploading UPS Document. ' . $error_message . ' Please check the logs for more details.'));
            wp_die(json_encode(['success' => false]));
        }
    }

    /**
     * Create upload document request
     *
     * @param mixed $orderId
     * @param array $attachment
     * @param mixed $docType
     * @return array $uploadDocData
     */
    public function create_ups_upload_doc_request($orderId, $attachment, $docType) {
        $json_uploadDocRequest = array();
        $json_uploadDocData = array();
        $fileUrl            = wp_upload_dir();
        $documentUrl        = $_SERVER['DOCUMENT_ROOT'] . parse_url($attachment['url'], PHP_URL_PATH);
        $fileUrl            = $documentUrl;
        $formFIle           = (base64_encode(file_get_contents($fileUrl)));
        $fileName           = isset($attachment['filename']) ? $attachment['filename'] : '';
        $fileType           = isset($attachment['subtype']) ? $attachment['subtype'] : '';

        // Json Request
        $json_uploadDocRequest = array(
            'Request'       =>  array(
                'TransactionReference'  =>  array(
                    'CustomerContext'   =>  $orderId,
                    //@note 'TransactionIdentifier'  => 'Pluginhive Upload Document Request' missing
                )
            ),
            'ShipperNumber'     =>  $this->upsShipperNumber,
            'UserCreatedForm'   =>  array(
                'UserCreatedFormFileName' => $fileName,
                'UserCreatedFormFile' => $formFIle,
                'UserCreatedFormFileFormat' => $fileType,
                'UserCreatedFormDocumentType' => $docType
            )
        );

        $json_uploadDocData = [
            'UploadRequest' => $json_uploadDocRequest,
            'fileName'      => $fileName,
            'docType'       => $docType,
            'uploadDateTime' => date('Y-m-d H:i')
        ];

        return $json_uploadDocData;
    }

    /**
     * Delete uploaded document
     */
    public function ph_ups_delete_document() {

        $response       = '';
        $queryString    = explode('|', base64_decode($_GET['ph_ups_delete_document']));
        $orderId         = isset($queryString[0]) ? $queryString[0] : '';
        $documentId     = isset($queryString[1]) ? $queryString[1] : '';

        //Check for active license
        if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
            $this->admin_diagnostic_report("------------------------------- UPS Document Delete -------------------------------");
            $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");

            WC_Admin_Meta_Boxes::add_error(__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'));
            wp_redirect(admin_url('/post.php?post=' . $orderId . '&action=edit'));
            exit();
        } else {

            $api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

            if (!$api_access_details) {
                return;
            }

            $endpoint = PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/document-paperless/deleted');

            $headers = array(
                'documentid'     => $documentId,
                'shippernumber'  => $this->upsShipperNumber
            );
    
            $response = Ph_Ups_Api_Invoker::phCallApi($endpoint, $api_access_details['token'], [], $headers);
        }

        // In case of any issues with remote post.
        if (is_wp_error($response) && is_object($response)) {
            $error_message = $response->get_error_message();
            wf_admin_notice::add_notice('Order #' . $orderId . ': Sorry. Something went wrong: ' . $error_message);
        }

        $response_obj  = json_decode($response['body'], true);

        // It is an error response.
        if (isset($response_obj['response']['errors'])) {

            $error_code = (string)$response_obj['response']['errors'][0]['code'];
            $error_desc = (string)$response_obj['response']['errors'][0]['message'];

            $message = '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']';

            wf_admin_notice::add_notice('Order #' . $orderId . ': ' . $message);

            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Delete Soap Fault ---------------');
                $this->admin_diagnostic_report($error_desc);
            }

            return false;
        }

        // Adding debug logs
        if ($this->debugEnabled) {

            $this->admin_diagnostic_report('--------------- UPS Document Delete Request ---------------');
            $this->admin_diagnostic_report($headers);

            $this->admin_diagnostic_report('--------------- UPS Document Delete Response ---------------');
            $this->admin_diagnostic_report($response['body']);
        }

        if (isset($response_obj['DeleteResponse']['Response']['ResponseStatus']) && isset($response_obj['DeleteResponse']['Response']['ResponseStatus']['Description']) && $response_obj['DeleteResponse']['Response']['ResponseStatus']['Description'] == 'Success') {

            $documentDetails = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, '_ph_ups_upload_document_details');

            // Update the document as deleted in the meta
            foreach ($documentDetails as $key => $documentDetail) {

                if (in_array($documentId, $documentDetail)) {

                    $documentDetails[$key]['isDeleted'] = true;
                }
            }

            PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($orderId, '_ph_ups_upload_document_details', $documentDetails);

            wp_redirect(admin_url('/post.php?post=' . $orderId . '&action=edit#PhUpsDocUploadMetabox'));
            exit();
        } else {

            WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while deleting UPS Document. ' . $error_message . ' Please check the logs for more details'));
            wp_redirect(admin_url('/post.php?post=' . $orderId . '&action=edit'));
            exit();
        }
    }

    /**
     * Push to image repository request
     *
     * @param mixed $orderId
     * @param mixed $documentId
     * @param boolean $isFreightShipment
     * @param array $trackingNumbers
     * @param mixed $shipmentIdentifier
     */
    public function ph_ups_push_request($orderId, $documentId, $isFreightShipment = false, $trackingNumbers = [], $shipmentIdentifier = '') {
        $json_request               = array();
        $upsShipmentDateTimeStamp   = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, '_ups_shipment_date_time_stamp');

        //JSON Request
        $json_request['PushToImageRepositoryRequest'] = array(
            'Request'           =>  array(
                'TransactionReference' => array(
                    'CustomerContext'  => $orderId,
                ),
            ),
            'ShipperNumber'     =>  $this->upsShipperNumber,
            'FormsHistoryDocumentID'   => array(
                'DocumentID'    =>  $documentId,
            ),
            'ShipmentIdentifier' =>  $shipmentIdentifier,

            // For small package shipment, set ShipmentType as "1". For freight shipment, set ShipmentType as "2".
            'ShipmentType'      =>  $isFreightShipment ? '2' : '1',
        );

        if (!$isFreightShipment) {
            $json_request['PushToImageRepositoryRequest']['ShipmentDateAndTime'] = $upsShipmentDateTimeStamp;

            foreach ($trackingNumbers as $trackingNumber) {
                $json_request['PushToImageRepositoryRequest']['TrackingNumber'][] = $trackingNumber;
            }
        }

        return $json_request;
    }

    /**
     * Push image to repository
     *
     * @param mixed $orderId
     * @param mixed $documentId
     * @param array $uploadedDocumentDetails
     */
    public function ph_ups_push_to_image_repository($orderId = '', $documentId = '', $uploadedDocumentDetails = []) {

        // If clicked on re-upload
        if (isset($_GET['ph_ups_reupload_document'])) {

            $queryString        = explode('|', base64_decode($_GET['ph_ups_reupload_document']));
            $orderId             = $queryString[0];
            $documentId         = $queryString[1];
        }

        $isFreightShipment  = false;
        $shipmentIdentifier = '';
        $trackingNumbers    = [];
        $pushToRepository   = true;
        $order_object        = wc_get_order($orderId);
        $ph_metadata_handler = new PH_UPS_WC_Storage_Handler($order_object);

        // Get label details
        $isLabelGeneratedOrder      = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_rest_label_details_array');
        $upsCreatedShipmentDetails  = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_rest_created_shipments_details_array');

        // Push to image respository only if label is already generated ( Post Upload )
        if (!empty($isLabelGeneratedOrder)) {

            // Get Shipment ID and check type of shipment
            foreach ($upsCreatedShipmentDetails as $shipmentId =>  $createdShipmentDetails) {

                $isFreightShipment = (isset($createdShipmentDetails['type']) && $createdShipmentDetails['type'] == 'freight') ? true : false;
                $shipmentIdentifier = $shipmentId;
                break;
            }

            // Get tracking numbers
            foreach ($isLabelGeneratedOrder as $labelDetail) {
                foreach ($labelDetail as $detail) {
                    $trackingNumbers[] = $detail['TrackingNumber'];
                }
            }

            // Get Push to image repository request
            $pushToImageRepositoryRequest = wp_json_encode($this->ph_ups_push_request($orderId, $documentId, $isFreightShipment, $trackingNumbers, $shipmentIdentifier), JSON_UNESCAPED_SLASHES);

            //Check for active license
            if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
                $this->admin_diagnostic_report("------------------------------- UPS Document Push To Repository -------------------------------");
                $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");
                return;
            } else {

                $api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

                if (!$api_access_details) {
                    return;
                }

                $endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/document-paperless/repository/image');

                $headers = array(
                    'shippernumber'  => $this->upsShipperNumber
                );
    
                $response = Ph_Ups_Api_Invoker::phCallApi(
                    PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
                    $api_access_details['token'],
                    $pushToImageRepositoryRequest,
                    $headers
                );
            }
            
            // In case of any issues with remote post.
            if (is_wp_error($response) && is_object($response)) {
                $error_message = $response->get_error_message();
                wf_admin_notice::add_notice('Order #' . $orderId . ': Sorry. Something went wrong: ' . $error_message);
            }

            $pushResponse  = json_decode($response['body'], true);

            // It is an error response.
            if (isset($pushResponse['response']['errors'])) {

                $error_code = (string)$pushResponse['response']['errors'][0]['code'];
                $error_desc = (string)$pushResponse['response']['errors'][0]['message'];

                $message = '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']';

                wf_admin_notice::add_notice('Order #' . $orderId . ': ' . $message);

                if ($this->debugEnabled) {

                    $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Soap Fault ---------------');
                    $this->admin_diagnostic_report($error_desc);
                }

                return false;
            }

            // Push to image repository logs
            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Request ---------------');
                $this->admin_diagnostic_report(wp_json_encode($pushToImageRepositoryRequest));

                $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Response ---------------');
                $this->admin_diagnostic_report(print_r($pushResponse, true));
            }

            if (isset($pushResponse['PushToImageRepositoryResponse']['Response']['ResponseStatus']) && isset($pushResponse['PushToImageRepositoryResponse']['Response']['ResponseStatus']['Description']) && $pushResponse['PushToImageRepositoryResponse']['Response']['ResponseStatus']['Description'] == 'Success') {

                $pushToRepository = true;
            } else {

                $pushToRepository = false;
                WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while Pushing the document to repository. ' . $error_message . ' Please check the logs for more details.'));
            }
        }

        // Update push to repository status
        $uploadedDocumentDetails['pushToRepository'] = $pushToRepository;

        // Save the details of uploaded documents in meta
        $updatedData     = [];
        $previousUploads = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, '_ph_ups_upload_document_details');

        // In case of reupload update the details of particular document
        if (!empty($previousUploads) && isset($_GET['ph_ups_reupload_document'])) {

            foreach ($previousUploads as $key => $value) {

                if ($value['documentID'] == $documentId) {
                    $value['pushToRepository'] = $pushToRepository;
                }

                $updatedData[] = $value;
            }

            $ph_metadata_handler->ph_update_meta_data('_ph_ups_upload_document_details', $updatedData);
        }

        // Updating the details of uploaded document 
        if (!empty($previousUploads) && !isset($_GET['ph_ups_reupload_document'])) {

            array_push($previousUploads, $uploadedDocumentDetails);

            $ph_metadata_handler->ph_update_meta_data('_ph_ups_upload_document_details', $previousUploads);
        } else if (!isset($_GET['ph_ups_reupload_document'])) {

            $ph_metadata_handler->ph_update_meta_data('_ph_ups_upload_document_details', [$uploadedDocumentDetails]);
        }

        $ph_metadata_handler->ph_save_meta_data();

        // Redirect to same page if the action is reupload
        if (isset($_GET['ph_ups_reupload_document'])) {
            wp_redirect(admin_url('/post.php?post=' . $orderId . '&action=edit'));
        }
    }

    /**
     * Add admin diagnostic report
     *
     * @param mixed $data
     */
    public function admin_diagnostic_report($data) {

        if (function_exists("wc_get_logger")) {
            $log = wc_get_logger();
            $log->debug(($data) . PHP_EOL . PHP_EOL, array('source' => 'PluginHive-UPS-Error-Debug-Log'));
        }
    }
}
