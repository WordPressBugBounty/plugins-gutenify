<?php
class Gutenify_block_inline_styles {

	private static $all_styles = '';

	public static function init() {
		add_action( 'wp', [ __CLASS__, 'add_styles' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'add_inline_styles' ], 201 );
	}

	public static function add_styles() {
		add_filter( 'render_block', function( $block_content, $block ) {
			if ( ! empty( $block['attrs']['gutenifyStyles'] ) ) {
				if ( ( isset( $block['blockName'] ) && 'gutenify/button' !== $block['blockName'] ) &&
				     ( 'gutenify/faqs' !== $block['blockName'] )&&
				     ( 'gutenify/grid' !== $block['blockName'] )&&
				     ( 'gutenify/icon' !== $block['blockName'] ) &&
				     ( 'gutenify/info-box' !== $block['blockName'] )&&
				     ( 'gutenify/post-carousel' !== $block['blockName'] )&&
				     ( 'gutenify/post-list' !== $block['blockName'] )&&
				     ( 'gutenify/section-title' !== $block['blockName'] )&&
				     ( 'gutenify/service' !== $block['blockName'] )&&
				     ( 'gutenify/star-rating' !== $block['blockName'] )&&
				     ( 'gutenify/team' !== $block['blockName'] )&&
				     ( 'gutenify/team-member' !== $block['blockName'] )&&
				     ( 'gutenify/testimonial' !== $block['blockName'] )&&
				     ( 'gutenify/testimonials' !== $block['blockName'] )&&
				     ( 'gutenify/wc-product-carousel' !== $block['blockName'] )&&
				     ( 'gutenify/wc-product-list' !== $block['blockName'] )&&
				     ( 'gutenify/post-ticker' !== $block['blockName'] )&&
				     ( 'gutenify/related-posts' !== $block['blockName'] )&&
				     ( 'gutenify/slider' !== $block['blockName'] )&&
				     ( 'gutenify/advance-slider' !== $block['blockName'] )&&
				     ( 'gutenify/advance-gallery' !== $block['blockName'])&&
				     ( 'gutenify/notice-bar' !== $block['blockName'])
					 ) {
					self::$all_styles .= $block['attrs']['gutenifyStyles'];
				}
			}
			return $block_content;
		}, 11, 2 );
	}

	public static function add_inline_styles() {
		global $wp_styles;
		$handle = 'gutenify-block-inline-handle';
		$deps = [ 'gutenify-frontend' ];
		wp_register_style( $handle, false, $deps );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, self::$all_styles );
	}
}

Gutenify_block_inline_styles::init();
