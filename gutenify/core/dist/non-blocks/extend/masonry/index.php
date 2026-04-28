<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Masonry {
	public static function init() {
		add_filter('enqueue_block_assets', array(__CLASS__, 'register_assets') );
		// add_filter( 'block_type_metadata', array( __CLASS__, 'add_assets' ) );
	}
	public static function register_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir =  Helpers::core_base_dir();
		$base_url =  Helpers::core_base_url();
		$assets = Helpers::asset_file_values(sprintf( '%sdist/non-blocks/extend/masonry/index.asset.php', $base_dir ));
		$handle = $main_slug . '-extend-masonry';

		$assets['dependencies'][] = "masonry";
		$assets['dependencies'][] = "jquery-masonry";
		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/masonry/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/masonry/style-index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}
	public static function add_assets( $metadata ) {
		if ( ! empty( $metadata['name'] ) && 'core/group' === $metadata['name'] ) {

			// foreach( $metadata as $meta ) {
			// }
		}

		return $metadata;
	}
}
Masonry::init();
