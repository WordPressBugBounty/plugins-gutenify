<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Save_Template {
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'register_assets' ) );

	}
	public static function register_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir =  Helpers::core_base_dir();
		$base_url =  Helpers::core_base_url();
		$assets = Helpers::asset_file_values(sprintf( '%sdist/extend/save-template/index.asset.php', $base_dir ));
		$handle = $main_slug . 'save-template';
		wp_register_script( $handle, $base_url . 'dist/extend/save-template/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );
	}
}

Save_Template::init();
