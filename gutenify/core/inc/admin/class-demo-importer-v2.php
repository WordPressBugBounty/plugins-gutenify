<?php
/**
 * Register and enqueue admin assets for the Demo Importer V2 page.
 *
 * @package gutenify
 * @subpackage Admin
 * @since 1.0.0
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Class Demo_Importer_V2
 *
 * Handles registration of scripts and styles for the Demo Importer V2 page.
 */
class Demo_Importer_V2 {

	/**
	 * Unique handle used to register scripts and styles.
	 *
	 * @var string
	 */
	public static $handle = 'gutenify-admin-demo-importer-v2';

	/**
	 * Initialize the class by hooking into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Register the JavaScript and CSS assets used on the admin page.
	 *
	 * @return void
	 */
	public static function register_assets() {
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		$base_url   = Helpers::core_base_url();
		$base_dir   = Helpers::core_base_dir();
		$path       = 'dist/non-blocks/admin/pages/demo-importer-v2/';
		$asset_file = wp_normalize_path( $base_dir . $path . 'index.asset.php' );

		// Ensure the asset file is inside the expected directory.
		if ( strpos( realpath( $asset_file ), realpath( $base_dir . $path ) ) === 0 && file_exists( $asset_file ) ) {
			$asset_file_values = include_once $asset_file;
			$deps              = ( isset( $asset_file_values['dependencies'] ) && is_array( $asset_file_values['dependencies'] ) )
			? $asset_file_values['dependencies']
			: array();
			$deps[]            = $plugin_main_slug . '-global-inline-handle';
			$deps[]            = 'updates';
			$ver               = isset( $asset_file_values['version'] ) ? sanitize_text_field( $asset_file_values['version'] ) : '1.7.0';

			wp_register_script( self::$handle, esc_url( $base_url . 'dist/non-blocks/admin/pages/demo-importer-v2/index.js' ), $deps, $ver, true );
			wp_register_style( self::$handle, esc_url( $base_url . 'dist/non-blocks/admin/pages/demo-importer-v2/index.css' ), array( 'wp-components' ), $ver );
		}
	}
}

// Initialize the class and hook it into WordPress.
Demo_Importer_V2::init();
