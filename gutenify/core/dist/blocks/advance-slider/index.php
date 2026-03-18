<?php
namespace gutenify;
defined( 'ABSPATH' ) || exit;

class Advanced_Slider{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));
		add_filter('gutenify_render_block_gutenify/advance-slider', array(__CLASS__, 'render_block'), 10, 4);
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

	public static function render_block( $block_content, $block, $instance, $block_id ) {
		$css = '';
		$root_selector = '.' . $block_id;

		// Handle Standard Shadow support
		if ( ! empty( $block['attrs']['style']['shadow'] ) ) {
			$shadow = $block['attrs']['style']['shadow'];
			if ( strpos( $shadow, 'var:preset|shadow|' ) === 0 ) {
				$slug = str_replace( 'var:preset|shadow|', '', $shadow );
				$css .= "box-shadow: var(--wp--preset--shadow--$slug);";
			} else {
				$css .= "box-shadow: $shadow;";
			}
		}

		// Handle Standard Border support
		if ( ! empty( $block['attrs']['style']['border'] ) ) {
			$border = $block['attrs']['style']['border'];
			if ( ! empty( $border['width'] ) ) {
				$css .= 'border-width: ' . $border['width'] . '; border-style: solid;';
			}
			if ( ! empty( $border['color'] ) ) {
				$color = $border['color'];
				if ( strpos( $color, 'var:preset|color|' ) === 0 ) {
					$slug = str_replace( 'var:preset|color|', '', $color );
					$css .= "border-color: var(--wp--preset--color--$slug);";
				} else {
					$css .= "border-color: $color;";
				}
			}
			if ( ! empty( $border['radius'] ) ) {
				$css .= \gutenify\Style_Helpers::border_radius_control( $border['radius'] );
			}
		}

		// Handle Standard Spacing support
		if ( ! empty( $block['attrs']['style']['spacing'] ) ) {
			$spacing = $block['attrs']['style']['spacing'];
			if ( ! empty( $spacing['margin'] ) ) {
				$css .= \gutenify\Style_Helpers::box_control( $spacing['margin'], 'margin-' );
			}
			if ( ! empty( $spacing['padding'] ) ) {
				$css .= \gutenify\Style_Helpers::box_control( $spacing['padding'], 'padding-' );
			}
		}

		if ( ! empty( $css ) ) {
			$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
			wp_add_inline_style( $handle, "$root_selector { $css }" );
		}

		return $block_content;
	}
}
// Initialize the Advanced_Slider class
Advanced_Slider::init();
