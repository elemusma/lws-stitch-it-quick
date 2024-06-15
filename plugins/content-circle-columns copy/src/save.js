/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';
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

	const Content = ({ column }) => (
		<div>
		{column.img && (
			<img
				src={column.img}
				alt=""
				className={`w-100 h-100 position-absolute bg-img ${column.img_class}`}
				style={`top:0;left:0;object-fit:cover;pointer-events:none;${column.img_style}`}
			/>
		)}
		<div className={``} data-aos={column.data_aos} data-aos-delay={column.data_aos_delay}>
			<div className={`position-relative`}>
				<div className={`${column.inner_col_class}`} style={`${column.inner_col_style}`}>
				<div>
				<p className={`bold small`} style={{ cursor: 'pointer',color:'var(--accent-primary)',margin:'0' }}><RichText.Content value={column.title} /></p>
				<p className={`small`} style={{ margin: '0px' }}><RichText.Content value={column.content} /></p>
				</div>
				</div>
			</div>
		</div>
		</div>
	);

	return (
		<div {...blockProps}>
			<section
				className={`position-relative ${attributes.section_class}`}
				style={`padding:50px 0;${attributes.section_style}`}
				id={attributes.section_id}
			>
				{attributes.section_image && (
					<img
						src={attributes.section_image}
						alt=""
						className={`w-100 h-100 position-absolute bg-img ${attributes.section_image_class}`}
						style={`top:0;left:0;object-fit:cover;pointer-events:none;${attributes.section_image_style}`}
					/>
				)}

				<RawHTML>{attributes.section_block}</RawHTML>

				<div
					className={attributes.container_class}
					style={attributes.container_style}
					id={attributes.container_id}
				>
					<div
						className={attributes.row_class}
						style={attributes.row_style}
						id={attributes.row_id}
					>
					<div
						className={attributes.main_col_content_class}
						style={attributes.main_col_content_style}
					>
						<InnerBlocks.Content />
					</div>
						{attributes.columns.map((column, index) => (
							<div key={index} className={`position-relative text-center ${column.col_class}`} style={column.col_style}>
								{/* {column.url ? ( */}
									<a href={column.url} target={column.linkTarget} title={column.linkTitle}>
										<Content column={column} />
									</a>
								{/* ) : ( */}
									{/* <Content column={column} /> */}
								{/* )} */}
							</div>
						))}
					</div>
				</div>
			</section>
		</div>
	);
}