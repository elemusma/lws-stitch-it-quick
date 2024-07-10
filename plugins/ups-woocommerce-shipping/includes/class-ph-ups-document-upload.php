<?php

if (!defined('ABSPATH')) {
    exit;
}

class ph_ups_document_upload
{

    // Class Variables Declaration
    public $upsUserId;
    public $upsPassword;
    public $upsAccessKey;
    public $upsShipperNumber;
    public $apiMode;
    public $debugEnabled;

    public function __construct()
    {

        $upsSettings            = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
        $this->upsUserId        = (isset($upsSettings['user_id']) && !empty($upsSettings['user_id'])) ? $upsSettings['user_id'] : '';
        $this->upsPassword      = (isset($upsSettings['password']) && !empty($upsSettings['password'])) ? $upsSettings['password'] : '';
        $this->upsAccessKey     = (isset($upsSettings['access_key']) && !empty($upsSettings['access_key'])) ? $upsSettings['access_key'] : '';
        $this->upsShipperNumber = (isset($upsSettings['shipper_number']) && !empty($upsSettings['shipper_number'])) ? $upsSettings['shipper_number'] : '';
        $this->apiMode          = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode'])) ? $upsSettings['api_mode'] : 'Test';
        $this->debugEnabled     = (isset($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;

        if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

            if (!class_exists('PH_UPS_Document_Upload_Rest')) {
				include_once('ups_rest/class-ph-ups-rest-document-upload.php');
			}

			$PH_UPS_Document_Upload_Rest = new PH_UPS_Document_Upload_Rest();

            // Ajax call to trigger document upload
            add_action('wp_ajax_ph_ups_upload_document', array($PH_UPS_Document_Upload_Rest, 'ph_ups_upload_document'), 10, 1);

            // Delete UPS Document
            if (isset($_GET['ph_ups_delete_document'])) {
                add_action('admin_init', array($PH_UPS_Document_Upload_Rest, 'ph_ups_delete_document'));
            }

            // Push image to repository
            if (isset($_GET['ph_ups_reupload_document'])) {
                add_action('admin_init', array($PH_UPS_Document_Upload_Rest, 'ph_ups_push_to_image_repository'));
            }

        } else {
            // Ajax call to trigger document upload
            add_action('wp_ajax_ph_ups_upload_document', array($this, 'ph_ups_upload_document'), 10, 1);

            // Delete UPS Document
            if (isset($_GET['ph_ups_delete_document'])) {
                add_action('admin_init', array($this, 'ph_ups_delete_document'));
            }

            // Push image to repository
            if (isset($_GET['ph_ups_reupload_document'])) {
                add_action('admin_init', array($this, 'ph_ups_push_to_image_repository'));
            }
        }
    }

    /**
     * Create SOAP header and client
     *
     * @return SoapClient $client
     */
    public function ph_ups_create_soap_client($options = [])
    {

        $header = new stdClass();
        $header->UsernameToken = new stdClass();
        $header->UsernameToken->Username    = $this->upsUserId;
        $header->UsernameToken->Password    = $this->upsPassword;
        $header->ServiceAccessToken = new stdClass();
        $header->ServiceAccessToken->AccessLicenseNumber = $this->upsAccessKey;

        $options['trace'] = true;
        $options['cache_wsdl'] = 0;

        $client = new SoapClient(plugin_dir_path(dirname(__FILE__)) . 'wsdl/' . $this->apiMode . '/paperless_document/PaperlessDocumentAPI.wsdl', $options);

        $authvalues = new SoapVar($header, SOAP_ENC_OBJECT);
        $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $header, false);

        if (!Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {
            $client->__setSoapHeaders($header);
        }

        return $client;
    }

    /**
     * Upload document
     */
    public function ph_ups_upload_document()
    {

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

        //Check if new registration method
        if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
            //Check for active license
            if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
                $this->admin_diagnostic_report("------------------------------- UPS Document Upload -------------------------------");
                $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");
                return;
            } else {

                $apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

                if (!$apiAccessDetails) {
                    return;
                }

                $proxyParams = Ph_UPS_Woo_Shipping_Common::phGetProxyParams($apiAccessDetails, 'upload_doc');

                $client = $this->ph_ups_create_soap_client($proxyParams['options']);

                // Updating the SOAP location to Proxy server
                $client->__setLocation($proxyParams['endpoint']);
            }
        } else {

            $client = $this->ph_ups_create_soap_client();
        }

        $uploadDocData = $this->create_ups_upload_doc_request($orderId, $attachment, $docType);

        try {

            $response = $client->ProcessUploading($uploadDocData['uploadRequest']);
        } catch (\SoapFault $fault) {

            $errorMessage  = $fault->faultstring;

            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Upload Soap Fault ---------------');
                $this->admin_diagnostic_report($errorMessage);
            }
        }

        // Debug logs for Upload requets & response
        if ($this->debugEnabled) {

            $this->admin_diagnostic_report('--------------- UPS Document Upload Request ---------------');
            $this->admin_diagnostic_report($client->__getLastRequest());

            $this->admin_diagnostic_report('--------------- UPS Document Upload Response ---------------');
            $this->admin_diagnostic_report($client->__getLastResponse());
        }

        // Get label details
        $isLabelGeneratedOrder      = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_label_details_array');

        // Handle document upload response
        if (isset($response->Response->ResponseStatus) && isset($response->Response->ResponseStatus->Description) && $response->Response->ResponseStatus->Description == 'Success') {

            $documentId = $response->FormsHistoryDocumentID->DocumentID;

            $uploadedDocumentDetails = [
                'success'               => true,
                'transactionIdentifier' => $response->Response->TransactionReference->TransactionIdentifier,
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
            WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while Uploading UPS Document. ' . $errorMessage . ' Please check the logs for more details.'));
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
    public function create_ups_upload_doc_request($orderId, $attachment, $docType)
    {

        $uploadDocRequest   = [];
        $uploadDocData      = [];
        $fileUrl            = wp_upload_dir();
        $documentUrl        = $_SERVER['DOCUMENT_ROOT'] . parse_url($attachment['url'], PHP_URL_PATH);
        $fileUrl            = $documentUrl;
        $formFIle           = (base64_encode(file_get_contents($fileUrl)));
        $fileName           = isset($attachment['filename']) ? $attachment['filename'] : '';
        $fileType           = isset($attachment['subtype']) ? $attachment['subtype'] : '';

        $uploadDocRequest['Request'] = [
            'TransactionReference' => [
                'CustomerContext'        => $orderId,
                'TransactionIdentifier'  => 'Pluginhive Upload Document Request'
            ]
        ];

        $uploadDocRequest['ShipperNumber'] = $this->upsShipperNumber;

        $uploadDocRequest['UserCreatedForm'] = [
            'UserCreatedFormFileName' => $fileName,
            'UserCreatedFormFile' => $formFIle,
            'UserCreatedFormFileFormat' => $fileType,
            'UserCreatedFormDocumentType' => $docType
        ];

        $uploadDocData = [
            'uploadRequest' => $uploadDocRequest,
            'fileName'      => $fileName,
            'docType'       => $docType,
            'uploadDateTime' => date('Y-m-d H:i')
        ];

        return $uploadDocData;
    }

    /**
     * Delete uploaded document
     */
    public function ph_ups_delete_document()
    {

        $response       = '';
        $deleteRequest  = [];
        $queryString    = explode('|', base64_decode($_GET['ph_ups_delete_document']));
        $orderId         = isset($queryString[0]) ? $queryString[0] : '';
        $documentId     = isset($queryString[1]) ? $queryString[1] : '';

        //Check if new registration method
        if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
            //Check for active license
            if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
                $this->admin_diagnostic_report("------------------------------- UPS Document Delete -------------------------------");
                $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");

                WC_Admin_Meta_Boxes::add_error(__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'));
                wp_redirect(admin_url('/post.php?post=' . $orderId . '&action=edit'));
                exit();
            } else {

                $apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

                if (!$apiAccessDetails) {
                    return;
                }

                $proxyParams = Ph_UPS_Woo_Shipping_Common::phGetProxyParams($apiAccessDetails, 'upload_doc');

                $client = $this->ph_ups_create_soap_client($proxyParams['options']);

                // Updating the SOAP location to Proxy server
                $client->__setLocation($proxyParams['endpoint']);
            }
        } else {

            $client = $this->ph_ups_create_soap_client();
        }

        // Delete Request
        $deleteRequest['Request'] = [
            'TransactionReference' =>
            [
                'CustomerContext'       => $orderId,
                'TransactionIdentifier' => 'Pluginhive Delete Document Request'
            ]
        ];

        $deleteRequest['ShipperNumber'] = $this->upsShipperNumber;
        $deleteRequest['DocumentID'] = $documentId;

        try {

            $response = $client->ProcessDeleting($deleteRequest);
        } catch (\SoapFault $fault) {

            $errorMessage = $fault->faultstring;

            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Delete Soap Fault ---------------');
                $this->admin_diagnostic_report($errorMessage);
            }
        }

        // Adding debug logs
        if ($this->debugEnabled) {

            $this->admin_diagnostic_report('--------------- UPS Document Delete Request ---------------');
            $this->admin_diagnostic_report($client->__getLastRequest());

            $this->admin_diagnostic_report('--------------- UPS Document Delete Response ---------------');
            $this->admin_diagnostic_report($client->__getLastResponse());
        }

        if (isset($response->Response->ResponseStatus) && isset($response->Response->ResponseStatus->Description) && $response->Response->ResponseStatus->Description == 'Success') {

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

            WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while deleting UPS Document. ' . $errorMessage . ' Please check the logs for more details'));
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
    public function ph_ups_push_request($orderId, $documentId, $isFreightShipment = false, $trackingNumbers = [], $shipmentIdentifier = '')
    {

        $request                    = [];
        $upsShipmentDateTimeStamp   = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, '_ups_shipment_date_time_stamp');

        $request['Request'] = [
            'TransactionReference' => [
                'CustomerContext' => $orderId,
                'TransactionIdentifier' => 'Pluginhive Push To Image Repository Request'
            ]
        ];

        $request['ShipperNumber'] = $this->upsShipperNumber;

        $request['FormsHistoryDocumentID'] = [
            'DocumentID' => $documentId
        ];

        $request['ShipmentIdentifier'] = $shipmentIdentifier;
        // For small package shipment, set ShipmentType as "1". For freight shipment, set ShipmentType as "2".
        $request['ShipmentType'] = $isFreightShipment ? '2' : '1';

        $trackingIds = [];

        if (!$isFreightShipment) {
            $request['ShipmentDateAndTime'] = $upsShipmentDateTimeStamp;

            foreach ($trackingNumbers as $trackingNumber) {
                $request['TrackingNumber'][] = $trackingNumber;
            }
        }

        return $request;
    }

    /**
     * Push image to repository
     *
     * @param mixed $orderId
     * @param mixed $documentId
     * @param array $uploadedDocumentDetails
     */
    public function ph_ups_push_to_image_repository($orderId = '', $documentId = '', $uploadedDocumentDetails = [])
    {

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

        //Check if new registration method
        if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
            //Check for active license
            if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
                $this->admin_diagnostic_report("------------------------------- UPS Document Push To Repository -------------------------------");
                $this->admin_diagnostic_report("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label");
                return;
            } else {

                $apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

                if (!$apiAccessDetails) {
                    return;
                }

                $proxyParams = Ph_UPS_Woo_Shipping_Common::phGetProxyParams($apiAccessDetails, 'push_to_repository');

                $client = $this->ph_ups_create_soap_client($proxyParams['options']);

                // Updating the SOAP location to Proxy server
                $client->__setLocation($proxyParams['endpoint']);
            }
        } else {

            $client = $this->ph_ups_create_soap_client();
        }

        // Get label details
        $isLabelGeneratedOrder      = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_label_details_array');
        $upsCreatedShipmentDetails  = PH_UPS_WC_Storage_Handler::ph_get_meta_data($orderId, 'ups_created_shipments_details_array');

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
            $pushToImageRepositoryRequest = $this->ph_ups_push_request($orderId, $documentId, $isFreightShipment, $trackingNumbers, $shipmentIdentifier);

            try {
                $pushResponse = $client->ProcessPushToImageRepository($pushToImageRepositoryRequest);
            } catch (\SoapFault $fault) {

                $errorMessage = $fault->faultstring;

                if ($this->debugEnabled) {

                    $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Soap Fault ---------------');
                    $this->admin_diagnostic_report($errorMessage);
                }
            }


            // Push to image repository logs
            if ($this->debugEnabled) {

                $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Request ---------------');
                $this->admin_diagnostic_report($client->__getLastRequest());

                $this->admin_diagnostic_report('--------------- UPS Document Push To Repository Response ---------------');
                $this->admin_diagnostic_report($client->__getLastResponse());
            }

            if (isset($pushResponse->Response->ResponseStatus) && isset($pushResponse->Response->ResponseStatus->Description) && $pushResponse->Response->ResponseStatus->Description == 'Success') {

                $pushToRepository = true;
            } else {

                $pushToRepository = false;
                WC_Admin_Meta_Boxes::add_error(__('Oops! Some error occured while Pushing the document to repository. ' . $errorMessage . ' Please check the logs for more details.'));
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
    public function admin_diagnostic_report($data)
    {

        if (function_exists("wc_get_logger")) {
            $log = wc_get_logger();
            $log->debug(($data) . PHP_EOL . PHP_EOL, array('source' => PH_UPS_DEBUG_LOG_FILE_NAME));
        }
    }
}

new ph_ups_document_upload();
