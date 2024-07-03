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

	// const setOuterHTML = (htmlString) => {
	// 	const tempDiv = document.createElement('div');
	  
	// 	// Append a text node with the HTML string
	// 	tempDiv.appendChild(document.createTextNode(htmlString));
	  
	// 	// Use React.createElement to create a React element based on the outerHTML
	// 	const reactElement = React.createElement('div', { dangerouslySetInnerHTML: { __html: tempDiv.outerHTML } });
	  
	// 	return reactElement;
	//   };

	return (
		<div {...blockProps}>
		<section className={`position-relative ${attributes.section_class}`} style={`padding:50px 0;${attributes.section_style}`} id={attributes.section_id}>
		<div className="background-carousel owl-carousel owl-theme owl-loaded position-absolute h-100" style={{top:0,left:0}}>
		{ attributes.background_gallery_images &&
		attributes.background_gallery_images.map( ( image ) => (
		<div
		className={ `h-100` }>
			<img
				key={ image.id }
				src={ image.url }
				alt={ image.alt }
				style={`width:100%;object-fit:cover;${ attributes.background_gallery_images_style }`}
				className={`${attributes.background_gallery_images_class}`}
			/>
		</div>
		) ) }
		</div>
		{/* {attributes.section_image && (
			<img src={attributes.section_image} alt="" className={`w-100 h-100 position-absolute bg-img ${attributes.section_image_class}`} style={`top:0;left:0;object-fit:cover;pointer-events:none;${attributes.section_image_style}`} />
		)} */}

			<RawHTML>{attributes.section_block}</RawHTML>

			<div className={`${attributes.container_class} position-relative`} style={`${attributes.container_style}z-index:1;`} id={attributes.container_id}>
				<div className={attributes.row_class} style={attributes.row_style} id={attributes.row_id}>
					<div className={attributes.col_class} style={attributes.col_style} id={attributes.col_id} data-aos={attributes.col_data_aos} data-aos-delay={attributes.col_data_aos_delay}>
					<InnerBlocks.Content />
					</div>
				</div>
			</div>
		</section>
		</div>
	);
}
