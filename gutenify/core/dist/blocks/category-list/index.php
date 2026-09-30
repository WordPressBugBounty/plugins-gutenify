<?php
/**
 * Category List Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\Category_List;

defined( 'ABSPATH' ) || exit;

class Category_List {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block.
	 *
	 * block.json's `render` field wires render.php as the render
	 * callback automatically; render.php itself bails early when
	 * WooCommerce isn't active.
	 */
	public static function register_block() {
		register_block_type( __DIR__ );
	}
}

Category_List::init();
