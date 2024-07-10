<?php

defined('ABSPATH') || exit;

class PH_UPS_Config {

	public const PH_UPS_API_MANAGER_ENDPOINT		= 'https://www.pluginhive.com/';
	public const PH_UPS_PROXY_ENV					= 'live';
	public const PH_UPS_PROXY_API_BASE_URL			= 'https://ship-rate-track-proxy.pluginhive.io';
	public const PH_UPS_REGISTRATION_UI_URL			= 'https://carrier-registration-ui.pluginhive.io';
	public const PH_UPS_AUTH_PROVIDER_TOKEN			= 'https://auth-provider.pluginhive.io/api/auth-provider/token';
	public const PH_UPS_AUTH_PROVIDER_UI_CREDENTIAL	= 'OGFmZWJlMTEtOWVmYy00MWZlLWEyNDgtMGZmYTM3YWIxYjA2OlMzTW5ZV0otb1ZTcjNHSllsTDNXNA==';
	public const PH_UPS_CARRIER_ENDPOINT			= '/api/ship-rate-track/carriers/';

	public const PH_UPS_LASSO_URI					= 'https://www.ups.com/lasso/signin';
	public const PH_UPS_READY_REG_API				= 'https://carrier-registration-api.pluginhive.io/api/carriers/ed359537-5119-4d82-9651-faf1fa7c6f55';
	public const PH_UPS_DAP_REG_API					= 'https://carrier-registration-api.pluginhive.io/api/carriers/5036d87b-17ac-48ce-9c88-c443bf9d1d58';

	public const UPS_DAP_REGISTRATION_API_PROFILE	= 'https://carrier-registration-api.pluginhive.io/api/carriers/3dbc4dd3-de66-4874-8a34-d56fa9fd95e0';
	public const UPS_DAP_REGISTRATION_API_TOKEN		= 'https://carrier-registration-api.pluginhive.io/api/carriers/0ce0e411-a836-437f-a8e8-d839f44e9a62';
	public const UPS_DAP_REGISTRATION_API_ACCOUNT	= 'https://carrier-registration-api.pluginhive.io/api/carriers/4f7f73b6-a43b-4efc-9b01-91a83bc68172';
	public const UPS_DAP_REGISTRATION_API_PROMO		= 'https://carrier-registration-api.pluginhive.io/api/carriers/5036d87b-17ac-48ce-9c88-c443bf9d1d58';
	
	public const PH_UPS_READY_CLIENT_ID				= '96GXtmEwXirHjEjh6CQV8QvXpapJiMviYL46D8xcsg7vjqHo';
	public const PH_UPS_READY_REDIRECT_URL			= self::PH_UPS_REGISTRATION_UI_URL . '/callback/carriers/a80738c4-8ab8-497e-b87b-1f026722ae0a';

	public const PH_UPS_DAP_CLIENT_ID				= 'XZ4IgFAnETA2XLT5FTYcwhIu3zn54bo446nRfGHaXsXY27OQ';
	public const PH_UPS_DAP_REDIRECT_URL			= self::PH_UPS_REGISTRATION_UI_URL . '/callback/carriers/a5011441-3fe8-440b-a2bb-f795150f1b4e';
}
