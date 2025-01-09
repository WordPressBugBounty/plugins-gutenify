<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Slider {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		// add_filter( 'gutenify_render_block_gutenify/slider', array( __CLASS__, 'render_block' ), 10, 3 );
	}
	public static function register_block() {
		register_block_type( __DIR__ , array(
			'render_callback' => array( __CLASS__, 'render_callback' )
		));
	}

	public static function render_callback( $attr, $block_content ){
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];

		$layout =  ! empty( $attr['blockAdvanceOptions']['layout'] ) ? esc_attr( $attr['blockAdvanceOptions']['layout'] ) : 'layout-1';

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => implode( ' ', array(
				$main_slug . '-section-' . $layout,
				'swiper',
			) )
		) );

		$has_navigation = ! empty( $attr['hasNavigation'] ) && true === $attr['hasNavigation'];
		$has_pagination = ! empty( $attr['hasPagination'] ) && true === $attr['hasPagination'];

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

	public static function render_callback1( $attributes, $content, $props ) {
		$constants = Helpers::plugin_constants();

		$class_name  = array();
		// $class       = array( 'swiper-container', 'gutenify-slider' );
		// $styles      = array();
		// $block_style = ! empty( $attributes['className'] ) && strpos( $attributes['className'], 'is-style-stacked' ) !== false ? 'stacked' : 'horizontal';


		$layout = ! empty( $attributes['blockAdvanceOptions']['layout'] ) ? $attributes['blockAdvanceOptions']['layout'] : 'layout-1';

		array_push( $class_name, 'gutenify-section-' . $layout, 'swiper' );




		// if ( isset( $attributes['columns'] ) ) {
		// 	array_push( $class, 'has-columns has-' . $attributes['columns'] . '-columns has-responsive-columns' );
		// }

		// if ( isset( $attributes['listPosition'] ) && 'right' === $attributes['listPosition'] && 'horizontal' === $block_style ) {
		// 	array_push( $class, 'has-image-right' );
		// }

		// if ( isset( $attributes['imageSize'] ) && 'horizontal' === $block_style ) {
		// 	array_push( $class, 'has-' . $attributes['imageSize'] . '-image' );
		// }

		// if ( isset( $attributes['imageStyle'] ) ) {
		// 	array_push( $class, 'has-' . $attributes['imageStyle'] . '-image' );
		// }

		$class_name = apply_filters( 'gutenify--slider--wrapper-class', $class_name, $props );

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( implode( ' ', $class_name ) )
		) );

		$block_content = sprintf( '<div %s> <div class="swiper-wrapper">', $wrapper_attributes );

		$list_items_markup = '';

		$list_items_markup = $content;

		$block_content .= $list_items_markup;
		$block_content .= '</div>';
		$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
		$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];
		if ( $has_navigation ) {
			$block_content .= '<div class="swiper-button-next"></div>
			<div class="swiper-button-prev"></div>';
		}
		if ( $has_pagination ) {
			$block_content .= '<div class="swiper-pagination"></div>';
		}
		$block_content .= '</div>';
		$block_content .= '</div>';


		return $block_content;
	}
}

Slider::init();

