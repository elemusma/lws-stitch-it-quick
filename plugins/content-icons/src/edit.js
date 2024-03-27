/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	InspectorControls,
	useBlockProps,
	InnerBlocks,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	__experimentalInputControl as InputControl,
	TextControl,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		section_style,
		section_class,
		section_id,
		section_image,
		section_image_class,
		section_image_style,
		section_block,
		container_style,
		container_class,
		container_id,
		row_style,
		row_class,
		row_id,
		col_left_style,
		col_left_class,
		col_left_id,
		col_left_icon,
		col_left_title,
		col_left_description,
		col_right_style,
		col_right_class,
		col_right_id,
		col_right_icon,
		col_right_title,
		col_right_description,
		columns,
	} = attributes;

	const [ value, setValue ] = useState( '' );

	const addColumn = () => {
		setAttributes( {
			columns: [
				...columns,
				{
					col_class: '',
					col_style: '',
					col_id: '',
					img: '',
					title: 'new column',
					content: 'new column content',
				},
			],
		} );
	};

	const updateColumn = ( columnIndex, field, value ) => {
		setAttributes( {
			columns: columns.map( ( column, index ) => {
				if ( index === columnIndex ) {
					return {
						...column,
						[ field ]: value,
					};
				}
				return column;
			} ),
		} );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section' ) } initialOpen={ false }>
					<InputControl
						label="Section Style"
						value={ section_style }
						onChange={ ( nextValue ) =>
							setAttributes( { section_style: nextValue } )
						}
					/>
					<InputControl
						label="Section Class"
						value={ section_class }
						onChange={ ( nextValue ) =>
							setAttributes( { section_class: nextValue } )
						}
					/>
					<InputControl
						label="Section ID"
						value={ section_id }
						onChange={ ( nextValue ) =>
							setAttributes( { section_id: nextValue } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Background Image' ) }
					initialOpen={ false }
				>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { section_image: media.url } )
							}
							type="image"
							allowedTypes={ [ 'image' ] }
							value={ section_image }
							render={ ( { open } ) => (
								<div>
									{ section_image && (
										<Button
											isLink
											isDestructive
											onClick={ () =>
												setAttributes( {
													section_image: '',
												} )
											}
										>
											{ __( 'Remove Section Image' ) }
										</Button>
									) }
									<Button
										onClick={ open }
										icon="upload"
										className="editor-media-placeholder__button is-button is-default is-large"
									>
										{ __( 'Select Section Image' ) }
									</Button>
								</div>
							) }
						/>
					</MediaUploadCheck>

					<InputControl
						label="Background Image Class"
						value={ section_image_class }
						onChange={ ( nextValue ) =>
							setAttributes( { section_image_class: nextValue } )
						}
					/>
					<InputControl
						label="Background Image Style"
						value={ section_image_style }
						onChange={ ( nextValue ) =>
							setAttributes( { section_image_style: nextValue } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Code Block' ) } initialOpen={ false }>
					{ /* <InputControl
						label="Code Block"
						value={section_block}
						onChange={(nextValue) => setAttributes({ section_block: nextValue })}
					/> */ }
					<label style={ { lineHeight: '2' } }>Code Block</label>
					<textarea
						id="sectionStyleTextarea"
						value={ attributes.section_block }
						onChange={ ( event ) =>
							setAttributes( {
								section_block: event.target.value,
							} )
						}
						placeholder="Enter section block here"
						style={ { width: '100%', height: '100px' } }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Container' ) } initialOpen={ false }>
					<InputControl
						label="Container Section Style"
						value={ container_style }
						onChange={ ( nextValue ) =>
							setAttributes( { container_style: nextValue } )
						}
					/>
					<InputControl
						label="Container Section Class"
						value={ container_class }
						onChange={ ( nextValue ) =>
							setAttributes( { container_class: nextValue } )
						}
					/>
					<InputControl
						label="Container Section ID"
						value={ container_id }
						onChange={ ( nextValue ) =>
							setAttributes( { container_id: nextValue } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Row' ) } initialOpen={ false }>
					<InputControl
						label="Row Style"
						value={ row_style }
						onChange={ ( nextValue ) =>
							setAttributes( { row_style: nextValue } )
						}
					/>
					<InputControl
						label="Row Class"
						value={ row_class }
						onChange={ ( nextValue ) =>
							setAttributes( { row_class: nextValue } )
						}
					/>
					<InputControl
						label="Row ID"
						value={ row_id }
						onChange={ ( nextValue ) =>
							setAttributes( { row_id: nextValue } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Column Left' ) } initialOpen={ false }>
					<InputControl
						label="Column Style"
						value={ col_left_style }
						onChange={ ( nextValue ) =>
							setAttributes( { col_left_style: nextValue } )
						}
					/>
					<InputControl
						label="Column Class"
						value={ col_left_class }
						onChange={ ( nextValue ) =>
							setAttributes( { col_left_class: nextValue } )
						}
					/>
					<InputControl
						label="Column ID"
						value={ col_left_id }
						onChange={ ( nextValue ) =>
							setAttributes( { col_left_id: nextValue } )
						}
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { col_left_icon: media.url } )
							}
							type="image"
							allowedTypes={ [ 'image' ] }
							value={ col_left_icon }
							render={ ( { open } ) => (
								<div>
									{ col_left_icon && (
										<Button
											isLink
											isDestructive
											onClick={ () =>
												setAttributes( {
													col_left_icon: '',
												} )
											}
										>
											{ __( 'Remove Col Image' ) }
										</Button>
									) }
									<Button
										onClick={ open }
										icon="upload"
										className="editor-media-placeholder__button is-button is-default is-large"
									>
										{ __( 'Select Col Image' ) }
									</Button>
								</div>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
				<PanelBody title={ __( 'Column Right' ) } initialOpen={ false }>
					<InputControl
						label="Column Style"
						value={ col_right_style }
						onChange={ ( nextValue ) =>
							setAttributes( { col_right_style: nextValue } )
						}
					/>
					<InputControl
						label="Column Class"
						value={ col_right_class }
						onChange={ ( nextValue ) =>
							setAttributes( { col_right_class: nextValue } )
						}
					/>
					<InputControl
						label="Column ID"
						value={ col_right_id }
						onChange={ ( nextValue ) =>
							setAttributes( { col_right_id: nextValue } )
						}
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { col_right_icon: media.url } )
							}
							type="image"
							allowedTypes={ [ 'image' ] }
							value={ col_right_icon }
							render={ ( { open } ) => (
								<div>
									{ col_right_icon && (
										<Button
											isLink
											isDestructive
											onClick={ () =>
												setAttributes( {
													col_right_icon: '',
												} )
											}
										>
											{ __( 'Remove Col Image' ) }
										</Button>
									) }
									<Button
										onClick={ open }
										icon="upload"
										className="editor-media-placeholder__button is-button is-default is-large"
									>
										{ __( 'Select Col Image' ) }
									</Button>
								</div>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
				<PanelBody
					title={ __( 'Column Settings' ) }
					initialOpen={ false }
				>
					<button onClick={ () => addColumn() }>
						Add New Column
					</button>
				</PanelBody>
			</InspectorControls>
			<section { ...useBlockProps() }>
				<img src={ section_image } alt="" />
				{ console.log( section_image ) }
				<div className="column-wrapper">
					{ columns.map( ( column, index ) => {
						return (
							<div className={ `column ${ column.col_class }` }>
								<input
									type="text"
									value={ column.col_class }
									onChange={ ( content ) =>
										updateColumn(
											index,
											'col_class',
											content.target.value
										)
									}
								/>
								<img 
									src={column.img}
								/>
								<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								updateColumn(index, 'img', media.url)
							}
							type="image"
							allowedTypes={ [ 'image' ] }
							value={ column.img }
							render={ ( { open } ) => (
								<div>
									{ column.img && (
										<Button
											isLink
											isDestructive
											onClick={ () =>
												updateColumn(index, 'img', '')
											}
										>
											{ __( 'Remove Col Image' ) }
										</Button>
									) }
									<Button
										onClick={ open }
										icon="upload"
										className="editor-media-placeholder__button is-button is-default is-large"
									>
										{ __( 'Select Col Image' ) }
									</Button>
								</div>
							) }
						/>
					</MediaUploadCheck>
					
								<h2>{ column.title }</h2>
								<RichText
									value={ column.title }
									onChange={ ( content ) =>
										updateColumn( index, 'title', content )
									}
									placeholder={ __( 'Column Title' ) }
								/>
								<textarea 
								value={ column.content }
								onChange={ ( content ) =>
									updateColumn( index, 'content', content.target.value )
								}
								placeholder={ __( 'Column Content' ) }
								/>
								{/* <RichText
									
								/> */}
								<p>{ column.content }</p>
							</div>
						);
					} ) }
				</div>
				<div style={ { display: 'flex' } }>
					<div
						style={ {
							flex: '1',
							marginRight: '20px',
							width: '50%',
						} }
					>
						<img
							src={ col_left_icon }
							alt=""
							style={ { width: '100px', height: '100px' } }
						/>
						<InputControl
							label="Column Left Title"
							value={ col_left_title }
							onChange={ ( nextValue ) =>
								setAttributes( { col_left_title: nextValue } )
							}
						/>
						<textarea
							id=""
							value={ attributes.col_left_description }
							onChange={ ( event ) =>
								setAttributes( {
									col_left_description: event.target.value,
								} )
							}
							placeholder="Enter content here"
							style={ { width: '100%', height: '100px' } }
						/>
					</div>
					<div style={ { flex: '1', width: '50%' } }>
						<img
							src={ col_right_icon }
							alt=""
							style={ { width: '100px', height: '100px' } }
						/>
						<InputControl
							label="Column Right Title"
							value={ col_right_title }
							onChange={ ( nextValue ) =>
								setAttributes( { col_right_title: nextValue } )
							}
						/>
						<textarea
							id=""
							value={ attributes.col_right_description }
							onChange={ ( event ) =>
								setAttributes( {
									col_right_description: event.target.value,
								} )
							}
							placeholder="Enter content here"
							style={ { width: '100%', height: '100px' } }
						/>
					</div>
				</div>
			</section>
		</>
	);
}
