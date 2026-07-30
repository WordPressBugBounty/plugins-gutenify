<?php
/**
 * Enqueue global block-related assets for both frontend and admin.
 *
 * @package gutenify
 * @subpackage Assets
 * @since 1.0.0
 */

namespace gutenify;

/**
 * Load assets for our blocks.
 *
 * @package Gutenify
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Block_Assets
 *
 * Handles registration and enqueueing of scripts/styles
 * used by custom Gutenberg block enhancements.
 */
class Block_Assets {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_block_inline_css' ), 200 );
	}

	/**
	 * Enqueue global scripts/styles for block editor and admin UI.
	 *
	 * @return void
	 */
	public static function enqueue_block_assets() {
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		// Global inline script (common dependency).
		wp_enqueue_script( $plugin_main_slug . '-global-inline-handle' );
	}

	/**
	 * Add inline css
	 *
	 * @return void
	 */
	public static function add_block_inline_css() {
		$global_style = get_option( 'gutenify_global_style' );

		if ( ! empty( $global_style ) ) {
			$handle = 'gutenify-global-inline-handle';
			wp_enqueue_style( $handle );
			wp_add_inline_style( $handle, wp_strip_all_tags( $global_style ) );
		}
	}
}

// Initialize the asset loader.
Block_Assets::init();
