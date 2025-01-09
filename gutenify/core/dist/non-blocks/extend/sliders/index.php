<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Extend_Sliders {
	public static function init() {
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ) );
	}

	public static function enqueue_block_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir =  Helpers::core_base_dir();
		$base_url =  Helpers::core_base_url();
		$assets = Helpers::asset_file_values(sprintf( '%sdist/non-blocks/extend/sliders/index.asset.php', $base_dir ));
		$handle = $main_slug . '-extend-sliders';

		$assets['']['dependencies'] = "gutenify-swiper";
		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/sliders/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/sliders/style-index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}
}

Extend_Sliders::init();
