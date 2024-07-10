<?php

class PH_UPS_Error_Notice_Handle
{
    private $error_discriptions = array(

        '250007' => '<br> <b>Possible Reason</b> - UPS account may be suspended. <br> <b>Possible Solution</b> - Try generating labels after 30 mins. Contact your UPS account representative if the issue still persists.',
    );

    /**
	 * Mapping error message.
	 *
	 * @param string
	 * @return string
	 */
    public function ph_find_error_additional_info( $error_code ) {

        $additional_info = isset( $this->error_discriptions[$error_code] ) ? __( $this->error_discriptions[$error_code], 'ups-woocommerce-shipping' ) : '';

        return $additional_info;
    }				
}