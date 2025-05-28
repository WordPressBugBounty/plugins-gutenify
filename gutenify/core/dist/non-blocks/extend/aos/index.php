<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class AOS {
	public static function init() {
		add_filter( 'enqueue_block_editor_assets', array( __CLASS__, 'register_assets' ) );
		add_filter( 'enqueue_block_assets', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'render_block', array( __CLASS__, 'aos_render' ), 20, 3 );
	}

	public static function enqueue_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$handle    = $main_slug . '-lib-aos';
		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );

		wp_add_inline_script(
			$handle,
			"document.addEventListener(
	'DOMContentLoaded',
	() => setTimeout(() => window.AOS ? window.AOS.init( ) : AOS.init( ), 50)
)"
		);
	}

	public static function register_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir  = Helpers::core_base_dir();
		$base_url  = Helpers::core_base_url();
		$assets    = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/aos/index.asset.php', $base_dir ) );
		$handle    = $main_slug . '-extend-aos';

		$assets['dependencies'][] = $main_slug . '-lib-aos';
		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/aos/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/aos/index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}

	public static function aos_render( $content, $block, $instance ) {
		if ( empty( $block['blockName'] ) ) {
			return $content;
		}
		$content = new \WP_HTML_Tag_Processor( $content );
			$content->next_tag();

			$animation_type = ! empty( $block['attrs']['animationsSettings']['animation'] ) ? esc_attr( $block['attrs']['animationsSettings']['animation'] ) : '';
			$variation_type = ! empty( $block['attrs']['animationsSettings']['variation'] ) ? esc_attr( $block['attrs']['animationsSettings']['variation'] ) : '';

			$animation = ! empty( $animation_type ) ? ( $animation_type . ( ! empty( $variation_type ) ? '-' . $variation_type : '' ) ) : '';
		if ( ! empty( $animation ) ) {
			$content->set_attribute( 'data-aos', $animation );
		}

			$duration = ! empty( $block['attrs']['animationsSettings']['duration'] ) ? esc_attr( $block['attrs']['animationsSettings']['duration'] ) : '';
		if ( ! empty( $duration ) ) {
			$content->set_attribute( 'data-aos-duration', $duration );
		}
			$delay = ! empty( $block['attrs']['animationsSettings']['delay'] ) ? esc_attr( $block['attrs']['animationsSettings']['delay'] ) : '';
		if ( ! empty( $delay ) ) {
			$content->set_attribute( 'data-aos-delay', $delay );
		}

			$easing = ! empty( $block['attrs']['animationsSettings']['easing'] ) ? esc_attr( $block['attrs']['animationsSettings']['easing'] ) : '';
		if ( ! empty( $easing ) ) {
			$content->set_attribute( 'data-aos-easing', $easing );
		}

			$anchor = ! empty( $block['attrs']['animationsSettings']['anchorPlacement'] ) ? esc_attr( $block['attrs']['animationsSettings']['anchorPlacement'] ) : '';
		if ( ! empty( $anchor ) ) {
			$content->set_attribute( 'data-aos-anchor-placement', $anchor );
		}

			$offset = ! empty( $block['attrs']['animationsSettings']['offset'] ) ? esc_attr( $block['attrs']['animationsSettings']['offset'] ) : '';
		if ( ! empty( $offset ) ) {
			$content->set_attribute( 'data-aos-offset', $offset );
		}

			$once = ! empty( $block['attrs']['animationsSettings']['once'] ) ? esc_attr( $block['attrs']['animationsSettings']['once'] ) : '';
		if ( ! empty( $once ) ) {
			$content->set_attribute( 'data-aos-once', 'true' );
		}

			$mirror = ! empty( $block['attrs']['animationsSettings']['mirror'] ) ? esc_attr( $block['attrs']['animationsSettings']['mirror'] ) : '';
		if ( ! empty( $mirror ) ) {
			$content->set_attribute( 'data-aos-mirror', 'true' );
		}
		return $content->get_updated_html();
	}
}
AOS::init();
