import { getPaymentMethodData } from '@woocommerce/settings';

export const settings = getPaymentMethodData( 'first_data_clover_credit_card', {} );

/**
 * Log data to console if debug is enabled.
 *
 * @param {string} message Message to log
 * @param {Object} data    Data object to log
 * @return {void}
 */
export const logData = (message, data = null) => {
	if (settings.debug_mode && settings.debug != 'off') {
		console.log(`Clover: ${message}`);
		if (data) {
			console.log(data);
		}
	}
};
