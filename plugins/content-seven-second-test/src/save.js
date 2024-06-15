/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { RawHTML } from '@wordpress/element';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const blockProps = useBlockProps.save();

	return (
		<div {...blockProps}>

		<div class="bg-white position-fixed w-100 h-100 activate-on-button-click" style="top:0;left:0;z-index:10;opacity:0;pointer-events:none;"></div>

		<section style="padding:50px 0px;">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<p>You'll be shown a website page for 7 seconds. After that, you'll be asked 3 questions about it.</p>
						[button class="open-modal modal-seven-second-test-img" id="modal-seven-second-test-img"]View image for 7 seconds[/button]
					</div>
				</div>
			</div>
		</section>


		<div class="modal-content modal-seven-second-test-img position-fixed w-100 h-100 z-3" style="opacity:0;">
		<div class="bg-overlay"></div>
		<div class="bg-content" style="padding:0px!important;width:100%!important;">
			<div class="bg-content-inner">
				<div class="close" id="">X</div>
				<div>
					{/* {{ img goes here }} */}
					<img src="" alt="" />
					</div>
				</div>
			</div>
		</div>

		<div class="modal-content modal-seven-second-test-questions position-fixed w-100 h-100 z-3" style="opacity:0;">
    <div class="bg-overlay"></div>
    <div class="bg-content" style="height:90%;">
        <div class="bg-content-inner">
            <div class="close" id="">X</div>
            <div>
				{/* {shortcode for form goes here} */}
				</div>
			</div>
		</div>
	</div>


		</div>
	);
}
