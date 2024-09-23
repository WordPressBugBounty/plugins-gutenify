<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Settings_Admin_Page {
	public static $handle = 'gutenify-admin-settings';
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
	}
	public static function register_assets() {
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		$base_url          = Helpers::core_base_url();
		$asset_file_values = include_once 'index.asset.php';
		$deps              = $asset_file_values['dependencies'];
		$deps[]            = $plugin_main_slug . '-global-inline-handle';
		$deps[]            = 'updates';
		$deps[]            = Components::$handle;

		$ver               = $asset_file_values['version'];
		$src               = $base_url . 'dist/non-blocks/admin/pages/settings/index.js';
		wp_register_script( self::$handle, $src, $deps, $ver, true );

		$src = $base_url . 'dist/non-blocks/admin/pages/settings/index.css';
		// [TODO: Avoid use of fontawesome in admin. use wordpress/icons for any icons or use svg].
		wp_register_style( self::$handle, $src, array( 'wp-components', $plugin_main_slug . '-fontawesome' ), $ver );
	}
}

Settings_Admin_Page::init();
