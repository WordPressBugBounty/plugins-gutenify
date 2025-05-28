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
	}

	/**
	 * Enqueue global scripts/styles for block editor and admin UI.
	 *
	 * @return void
	 */
	public static function enqueue_block_assets() {
		// Plugin constants and flags.
		$constants        = Helpers::plugin_constants();
		$is_pro_active    = Helpers::is_pro_active();
		$plugin_main_slug = $constants['plugin_main_slug'];

		// Global inline script (common dependency).
		wp_enqueue_script( $plugin_main_slug . '-global-inline-handle' );

		// Only enqueue admin/editor-specific assets.
		if ( is_admin() ) {
			wp_enqueue_script( $plugin_main_slug . '-components' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-inspector-controls' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-dynamic-css' );
			// wp_enqueue_script( $plugin_main_slug . '-extend-block-custom-attributes' );
			// wp_enqueue_script( $plugin_main_slug . '-extend-block-custom-classname' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-spacing' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-pro-notice' );
			wp_enqueue_script( $plugin_main_slug . '-admin-global' );

			if ( $is_pro_active ) {
				wp_enqueue_script( $plugin_main_slug . '-extend-block-custom-css' );
			}

			wp_enqueue_style( $plugin_main_slug . '-admin-global' );
			wp_enqueue_style( $plugin_main_slug . '-fontawesome' );
			wp_enqueue_style( $plugin_main_slug . '-extend-block-pro-notice' );
			wp_enqueue_style( $plugin_main_slug . '-fonts' );
		}
	}
}

// Initialize the asset loader.
Block_Assets::init();
