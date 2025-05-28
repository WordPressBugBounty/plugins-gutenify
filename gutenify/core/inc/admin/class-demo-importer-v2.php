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

		$base_url          = Helpers::core_base_url();
		$base_dir          = Helpers::core_base_dir();
		$path              = 'dist/non-blocks/admin/pages/demo-importer-v2/';
		$asset_file_values = include_once $base_dir . $path . 'index.asset.php';
		$deps              = $asset_file_values['dependencies'];
		$deps[]            = $plugin_main_slug . '-global-inline-handle';
		$deps[]            = 'updates';
		$ver               = $asset_file_values['version'];

		wp_register_script( self::$handle, $base_url . 'dist/non-blocks/admin/pages/demo-importer-v2/index.js', $deps, $ver, true );
		wp_register_style( self::$handle, $base_url . 'dist/non-blocks/admin/pages/demo-importer-v2/index.css', array( 'wp-components' ), $ver );
	}
}

// Initialize the class and hook it into WordPress.
Demo_Importer_V2::init();
