<?php
/**
 * Image Marquee Item Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\ImageMarqueeItem;

defined( 'ABSPATH' ) || exit;

class ImageMarqueeItem {

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

ImageMarqueeItem::init();
