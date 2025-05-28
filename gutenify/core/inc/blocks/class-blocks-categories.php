<?php
/**
 * Register a custom block category
 *
 * @package   gutenify
 * @subpackage Blocks
 */

namespace gutenify;

/**
 * Prevent direct access to the file.
 *
 * Ensures this file is being loaded within the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Class Blocks_Categories
 * Registers a custom block category for the Liger Lite plugin.
 */
class Blocks_Categories {

	/**
	 * Constructor - Adds filter to register custom block category.
	 */
	public function __construct() {
		// Hook into WordPress to register a custom block category.
		add_filter( 'block_categories_all', array( __CLASS__, 'register_block_categories' ) );
	}

	/**
	 * Registers the custom block category.
	 *
	 * @param array $categories Existing block categories.
	 * @return array Modified list of block categories.
	 */
	public static function register_block_categories( $categories ) {

		$custom_category = array(
			'slug'  => 'gutenify',
			'title' => __( 'Gutenify Blocks', 'gutenify' ),
		);

		return array_merge( array( $custom_category ), $categories );
	}
}

// Initialize class.
new Blocks_Categories();
