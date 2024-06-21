/**
 * WordPress dependencies
 */
import { render, createRoot } from '@wordpress/element';

/**
 * External dependencies
 */
import { HashRouter } from 'react-router-dom';

/**
 * Internal dependencies.
 */
import AdminEditor from './app.js';

const domElement = document.getElementById( 'barn2-wpo-settings-root' );
const uiElement = (
	<HashRouter>
		<AdminEditor />
	</HashRouter>
);

if ( domElement ) {
	if ( createRoot ) {
		createRoot( domElement ).render( uiElement );
	} else {
		render( uiElement, domElement );
	}
}
