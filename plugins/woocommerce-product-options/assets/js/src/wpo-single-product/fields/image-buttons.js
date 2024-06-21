const imageButtons = ( addToCartForm, $ ) => {
	const form = addToCartForm;

	function init() {
		const imageButtonsOptions = Array.from( form.querySelectorAll( '.wpo-image-buttons' ) );
		imageButtonsOptions.forEach( ( imageButtonsOption ) => {
			setLineClamp( imageButtonsOption );
		} );

		const imageButtonFields = Array.from( form.querySelectorAll( '.wpo-image-button' ) );

		imageButtonFields.forEach( ( imageButton ) => {
			const imageButtonsOption = imageButton.closest( '.wpo-image-buttons' );
			const activeContainer = imageButton.querySelector( '.wpo-image-active' );
			const input = imageButton.querySelector( 'input' );

			activeContainer.classList.toggle( 'wpo-image-selected', input.checked );

			input.addEventListener( 'change', ( event ) => {
				activeContainer.classList.toggle( 'wpo-image-selected', event.target.checked );
				if ( imageButtonsOption.querySelectorAll( '.wpo-image-selected' )?.length < 1 ) {
					switchWooGalleryImage( false, activeContainer );
				}
			} );

			imageButton.addEventListener( 'click', () => switchWooGalleryImage( imageButton, activeContainer ) );
		} );
	}

	function setLineClamp( imageButtonsOption ) {
		const imageWrap = imageButtonsOption?.querySelector( '.wpo-image-wrap' );
		const imageText = imageWrap?.querySelector( '.wpo-image-text' );
		const imageLabel = imageText?.querySelector( '.wpo-image-label' );

		if ( ! imageLabel ) {
			return;
		}

		const overlayFactor = imageButtonsOption.classList.contains( 'wpo-image-buttons-partial' ) ? .5 : 1;
		const height = overlayFactor * imageWrap.getBoundingClientRect().height;
		const lineHeight = parseFloat( getComputedStyle( imageLabel )[ 'line-height' ].replace( 'px', '' ) );
		const lineClamp = Math.floor( height / lineHeight ) - 2;
		imageButtonsOption.style.setProperty('--wpo-image-buttons-line-clamp', lineClamp );
	}

	function switchWooGalleryImage( imageButton, activeContainer ) {
		const $form = $( form );
		const productContainer = form.closest( '.product' );
		const productGallery = productContainer.querySelector( '.images' );
		const galleryImage = productGallery?.querySelector( 'img.wp-post-image' );
		const galleryNav = productContainer.querySelector( '.flex-control-nav' );

		if ( imageButton === false ) {
			$( galleryNav?.querySelector( 'li:nth-child(1) img' ) ).trigger( 'click' );
			wpoGalleryImageReset( $form );
			return;
		}

		const imageData = JSON.parse( imageButton?.dataset?.image ?? false );
		const galleryThumbnailSrc = imageData?.gallery_thumbnail_src ?? '';

		if ( activeContainer.classList.contains( 'wpo-image-selected' ) ) {
			wpoGalleryImageReset( $form );
		}

		if ( galleryImage?.dataset && ! galleryImage.dataset.srcset ) {
			galleryImage.dataset.srcset = galleryImage.srcset;
		}

		// See if gallery has a matching image we can slide to.
		const slideToImage = galleryNav?.querySelectorAll( 'li img[src="' + galleryThumbnailSrc + '"]' );

		if ( slideToImage?.length > 0 ) {
			$( slideToImage ).trigger( 'click' );

			window.setTimeout( function () {
				$( window ).trigger( 'resize' );
				$( productGallery ).trigger( 'woocommerce_gallery_init_zoom' );
			}, 20 );
		} else {
			// Otherwise, just update the image src.
			wpoImageUpdate( $form, imageButton );
		}
	}

	// function wpoResetImageAttr( $element, attrs ) {
	// 	if ( Array.isArray( attrs ) ) {
	// 		attrs.forEach( function ( attr ) {
	// 			wpoResetImageAttr( $element, attr );
	// 		} );
	// 	} else if ( undefined !== $element.attr( 'data-o_' + attrs ) ) {
	// 		$element.attr( attrs, $element.attr( 'data-o_' + attrs ) );
	// 	}
	// };

	// function wpoSetImageAttr( $element, attrs, value ) {
	// 	if ( Array.isArray( attrs ) ) {
	// 		attrs.forEach( function ( attr ) {
	// 			const attrEntries = Object.entries( attr );
	// 			wpoSetImageAttr( $element, attrEntries[0][0], attrEntries[0][1] );
	// 		} );
	// 	} else {
	// 		if ( undefined === $element.attr( 'data-o_' + attrs ) ) {
	// 			$element.attr( 'data-o_' + attrs, ! $element.attr( attrs ) ? '' : $element.attr( attrs ) );
	// 		}
	// 		if ( false === value ) {
	// 			$element.removeAttr( attrs );
	// 		} else {
	// 			$element.attr( attrs, value );
	// 		}
	// 	}
	// };

	function wpoGalleryImageReset( $form ) {
		// const $product = $form.closest( '.product' ),
		// 	$productGallery = $product.find( '.images' ),
		// 	$galleryNav = $product.find( '.flex-control-nav' ),
		// 	$galleryImg = $galleryNav.find( 'li:eq(0) img' ),
		// 	$productImgWrap = $productGallery
		// 		.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' )
		// 		.eq( 0 ),
		// 	$productImg = $productImgWrap.find( '.wp-post-image' ),
		// 	$productLink = $productImgWrap.find( 'a' ).eq( 0 );

		// wpoResetImageAttr( $productImg, [
		// 	'src',
		// 	'width',
		// 	'height',
		// 	'srcset',
		// 	'sizes',
		// 	'title',
		// 	'data-caption',
		// 	'alt',
		// 	'data-src',
		// 	'data-large_image',
		// 	'data-large_image_width',
		// 	'data-large_image_height'
		// ] );
		// wpoResetImageAttr( $productImgWrap, 'data-thumb' );
		// wpoResetImageAttr( $galleryImg, 'src' );
		// wpoResetImageAttr( $productLink, 'href' );

		if ( typeof $.fn.slick === 'function' ) {
			$( '.tbh-carousel' ).slick( 'slickGoTo', 0 );
		}
	};

	function wpoImageUpdate( $form, imageButton ) {
		const $product = $form.closest( '.product' ),
			$productGallery = $product.find( '.images' ),
			$galleryNav = $product.find( '.flex-control-nav' ),
			// $productImgWrap = $productGallery
			// 	.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' )
			// 	.eq( 0 ),
			imageData = JSON.parse( imageButton?.dataset?.image ?? false );

		if ( imageData?.src ) {
			// const $galleryImg = $galleryNav.find( 'li:eq(0) img' ),
			// 	$productImg = $productImgWrap.find( '.wp-post-image' ),
			// 	$productLink = $productImgWrap.find( 'a' ).eq( 0 );

			// // See if the gallery has an image with the same original src as the image we want to switch to.
			// const galleryHasImage = $galleryNav.find( 'li img[data-o_src="' + imageData?.gallery_thumbnail_src + '"]' ).length > 0;

			// // If the gallery has the image, reset the images. We'll scroll to the correct one.
			// if ( galleryHasImage ) {
			// 	wpoGalleryImageReset( $form );
			// }

			// See if gallery has a matching image we can slide to.
			const slideToImage = $galleryNav.find( 'li img[src="' + imageData?.gallery_thumbnail_src + '"]' );

			if ( slideToImage.length > 0 ) {
				slideToImage.trigger( 'click' );
				$form.attr( 'current-image', imageData?.image_id );
				window.setTimeout( function () {
					$( window ).trigger( 'resize' );
					$productGallery.trigger( 'woocommerce_gallery_init_zoom' );
				}, 20 );
				return;
			}

			if ( typeof $.fn.slick === 'function' && imageData?.gallery_thumbnail_src ) {
				const $slickCarousel = $( '.thb-carousel' );
				const index = $slickCarousel.find( `div[data-thumb="${ imageData.gallery_thumbnail_src }"]` ).index();

				$slickCarousel.slick( 'slickGoTo', index );
			}

			// wpoResetImageAttr( $productImg, [
			// 	{ src: imageData?.src },
			// 	{ width: imageData?.src_w },
			// 	{ height: imageData?.src_h },
			// 	{ srcset: imageData?.srcset },
			// 	{ sizes: imageData?.sizes },
			// 	{ title: imageData?.title },
			// 	{ 'data-caption': imageData?.caption },
			// 	{ alt: imageData?.alt },
			// 	{ 'data-src': imageData?.full_src },
			// 	{ 'data-large_image': imageData?.full_src },
			// 	{ 'data-large_image_width': imageData?.full_src_w },
			// 	{ 'data-large_image_height': imageData?.full_src_h },
			// ] );
			// wpoSetImageAttr( $productImgWrap, 'data-thumb', imageData?.src );
			// wpoSetImageAttr( $galleryImg, 'src', imageData?.gallery_thumbnail_src );
			// wpoSetImageAttr( $productLink, 'href', imageData?.full_src );
		} else {
			wpoGalleryImageReset( $form );
		}

		// window.setTimeout( function () {
		// 	$( window ).trigger( 'resize' );
		// 	wpoMaybeTriggerSlidePositionReset( $form, imageButton );
		// 	$productGallery.trigger( 'woocommerce_gallery_init_zoom' );
		// }, 20 );
	};

	// function wpoMaybeTriggerSlidePositionReset( $form, imageButton ) {
	// 	const $product = $form.closest( '.product' ),
	// 		$productGallery = $product.find( '.images' ),
	// 		imageData = JSON.parse( imageButton?.dataset?.image ?? false );

	// 	let resetSlidePosition = false;

	// 	if ( $form.attr( 'current-image' ) !== imageData?.image_id ) {
	// 		resetSlidePosition = true;
	// 	}

	// 	$form.attr( 'current-image', imageData?.image_id );

	// 	if ( resetSlidePosition ) {
	// 		$productGallery.trigger( 'woocommerce_gallery_reset_slide_position' );
	// 	}

	// };

	return { init };
};

export default imageButtons;
