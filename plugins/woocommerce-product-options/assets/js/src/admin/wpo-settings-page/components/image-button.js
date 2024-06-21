/**
 * WordPress dependencies.
 */
import { useState, useLayoutEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { MediaUpload } from '@wordpress/media-utils';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies.
 */
import { Button } from '@barn2plugins/components';

const ImageButton = ( { imageId = null, onChange = () => {} } ) => {
	const [ pending, setPending ] = useState( false );
	const [ imageUrl, setImageUrl ] = useState( null );

	/**
	 * On component mount, trigger an automated fetch for the image thumbnail.
	 */
	useLayoutEffect( () => {
		if ( ! imageId ) {
			return;
		}

		const fetchImage = async () => {
			setPending( true );

			const imageDetails = await apiFetch( {
				path: `/wp/v2/media/${ imageId }?_fields=media_details`,
			} );

			setImageUrl( imageDetails?.media_details?.sizes?.thumbnail?.source_url );
			setPending( false );
		};

		fetchImage();
	}, [ imageId ] );

	return (
		<>
			<MediaUpload
				onSelect={ ( media ) => {
					onChange( media.id );
				} }
				allowedTypes={ [ 'image' ] }
				value={ imageId }
				render={ ( { open } ) => (
					<div className="editor-post-featured-image__container">
						<Button
							className={ ! imageId ? 'wpo-choices-image-toggle' : 'wpo-choices-image-preview' }
							onClick={ open }
							aria-label={
								! imageId ? null : __( 'Edit or update the image', 'woocommerce-product-options' )
							}
						>
							{ imageUrl && <img src={ imageUrl } alt="" /> }
							{ pending && <Spinner /> }
							{ ! imageId && __( 'Add Media', 'woocommerce-product-options' ) }
						</Button>
					</div>
				) }
			/>
		</>
	);
};

export default ImageButton;
