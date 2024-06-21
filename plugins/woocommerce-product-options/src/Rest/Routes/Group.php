<?php

namespace Barn2\Plugin\WC_Product_Options\Rest\Routes;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Collection;
use Barn2\Plugin\WC_Product_Options\Model\Group as Group_Model;
use Barn2\Plugin\WC_Product_Options\Model\Option as Option_Model;
use Barn2\Plugin\WC_Product_Options\Formula;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Rest\Base_Route;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Rest\Route;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST controller for the group route.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Group extends Base_Route implements Route {

	protected $rest_base = 'groups';

	/**
	 * Register the REST routes.
	 */
	public function register_routes() {

		// GET ALL
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/all',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_all' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// EXPORT
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'export' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// VISIBILITY
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/visibility',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_visibility_objects' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// GET
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'args' => [
					'id' => [
						'type'        => 'integer',
						'required'    => true,
						'description' => __( 'The unique identifier for the group.', 'woocommerce-product-options' )
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// CREATE.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'args' => $this->get_group_schema(),
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// UPDATE
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'args' => $this->get_group_schema(),
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// DELETE
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'args' => [
					'id' => [
						'type'        => 'integer',
						'required'    => true,
						'description' => __( 'The unique identifier for the group.', 'woocommerce-product-options' ),
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				]
			]
		);

		// IMPORT
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import',
			[
				'args' => [
					'groups' => [
						'type'        => 'array',
						'required'    => true,
						'description' => __( 'The array with all the groups being imported.', 'woocommerce-product-options' )
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'import' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// REORDER
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reorder',
			[
				'args' => [
					'reorder' => [
						'type'        => 'array',
						'required'    => true,
						'description' => __( 'An array of group_id => menu_order data.', 'woocommerce-product-options' ),
					],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'reorder' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// DUPLICATE
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/duplicate',
			[
				'args' => $this->get_group_schema(),
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'duplicate' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);
	}

	/**
	 * Retrieve all groups.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_all( $request ) {
		$group_collection = Group_Model::orderBy( 'menu_order', 'asc' )->get();

		if ( ! $group_collection instanceof Collection ) {
			return new WP_Error( 'wpo-rest-group-get-all', __( 'No groups', 'woocommerce-product-options' ) );
		}

		return new WP_REST_Response( $group_collection, 200 );
	}

	/**
	 * Retrieve all groups.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function export( $request ) {
		$group_collection = Group_Model::orderBy( 'menu_order', 'asc' )->get();

		if ( ! $group_collection instanceof Collection ) {
			return new WP_Error( 'wpo-rest-group-get-all', __( 'No groups', 'woocommerce-product-options' ) );
		}

		$groups = [];

		foreach ( $group_collection->all() as $group ) {
			$group_array            = $group->toArray();
			$group_array['options'] = Option_Model::orderBy( 'menu_order', 'asc' )->where( 'group_id', $group->getID() )->get();
			$groups[]               = $group_array;
		}

		$data = [ 'groups' => $groups ];

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Retrieve a group by ID.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get( $request ) {
		$id = $request->get_param( 'id' );

		$group = Group_Model::where( 'id', $id )->get();

		if ( ! is_object( $group ) ) {
			return new WP_Error( 'wpo-rest-group-get', __( 'No group', 'woocommerce-product-options' ) );
		}

		return new WP_REST_Response( $group, 200 );
	}

	/**
	 * Create a group
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( $request ) {
		$data    = $request->get_params();
		$options = $request->get_param( 'options' );

		$option_errors  = [];
		$option_updates = [];

		unset( $data['id'] );

		$data['menu_order'] = Group_Model::max( 'menu_order' ) + 1;

		$group = Group_Model::create( $data );

		if ( ! $group instanceof Group_Model || empty( $group->getID() ) ) {
			return new WP_Error( 'wpo-rest-group-create', __( 'Something went wrong while creating the group', 'woocommerce-product-options' ) );
		}

		if ( ! empty( $options ) && is_array( $options ) ) {
			// check for deleted options
			$this->delete_missing_options( $group->getID(), $options );

			foreach ( $options as $option_data ) {
				unset( $option_data['id'] );

				$option_data['group_id']   = $group->getID();
				$option_data['menu_order'] = Option_Model::where( 'group_id', $group->getID() )->max( 'menu_order' ) + 1;

				$option = Option_Model::create( $option_data );

				if ( ! $option || ! $option instanceof Option_Model || ! $option->getID() ) {
					$option_errors[] = new WP_Error( 'wpo-rest-group-create-option', __( 'Something went wrong: could not create an option.', 'woocommerce-product-options' ) );
				} else {
					$option_updates[ $option->getID() ] = $option;
				}
			}
		}

		return new WP_REST_Response(
			[
				'group_id' => $group->getID(),
				'options'  => [
					'errors'  => $option_errors,
					'updates' => $option_updates,
				],
			],
			200
		);
	}

	/**
	 * Duplicate a group
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate( $request ) {
		$data    = $request->get_params();
		$options = Option_Model::orderBy( 'menu_order', 'asc' )->where( 'group_id', $data['id'] )->get();

		if ( $options instanceof Collection ) {
			$data['options'] = $options->toArray();
		}

		$results = $this->process_group_addition( $data );

		if ( $results === false ) {
			return new WP_Error( 'wpo-rest-group-duplicate', __( 'Something went wrong while duplicating the group', 'woocommerce-product-options' ) );
		}

		$group          = $results['group'];
		$option_errors  = $results['option_errors'];
		$option_updates = $results['option_updates'];

		return new WP_REST_Response(
			[
				'group_id' => $group->getID(),
				'options'  => [
					'errors'  => $option_errors,
					'updates' => $option_updates,
				],
			],
			200
		);
	}

	public function import( $request ) {
		$groups = $request->get_param( 'groups' );

		$group_errors  = [];
		$group_imports = [];

		foreach ( $groups as $group ) {
			$results = $this->process_group_addition( $group );

			if ( $results === false ) {
				$group_errors[] = new WP_Error( 'wpo-rest-group-import', __( 'Something went wrong while importing the group', 'woocommerce-product-options' ) );
			}

			$group          = $results['group'];
			$option_errors  = $results['option_errors'];
			$option_updates = $results['option_updates'];

			if ( ! empty( $option_errors ) ) {
				$group_errors[] = new WP_Error(
					'wpo-rest-group-import-options',
					_n(
						'Something went wrong while importing %d option of the group',
						'Something went wrong while importing %d options of the group',
						count( $option_errors ),
						'woocommerce-product-options'
					)
				);
			} else {
				$group_imports[ $group->getID() ] = [
					'options'  => [
						'errors'  => $option_errors,
						'updates' => $option_updates,
					],
				];
			}
		}

		return new WP_REST_Response(
			[
				'groups' => $group_imports,
				'errors' => $group_errors,
			],
			200
		);
	}

	/**
	 * Update a group
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( $request ) {
		$id      = $request->get_param( 'id' );
		$options = $request->get_param( 'options' );
		$data    = $request->get_params();

		$option_errors   = [];
		$option_warnings = [];
		$option_updates  = [];

		$group = Group_Model::find( $id );

		if ( ! $group || ! $group instanceof Group_Model ) {
			return new WP_Error( 'wpo-rest-group-update', __( 'Something went wrong: could not update the selected group', 'woocommerce-product-options' ) );
		}

		$group->update( $data );

		// check for deleted options
		$this->delete_missing_options( $group->getID(), $options );

		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $option_data ) {
				if ( $option_data['id'] === 0 ) {
					unset( $option_data['id'] );
					$option_data['menu_order'] = Option_Model::where( 'group_id', $group->getID() )->max( 'menu_order' ) + 1;

					$option = Option_Model::create( $option_data );
				} else {
					$option = Option_Model::find( $option_data['id'] );
				}

				if ( ! $option || ! $option instanceof Option_Model ) {
					$option_errors[] = new WP_Error( 'wpo-rest-group-update-option', __( 'Something went wrong: could not update an option.', 'woocommerce-product-options' ) );
					continue;
				}

				$option_updates[ $option->getID() ] = $option->update( $option_data );
			}
		}

		// detect changed option types, number used in formulas
		$warning_messages = $this->validate_formula_options( $group->getID() );

		if ( ! empty( $warning_messages ) ) {
			$option_warnings = array_merge( $option_warnings, $warning_messages );
		}

		return new WP_REST_Response(
			[
				'group_id' => $group->getID(),
				'options'  => [
					'errors'   => $option_errors,
					'updates'  => $option_updates,
					'warnings' => $option_warnings
				]
			],
			200
		);
	}

	/**
	 * Delete a group
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		$group = Group_Model::find( $id );

		if ( ! $group || ! $group instanceof Group_Model ) {
			return new WP_Error( 'wpo-rest-group-delete', __( 'Something went wrong: could not find the group', 'woocommerce-product-options' ) );
		}

		$group->delete();

		Option_Model::where( 'group_id', $group->getID() )->delete();

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Reorder the groups
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder( $request ) {
		$reorder_map = $request->get_param( 'reorder' );

		foreach ( $reorder_map as $index => $group_id ) {
			$group = Group_Model::find( $group_id );

			if ( ! $group || ! $group instanceof Group_Model ) {
				return new WP_Error( 'wpo-rest-group-delete', __( 'Something went wrong with reodering.', 'woocommerce-product-options' ) );
			}

			$group->update( [ 'menu_order' => $index ] );
		}

		return new WP_REST_Response( $reorder_map, 200 );
	}

	/**
	 * Permission callback to access the routes.
	 *
	 * @return bool
	 */
	public function permission_callback() {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Check if there are price formulas that need validity checks.
	 *
	 * @param int $group_id
	 * @return array|null Array of warning messages or null if no warnings.
	 */
	private function validate_formula_options( $group_id ) {
		$options = Option_Model::where( 'group_id', $group_id )->get();

		$formula_options = $options->filter(
			function ( $option ) {
				return $option->type === 'price_formula';
			}
		);

		if ( count( $formula_options ) < 1 ) {
			return null;
		}

		$warning_messages = [];

		// check if the price formula is valid and get the variables
		foreach ( $formula_options as $formula_option ) {
			$price_formula = new Formula( $formula_option );

			if ( ! $price_formula->check_validity() ) {
				continue;
			}

			// check variables exists as number options.
			$variables = $price_formula->get_variables();

			foreach ( $variables as $variable ) {
				if ( $variable['type'] !== 'number_option' ) {
					continue;
				}

				$variable_option = Option_Model::find( $variable['id'] );

				// option no longer exists
				if ( ! $variable_option ) {
					/* translators: 1: option name, 2: formula name */
					$warning_messages[] = sprintf( __( 'The option "%1$s" no longer exists. Please update the formula "%2$s".', 'woocommerce-product-options' ), $variable['name'], $formula_option->name );
					$price_formula->set_valid( false );
					continue;
				}

				// option is not a number option
				if ( $variable_option->type !== 'number' ) {
					/* translators: 1: option name, 2: formula name */
					$warning_messages[] = sprintf( __( 'The option "%1$s" is no longer a number option. Please update the formula "%2$s".', 'woocommerce-product-options' ), $variable['name'], $formula_option->name );
					$price_formula->set_valid( false );
					continue;
				}

				// check if we need to update the variable names.
				if ( str_replace( ' ', '', $variable_option->name ) !== $variable['name'] ) {
					$price_formula->update_variable_name( $variable['id'], str_replace( ' ', '', $variable['name'] ) );

					continue;
				}
			}

			$price_formula->save();
		}

		return $warning_messages;
	}

	/**
	 * Determine which options were deleted by their lack of presence in the request.
	 *
	 * @param int $group_id
	 * @param array $options
	 */
	private function delete_missing_options( $group_id, $options ): void {
		$current_options = Option_Model::where( 'group_id', $group_id )->pluck( 'id' );
		$updated_options = new Collection( $options );
		$deleted_options = $current_options->diff( $updated_options->pluck( 'id' ) );

		if ( $deleted_options->isNotEmpty() ) {
			foreach ( $deleted_options as $option_id ) {
				Option_Model::find( $option_id )->delete();
			}
		}
	}

	/**
	 * Process the addition of a group
	 *
	 * @param array $data The data of the group to be added.
	 * @return Group_Model|false
	 */
	private function process_group_addition( $data ) {
		unset( $data['id'] );

		$option_errors  = [];
		$option_updates = [];

		$data['menu_order'] = Group_Model::max( 'menu_order' ) + 1;

		$options = $data['options'];
		unset( $data['options'] );

		$group = Group_Model::create( $data );

		if ( ! $group instanceof Group_Model || empty( $group->getID() ) ) {
			return false;
		}

		if ( ! empty( $options ) && is_array( $options ) ) {
			$translated_option_ids = [];

			foreach ( $options as $option_data ) {
				$old_option_id = $option_data['id'];
				unset( $option_data['id'] );

				$option_data['group_id']   = $group->getID();
				$option_data['menu_order'] = Option_Model::where( 'group_id', $group->getID() )->max( 'menu_order' ) + 1;

				$option = Option_Model::create( array_filter( $option_data ) );

				if ( ! $option || ! $option instanceof Option_Model || ! $option->getID() ) {
					$option_errors[] = new WP_Error( 'wpo-rest-group-create-option', __( 'Something went wrong: could not create an option.', 'woocommerce-product-options' ) );
				} else {
					$translated_option_ids[ $old_option_id ] = $option->getID();
					$option_updates[ $option->getID() ]      = $option;
				}
			}

			// now that we have all the IDs of the new options, we can update the conditional logic
			foreach ( $options as $option_data ) {
				$old_option_id = $option_data['id'];

				if ( ! empty( $option_data['conditional_logic'] ) ) {
					$conditional_logic = $option_data['conditional_logic'];
					$conditions        = $conditional_logic['conditions'];

					foreach ( $conditions as $index => $condition ) {
						$conditions[ $index ]['id']       = $condition['id'] . '-' . $translated_option_ids[ $condition['optionID'] ];
						$conditions[ $index ]['optionID'] = $translated_option_ids[ $condition['optionID'] ];
					}

					$conditional_logic['conditions'] = $conditions;
					$option                          = Option_Model::find( $translated_option_ids[ $old_option_id ] );

					if ( $option && $option instanceof Option_Model ) {
						$option->update( [ 'conditional_logic' => $conditional_logic ] );
					}
				}
			}
		}

		return [
			'group'          => $group,
			'option_errors'  => $option_errors,
			'option_updates' => $option_updates,
		];
	}

	// GET VISIBILITY OBJECTS
	public function get_visibility_objects( $request ) {
		$group_collection = Group_Model::orderBy( 'menu_order', 'asc' )->get();

		if ( ! $group_collection instanceof Collection ) {
			return new WP_Error( 'wpo-rest-group-get-all', __( 'No groups', 'woocommerce-product-options' ) );
		}

		$visibility_objects = [];
		$products           = [];
		$categories         = [];

		foreach ( $group_collection->toArray() as $group ) {
			$products   = array_merge( $products, $group['products'] ?? [], $group['exclude_products'] ?? [] );
			$categories = array_merge( $categories, $group['categories'] ?? [], $group['exclude_categories'] ?? [] );
		}

		$products = array_map(
			function ( $product ) {
				return [
					'id'   => $product->get_id(),
					'name' => $product->get_name(),
					'slug' => $product->get_slug(),
				];
			},
			wc_get_products(
				[
					'limit' => -1,
					'include' => array_unique( $products )
				]
			)
		);

		$categories = array_values(
			get_terms(
				[
					'include'  => array_unique( $categories ),
					'taxonomy' => 'product_cat',
				]
			)
		);

		$visibility_objects = [
			'products'   => $products,
			'categories' => $categories,
		];

		return new WP_REST_Response( $visibility_objects, 200 );
	}

	/**
	 * Retrieves the schema for the update and create endpoints.
	 *
	 * @return []
	 */
	private function get_group_schema() {
		return [
			'id'                 => [
				'type'        => 'integer',
				'required'    => true,
				'description' => __( 'The unique identifier for the group.', 'woocommerce-product-options' )
			],
			'menu_order'         => [
				'type'        => 'int',
				'required'    => true,
				'description' => __( 'The menu order for the group.', 'woocommerce-product-options' )
			],
			'name'               => [
				'type'        => 'string',
				'required'    => false,
				'description' => __( 'The name for the group.', 'woocommerce-product-options' )
			],
			'display_name'       => [
				'type'        => 'boolean',
				'required'    => false,
				'description' => __( 'Indicates whether the group name should be displayed.', 'woocommerce-product-options' )
			],
			'visibility'         => [
				'type'        => 'string',
				'required'    => false,
				'description' => __( 'The visiblity status for the group.', 'woocommerce-product-options' )
			],
			'products'           => [
				'type'        => 'array',
				'items'       => [
					'type' => 'integer',
				],
				'required'    => false,
				'description' => __( 'The products for the group.', 'woocommerce-product-options' )
			],
			'exclude_products'   => [
				'type'        => 'array',
				'items'       => [
					'type' => 'integer',
				],
				'required'    => false,
				'description' => __( 'The products to exclude for for the group.', 'woocommerce-product-options' )
			],
			'categories'         => [
				'type'        => 'array',
				'items'       => [
					'type' => 'integer',
				],
				'required'    => false,
				'description' => __( 'The categories for the group.', 'woocommerce-product-options' )
			],
			'exclude_categories' => [
				'type'        => 'array',
				'items'       => [
					'type' => 'integer',
				],
				'required'    => false,
				'description' => __( 'The categories to exclude for the group.', 'woocommerce-product-options' )
			],
			'options'            => [
				'type'        => 'array',
				'required'    => false,
				'description' => __( 'The options for the group.', 'woocommerce-product-options' ),
				'items'       => [
					'type'       => 'object',
					'properties' => [
						'id'           => [
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The unique identifier for the option.', 'woocommerce-product-options' )
						],
						'group_id'     => [
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The Group ID to which the option belongs.', 'woocommerce-product-options' )
						],
						'menu_order'   => [
							'type'        => 'int',
							'required'    => false,
							'description' => __( 'The menu order for the option.', 'woocommerce-product-options' )
						],
						'name'         => [
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'The name for the option.', 'woocommerce-product-options' )
						],
						'description'  => [
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'The visiblity status for the option.', 'woocommerce-product-options' )
						],
						'type'         => [
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'The field type for the option.', 'woocommerce-product-options' )
						],
						'choices'      => [
							'type'        => 'object',
							'required'    => false,
							'description' => __( 'The choices for the option.', 'woocommerce-product-options' )
						],
						'required'     => [
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Indicates whether the option is required.', 'woocommerce-product-options' )
						],
						'display_name' => [
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Indicates whether the option name should be displayed.', 'woocommerce-product-options' )
						],
						'settings'     => [
							'type'        => 'object',
							'required'    => false,
							'description' => __( 'Any specific extra settings for the option.', 'woocommerce-product-options' ),
							'properties'  => [
								'datepicker' => [
									'type'        => 'object',
									'required'    => false,
									'description' => __( 'Settings for the datepicker', 'woocommerce-product-options' ),
									'properties'  => [
										'date_format'      => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The date format for the option.', 'woocommerce-product-options' )
										],
										'min_date'         => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The minimum date for the option.', 'woocommerce-product-options' )
										],
										'max_date'         => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The maximum date for the option.', 'woocommerce-product-options' )
										],
										'disable_days'     => [
											'type'        => 'array',
											'required'    => false,
											'description' => __( 'The days to disable for the option.', 'woocommerce-product-options' ),
											'items'       => [
												'type' => 'integer',
											],
										],
										'disable_dates'    => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The dates to disable for the option.', 'woocommerce-product-options' ),
										],
										'disable_past_dates' => [
											'type'        => [ 'boolean', 'null' ],
											'required'    => false,
											'description' => __( 'Indicates whether past dates should be disabled for the option.', 'woocommerce-product-options' )
										],
										'disable_future_dates' => [
											'type'        => [ 'boolean', 'null' ],
											'required'    => false,
											'description' => __( 'Indicates whether future dates should be disabled for the option.', 'woocommerce-product-options' )
										],
										'enable_time'      => [
											'type'        => [ 'boolean', 'null' ],
											'required'    => false,
											'description' => __( 'Indicates whether the time should be enabled for the option.', 'woocommerce-product-options' )
										],
										'min_time'         => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The minimum time for the option.', 'woocommerce-product-options' )
										],
										'max_time'         => [
											'type'        => 'string',
											'required'    => false,
											'description' => __( 'The maximum time for the option.', 'woocommerce-product-options' )
										],
										'minute_increment' => [
											'type'        => 'integer',
											'required'    => false,
											'minimum'     => 1,
											'maximum'     => 60,
											'description' => __( 'The minute increment for the option.', 'woocommerce-product-options' )
										],
										'hour_increment'   => [
											'type'        => 'integer',
											'required'    => false,
											'minimum'     => 1,
											'maximum'     => 24,
											'description' => __( 'The hour increment for the option.', 'woocommerce-product-options' )
										],
									]
								],
							]
						]
					]
				]
			],
		];
	}
}
