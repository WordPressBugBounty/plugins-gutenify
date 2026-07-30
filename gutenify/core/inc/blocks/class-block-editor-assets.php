<?php
/**
 * Enqueue global block-related assets for admin.
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
 * Class Block_Editor_Assets
 *
 * Handles registration and enqueueing of scripts/styles
 * used by custom Gutenberg block enhancements.
 */
class Block_Editor_Assets {
	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
	}

	public static function enqueue_block_editor_assets() {
		// Plugin constants and flags.
		$constants            = Helpers::plugin_constants();
		$is_pro_active        = Helpers::is_pro_active();
		$plugin_main_slug     = $constants['plugin_main_slug'];
		$plugin_main_base_url = Helpers::core_base_url();
		$plugin_main_base_dir = Helpers::core_base_dir();

		$asset_paths = array(
			// Admin.
			'admin-global'                    => array(
				'path' => 'dist/non-blocks/admin/global',
			),
			'extend-block-inspector-controls' => array(
				'path' => 'dist/non-blocks/extend/block-inspector-controls',
			),
			'extend-block-dynamic-css'        => array(
				'path' => 'dist/non-blocks/extend/block-dynamic-css',
			),
			'extend-block-spacing'            => array(
				'path' => 'dist/non-blocks/extend/block-spacing',
			),
			'extend-block-position'           => array(
				'path' => 'dist/non-blocks/extend/block-position',
			),
			// 'extend-block-custom-css'         => array(
			// 'path' => 'dist/non-blocks/extend/block-custom-css',
			// ),
			'extend-block-pro-notice'         => array(
				'path' => 'dist/non-blocks/extend/block-pro-notice',
			),
			// 'extend-custom-list'              => array(
			// 'path' => 'dist/non-blocks/extend/custom-list',
			// ),
		);

		if ( $is_pro_active ) {
			$asset_paths['extend-block-custom-css'] = array(
				'path' => 'dist/non-blocks/extend/block-custom-css',
			);
		}

		foreach ( $asset_paths as $handle => $asset ) {
			$path       = $asset['path'];
			$asset_file = gutenify_get_block_asset_file_values( sprintf( '%s' . $path . '/index.asset.php', $plugin_main_base_dir ) );
			$handle     = $plugin_main_slug . '-' . str_replace( '/', '-', $handle );

			$deps = ! empty( $asset['js_dependencies'] ) ? array_merge( $asset_file['dependencies'], $asset['js_dependencies'] ) : $asset_file['dependencies'];
			wp_enqueue_script( $handle, $plugin_main_base_url . $path . '/index.js', $deps, $asset_file['version'], true );

			if ( file_exists( $plugin_main_base_dir . $path . '/index.css' ) ) {
				$deps = ! empty( $asset['style_dependencies'] ) ? $asset['style_dependencies'] : array();
				wp_enqueue_style( $handle, $plugin_main_base_url . $path . '/index.css', $deps, $asset_file['version'] );
			}
		}

		wp_enqueue_style( $plugin_main_slug . '-fontawesome' );
		wp_enqueue_style( $plugin_main_slug . '-fonts' );
	}
}

// Initialize the asset loader.
Block_Editor_Assets::init();
