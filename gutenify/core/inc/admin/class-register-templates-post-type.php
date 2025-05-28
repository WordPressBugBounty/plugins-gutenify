<?php
/**
 * Register custom post types for Templates and Template Kits
 *
 * @package Gutenify
 */

namespace gutenify;

// Prevent direct access to the file to ensure it's being loaded within the WordPress environment.
defined( 'ABSPATH' ) || exit;

/**
 * Register_Templates_Post_Type class
 *
 * This class registers two custom post types: Template and Template Kit,
 * along with their respective taxonomies and capabilities.
 */
class Register_Templates_Post_Type {

	/**
	 * Constructor for Register_Templates_Post_Type
	 *
	 * Initializes the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_custom_post_type' ) );
	}

	/**
	 * Register the custom post types: Template and Template Kit
	 */
	public function add_custom_post_type() {
		$post_type_prefix = 'gutenify';
		register_post_type(
			$post_type_prefix . '_template',
			array(
				'labels'              => array(
					'name'               => _x( 'Templates', 'Post Type General Name' ),
					'singular_name'      => _x( 'Template', 'Post Type Singular Name' ),
					'menu_name'          => __( 'Templates' ),
					'parent_item_colon'  => __( 'Parent Template' ),
					'all_items'          => __( 'Templates' ),
					'view_item'          => __( 'View Template' ),
					'add_new_item'       => __( 'Add New Template' ),
					'add_new'            => __( 'Add New' ),
					'edit_item'          => __( 'Edit Template' ),
					'update_item'        => __( 'Update Template' ),
					'search_items'       => __( 'Search Template' ),
					'not_found'          => __( 'Not Found' ),
					'not_found_in_trash' => __( 'Not found in Trash' ),
				),
				'public'              => false, // true?
				'publicly_queryable'  => false, // true?
				'has_archive'         => false,
				'show_ui'             => true,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
                // phpcs:ignore
                'menu_icon'           => 'dashicons-welcome-view-site',
				'menu_position'       => 91,
				'hierarchical'        => false,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'show_in_rest'        => true,
				'taxonomies'          => array(
					$post_type_prefix . '_template_category',
				),
				'capability_type'     => 'post',
				'supports'            => array(
					'title',
					'editor',
					'thumbnail',
					'custom-fields',
				),
			)
		);

		register_taxonomy(
			$post_type_prefix . '_template_category',
			$post_type_prefix . '_template',
			array(
				'label'              => esc_html__( 'Categories' ),
				'labels'             => array(
					'menu_name' => esc_html__( 'Categories' ),
				),
				'rewrite'            => false,
				'hierarchical'       => true,
				'publicly_queryable' => false,
				'show_in_nav_menus'  => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		// Register the "Template Kit" custom post type.
		$filter_name = 'gutenify_kit_post_type_args';
		$args        = apply_filters(
			$filter_name,
			array(
				'labels'              => array(
					'name'               => _x( 'Templates Kit', 'Post Type General Name' ),
					'singular_name'      => _x( 'Template  Kit', 'Post Type Singular Name' ),
					'menu_name'          => __( 'Gutenify' ),
					'parent_item_colon'  => __( 'Parent Template Kit' ),
					'all_items'          => __( 'Template Kits' ),
					'view_item'          => __( 'View Template  Kit' ),
					'add_new_item'       => __( 'Add New Template  Kit' ),
					'add_new'            => __( 'Add New' ),
					'edit_item'          => __( 'Edit Template  Kit' ),
					'update_item'        => __( 'Update Template  Kit' ),
					'search_items'       => __( 'Search Template  Kit' ),
					'not_found'          => __( 'Not Found' ),
					'not_found_in_trash' => __( 'Not found in Trash' ),
				),
				'public'              => false, // true?
				'publicly_queryable'  => false, // true?
				'has_archive'         => false,
				'show_ui'             => true,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'hierarchical'        => false,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => true,
				'capability_type'     => 'post',
				'supports'            => array(
					'title',
					'editor',
					'thumbnail',
				),
			)
		);
		register_post_type( $post_type_prefix . '_kit', $args );
	}
}

// Initialize the Register_Templates_Post_Type class.
new Register_Templates_Post_Type();
