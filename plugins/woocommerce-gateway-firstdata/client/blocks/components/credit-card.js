import { useEffect, useState } from "@wordpress/element";
import { CreditCardFields } from "./credit-card-fields";
import { PaymentMethodDescription } from "./payment-method-description";
import { usePaymentProcessing } from "../use-payment-processing";
import { usePaymentForm } from "../use-payment-form";
import { settings, logData } from "../utils";
import { __ } from "@wordpress/i18n";

export const CreditCard = ( props ) => {
	const { eventRegistration, emitResponse } = props;
	const { onPaymentSetup, onCheckoutFail } = eventRegistration;
	const paymentForm = usePaymentForm(props);
	const { setupIntegration, hostedFieldsInstance, integrationInstance } = paymentForm;

	// general error message
	const [errorMessage, setErrorMessage] = useState('');

	// whether hosted fields handler are loaded
	const [isLoaded, setIsLoaded] = useState(false);

	useEffect(() => {
		async function integrate() {
			try {
				await setupIntegration();
				setIsLoaded(true);
				logData('Clover loading complete');
			} catch (e) {
				logData(`Integration Error: ${e.message}`, e);
				setErrorMessage(__('An error occurred while loading the payment form.', 'woocommerce-gateway-firstdata'));
			}
		}

		integrate();

		return () => {
			setIsLoaded(false);
		};
	}, [setupIntegration]);

	useEffect(() => {
		const unsubscribe = onCheckoutFail?.(async (checkoutFailResponse) => {
			const { paymentStatus, paymentDetails } = checkoutFailResponse?.processingResponse || {}

			if (
				paymentStatus === emitResponse?.responseTypes.FAIL &&
				paymentDetails?.result === emitResponse?.responseTypes.FAIL &&
				paymentDetails?.message
			) {
				return {
					type: emitResponse?.responseTypes.FAIL,
					// TODO: We can add more specific errors here based on paymentDetails?.result once we know who should map the message to customer friendly versions
					message: __('An error occurred, please try again or try an alternate form of payment.', 'woocommerce-gateway-firstdata'),
					messageContext:
					settings?.debug_mode === 'full' ||
					settings?.debug_mode === 'checkout'
							? emitResponse?.noticeContexts?.CHECKOUT
							: emitResponse?.noticeContexts?.PAYMENTS,
					retry: true,
				}
			}
		})
		return () => unsubscribe?.()
	}, [
		emitResponse?.noticeContexts?.CHECKOUT,
		emitResponse?.noticeContexts?.PAYMENTS,
		emitResponse?.responseTypes.FAIL,
		onCheckoutFail,
	])

	// handle payment setup
	// @link https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce-blocks/docs/internal-developers/block-client-apis/checkout/checkout-api.md#passing-a-value-from-the-client-through-to-server-side-payment-processing
	usePaymentProcessing(
		onPaymentSetup,
		emitResponse,
		integrationInstance,
	)

	if (errorMessage) {
		return <div className="woocommerce-error">{errorMessage}</div>;
	}

	return (
		<>
			<PaymentMethodDescription settings={settings} />
			<CreditCardFields {...props} isLoaded={isLoaded} hostedFieldsInstance={hostedFieldsInstance} />
		</>
	);
}
