<?php
/**
 * Search Toggle Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\SearchToggle;

defined( 'ABSPATH' ) || exit;

class SearchToggle {

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

SearchToggle::init();
