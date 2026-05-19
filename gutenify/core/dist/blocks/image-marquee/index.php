<?php
/**
 * Image Marquee Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\ImageMarquee;

defined( 'ABSPATH' ) || exit;

class ImageMarquee {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block.
	 */
	public static function register_block() {
		$constants = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		register_block_type( __DIR__, array(
			'render_callback' => array( __CLASS__, 'render_callback' ),
		) );
	}

	/**
	 * Render callback to enqueue Swiper.
	 */
	public static function render_callback( $attributes, $content ) {
		$constants = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		// Enqueue Swiper JS and CSS.
		wp_enqueue_script( $plugin_main_slug . '-swiper' );
		wp_enqueue_style( $plugin_main_slug . '-swiper' );

		return $content;
	}
}

ImageMarquee::init();
