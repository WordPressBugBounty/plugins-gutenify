<?php
/**
 * Filter Sidebar Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\Filter_Sidebar;

defined( 'ABSPATH' ) || exit;

class Filter_Sidebar {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block.
	 *
	 * A static-save block whose InnerBlocks content is native
	 * WooCommerce filter blocks (woocommerce/product-filters etc.) —
	 * skip registering it entirely when WooCommerce isn't active, since
	 * an empty drawer with no filters to show has nothing useful to
	 * offer the inserter.
	 */
	public static function register_block() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		register_block_type( __DIR__ );
	}
}

Filter_Sidebar::init();
