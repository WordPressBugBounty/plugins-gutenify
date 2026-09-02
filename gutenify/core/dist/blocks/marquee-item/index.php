<?php
/**
 * Marquee Item Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\MarqueeItem;

defined( 'ABSPATH' ) || exit;

class MarqueeItem {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block.
	 */
	public static function register_block() {
		register_block_type( __DIR__ );
	}
}

MarqueeItem::init();
