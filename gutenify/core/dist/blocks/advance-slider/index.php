<?php
namespace gutenify;
defined( 'ABSPATH' ) || exit;

class Advanced_Slider{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));

	}

	public static function register_block() {
		register_block_type(__DIR__,array(
			'render_callback' => array( __CLASS__, 'render_callback' ),
		));
	}

	public static function render_callback( $attr, $block_content ){
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];

		$layout =  ! empty( $attr['blockAdvanceOptions']['layout'] ) ? esc_attr( $attr['blockAdvanceOptions']['layout'] ) : 'layout-1';

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => implode( ' ', array(
				$main_slug . '-section-' . $layout,
			) )
		) );
		$has_navigation = ! ( empty($attr['blockAdvanceOptions']['hasNavigation']) || false === $attr['blockAdvanceOptions']['hasNavigation'] );
		$has_pagination = ! ( empty($attr['blockAdvanceOptions']['hasPagination']) || false === $attr['blockAdvanceOptions']['hasPagination'] );

		$new_content = sprintf( '<div %s>', $wrapper_attributes );
		$new_content .= '<div class="swiper-wrapper">';
		$new_content .= $block_content;
		$new_content .= '</div>';
		if ( $has_navigation ) {
			$new_content .= '<div class="navigation-wrap">
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>';
		}

		if ( $has_pagination ) {
			$new_content .= '<div class="swiper-pagination"></div>';
		}
		$new_content .= '</div>';
		return $new_content;
	}
}
// Initialize the Advance_Slider class
Advanced_Slider::init();
