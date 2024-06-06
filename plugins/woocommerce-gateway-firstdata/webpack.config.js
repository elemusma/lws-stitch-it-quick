const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const DependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');
const fs = require('fs');
const dotenv = require('dotenv')

dotenv.config({ path: '.env' })

module.exports = {
	...defaultConfig,
	devServer: {
		...defaultConfig.devServer,
		headers: {
			...defaultConfig.devServer?.headers,
			'Access-Control-Allow-Origin': '*',
			'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
			'Access-Control-Allow-Headers':
				'X-Requested-With, content-type, Authorization',
		},
		allowedHosts: process.env.WEBPACK_ALLOWEDHOSTS
			? [
					...((defaultConfig.devServer &&
						defaultConfig.devServer.allowedHosts) ||
						[]),
					...process.env.WEBPACK_ALLOWEDHOSTS.split(','),
				]
			: 'auto',
		client: {
			overlay: {
				errors: true,
				warnings: false,
				runtimeErrors: false,
			},
		},
		server:
			process.env.WEBPACK_USE_HTTPS === 'true'
				? {
						type: 'https',
						options: {
							ca: fs.readFileSync(process.env.WEBPACK_CA_CERT),
							key: fs.readFileSync(process.env.WEBPACK_APP_KEY),
							cert: fs.readFileSync(process.env.WEBPACK_APP_CERT),
							requestCert: false,
						},
					}
				: defaultConfig.devServer?.server || 'http',
		host: process.env.WEBPACK_HOST || 'localhost',
		port: process.env.WEBPACK_PORT || 8887,
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new DependencyExtractionWebpackPlugin(),
	],
	entry: {
		clover: path.resolve(process.cwd(), 'client/blocks', 'clover.js'),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve(process.cwd(), 'assets/js/blocks'),
		filename: 'wc-first-data-clover-credit-card-checkout-block.js'
	},
};
