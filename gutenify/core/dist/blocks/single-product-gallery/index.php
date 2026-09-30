<?php
/**
 * Single Product Gallery Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\Single_Product_Gallery;

defined( 'ABSPATH' ) || exit;

class Single_Product_Gallery {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block.
	 *
	 * Skip registering entirely when WooCommerce isn't active — the
	 * block only makes sense in a product context. render.php also
	 * bails independently, as defense in depth.
	 */
	public static function register_block() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		register_block_type( __DIR__ );
	}
}

Single_Product_Gallery::init();
