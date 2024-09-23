<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Components {
	public static $handle = 'gutenify-components';
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ), 1 );
	}
	public static function register_assets() {
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$base_url          = Helpers::core_base_url();
		$asset_file_values = include_once 'index.asset.php';
		$deps              = $asset_file_values['dependencies'];
		$deps[] = $plugin_main_slug . '-global-inline-handle';
		$ver               = $asset_file_values['version'];
		$src               = $base_url . 'dist/non-blocks/components/index.js';
		wp_register_script( self::$handle, $src, $deps, $ver );

		wp_localize_script(
			self::$handle,
			'gutenify_components_vars',
			array(
				'brand_color' => '#2196f3',
			)
		);

		$src = $base_url . 'dist/non-blocks/components/index.css';
		wp_register_style( self::$handle, $src, array( 'wp-components' ), $ver );
	}

	public static function enqueue_block_editor_assets() {
		wp_enqueue_script( self::$handle );
		wp_enqueue_style( self::$handle );
	}
}

Components::init();
