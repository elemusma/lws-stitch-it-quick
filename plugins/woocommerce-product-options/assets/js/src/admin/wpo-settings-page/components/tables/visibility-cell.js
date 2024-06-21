/**
 * WordPress dependencies.
 */
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __, _n } from '@wordpress/i18n';

import { useGroupVisibilityObjects } from '../../hooks/groups';

/**
 * Displays the content of the visiblity column.
 *
 * @param {Object} props
 * @param {Object} props.table
 * @param {Object} props.visibilityObjects
 * @return {Object} JSX
 */
const VisibilityCell = ( { table } ) => {
	// eslint-disable-next-line camelcase
	const { visibility, products, exclude_products, categories, exclude_categories } = table.row.original;
	const visibilityObjectsQuery = useGroupVisibilityObjects();
	const visibilityObjects = useMemo( () => {
		return visibilityObjectsQuery.isFetched ? visibilityObjectsQuery.data : null;
	}, [ visibilityObjectsQuery ] );

	const [ formattedProducts, setFormattedProducts ] = useState( [] );
	const [ formattedCategories, setFormattedCategories ] = useState( [] );
	const [ formattedExcludedProducts, setFormattedExcludedProducts ] = useState( [] );
	const [ formattedExcludedCategories, setFormattedExcludedCategories ] = useState( [] );

	/**
	 * On component mount, trigger an automated search for selected products.
	 */
	useEffect( () => {
		setFormattedProducts(
			visibilityObjects?.products?.filter( ( object ) => {
				return products?.includes( object.id );
			} )
		);
		setFormattedCategories(
			visibilityObjects?.categories?.filter( ( object ) => {
				return categories?.includes( object.term_id );
			} )
		);
		setFormattedExcludedProducts(
			visibilityObjects?.products?.filter( ( object ) => {
				// eslint-disable-next-line camelcase
				return exclude_products?.includes( object.id );
			} )
		);
		setFormattedExcludedCategories(
			visibilityObjects?.categories?.filter( ( object ) => {
				// eslint-disable-next-line camelcase
				return exclude_categories?.includes( object.term_id );
			} )
		);
		// eslint-disable-next-line camelcase
	}, [ products, exclude_products, categories, exclude_categories, visibilityObjects ] );

	/**
	 * Get the formatted list of renderable visibilities.
	 *
	 * @return {React.ReactElement} Formatted list of products and categories
	 */
	const getItemsFormatted = () => {
		if ( visibilityObjects === null ) {
			return '';
		}

		const productCount = formattedProducts?.length ?? 0;
		const categoryCount = formattedCategories?.length ?? 0;
		const excludedProductCount = formattedExcludedProducts?.length ?? 0;
		const excludedCategoryCount = formattedExcludedCategories?.length ?? 0;
		const objectCount = productCount + categoryCount + excludedProductCount + excludedCategoryCount;

		const productNames = formattedProducts?.map( ( product ) => product.name ).join( ', ' ) ?? null;
		const excludedProductNames = formattedExcludedProducts?.map( ( product ) => product.name ).join( ', ' ) ?? null;
		const categoryNames = formattedCategories?.map( ( category ) => category.name ).join( ', ' ) ?? null;
		const excludedCategoryNames =
			formattedExcludedCategories?.map( ( category ) => category.name ).join( ', ' ) ?? null;

		return (
			<div className="wpo-visibility-cell">
				{ ( objectCount === 0 || visibility === 'global' ) && (
					<span className="barn2-selection-item" key={ 'all-product-list' }>
						{ __( 'All products', 'woocommerce-product-options' ) }
					</span>
				) }
				{
					<>
						{ productNames && (
							<span className="barn2-selection-item" key={ 'products-list' }>
								<strong>
									{ _n( 'Product: ', 'Products: ', productCount, 'woocommerce-product-options' ) }
								</strong>
								<span className="barn2-selection-list">{ productNames }</span>
							</span>
						) }
						{ categoryNames && (
							<span className="barn2-selection-item" key={ 'category-list' }>
								<strong>
									{ _n( 'Category: ', 'Categories: ', categoryCount, 'woocommerce-product-options' ) }
								</strong>
								<span className="barn2-selection-list">{ categoryNames }</span>
							</span>
						) }
						{ excludedProductNames && (
							<span className="barn2-selection-item" key={ 'excluded-products-list' }>
								<strong>
									{ _n(
										'Excluding product: ',
										'Excluding products: ',
										excludedProductCount,
										'woocommerce-product-options'
									) }
								</strong>
								<span className="barn2-selection-list">{ excludedProductNames }</span>
							</span>
						) }
						{ excludedCategoryNames && (
							<span className="barn2-selection-item" key={ 'excluded-category-list' }>
								<strong>
									{ _n(
										'Excluding category: ',
										'Excluding categories: ',
										excludedCategoryCount,
										'woocommerce-product-options'
									) }
								</strong>
								<span className="barn2-selection-list">{ excludedCategoryNames }</span>
							</span>
						) }
					</>
				}
			</div>
		);
	};

	return <>{ getItemsFormatted() }</>;
};

export default VisibilityCell;
