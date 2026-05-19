<?php
/**
 * Search Toggle Container Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\SearchToggleContainer;

defined( 'ABSPATH' ) || exit;

class SearchToggleContainer {

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

SearchToggleContainer::init();
