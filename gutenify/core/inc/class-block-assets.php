<?php
namespace gutenify;
/**
 * Load assets for our blocks.
 *
 * @package Gutenify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load general assets for our blocks.
 *
 * @since 1.0.0
 */
class Block_Assets {

	/**
	 * The Constructor.
	 */
	public static function init() {
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ), 1 );
		// add_action( 'init', array( __CLASS__, 'register_block_assets' ) );
	}

	public static function register_block_assets() {
		$active_blocks = Helpers::active_blocks();
		$plugin_constants = Helpers::plugin_constants();
		$plugin_main_slug = $plugin_constants['plugin_main_slug'];
		$base_url =  Helpers::core_base_url();
		$base_dir =  Helpers::core_base_dir();

		foreach( $active_blocks as $block_name ) {

			$asset_file = Helpers::asset_file_values( sprintf( '%s' . 'dist/blocks/' . $block_name . '/index.asset.php', $base_dir ) );

			$handle = $plugin_main_slug . '--' . $block_name;
			wp_register_script( $handle, $base_url . 'dist/blocks/' . $block_name . '/index.js', $asset_file['dependencies'], $asset_file['version'], true );

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/index.css', $base_dir ) ) ) {
				wp_register_style( $handle . '--editor', $base_url . 'dist/blocks/' . $block_name . '/index.css', array(), $asset_file['version'] );
			}

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/style-index.css', $base_dir ) ) ) {
				wp_register_style( $handle . '--frontend', $base_url . 'dist/blocks/' . $block_name . '/style-index.css', array(), $asset_file['version'] );
			}

			if ( file_exists( sprintf( '%s' . 'dist/blocks/' . $block_name . '/scripts/script.js', $base_dir ) ) ) {
				$asset_file = Helpers::asset_file_values( sprintf( '%s' . 'dist/blocks/' . $block_name . '/scripts/script.asset.php', $base_dir ) );
				wp_register_script( $handle . '--view-script', $base_url . 'dist/blocks/' . $block_name . '/scripts/script.js', $asset_file['dependencies'], $asset_file['version'], true );
			}
		}
	}

	public static function enqueue_block_assets(   ) {
		$constants = Helpers::plugin_constants();
		$is_pro_active = Helpers::is_pro_active();
		$plugin_main_slug = $constants['plugin_main_slug'];

		wp_enqueue_script( $plugin_main_slug . '-global-inline-handle' );
		if ( is_admin() ) {
			wp_enqueue_script( $plugin_main_slug . '-components' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-inspector-controls' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-dynamic-css' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-custom-attributes' );
			wp_enqueue_script( $plugin_main_slug . '-extend-block-custom-classname' );
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

Block_Assets::init();
