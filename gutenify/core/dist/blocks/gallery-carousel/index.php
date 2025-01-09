<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;


class Gallery_Carousel {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		// add_filter( 'gutenify_render_block_gutenify/gallery-carousel', array( __CLASS__, 'gutenify_render_gallery_carousel' ), 10, 3 );
	}

	public static function register_block() {
		register_block_type( __DIR__ , array(
			'render_callback' => array( __CLASS__, 'render_callback' )
		));
	}

	public static function render_callback ( $attributes, $context, $props ) {
		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( implode( ' ', array(  'gutenify--gallery-carousel--' . $attributes['layout'], 'swiper' ) ) )
		) );

		$block_content = sprintf(
			'<div %s><div class="swiper-wrapper">',
			$wrapper_attributes,
		);

		$list_items_markup = '';

		if ( ! empty( $attributes['images'] ) ) {
			foreach ( $attributes['images'] as $image ) {
				$title              = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['title'] ) ? $attributes['imagesData'][ $image['id'] ]['title'] : '';
				$sub_title          = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['subTitle'] ) ? $attributes['imagesData'][ $image['id'] ]['subTitle'] : '';
				$description        = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['description'] ) ? $attributes['imagesData'][ $image['id'] ]['description'] : '';
				$button_text        = ! empty( $image['id'] ) && ! empty( $attributes['imagesData'][ $image['id'] ]['buttonText'] ) ? $attributes['imagesData'][ $image['id'] ]['buttonText'] : '';

				$list_items_markup .= '<div class="gutenify-gallery-carousel-item swiper-slide">';
				if (  ! empty( $image['url'] ) ) {
					$list_items_markup .= '
						<div class="gutenify-slider-image-wrapper">
							<div class="gutenify-slider-content-image featured-image">
								<img src="' . ( $image['url'] ) . '" />
							</div>
						</div>';
				}
				$list_items_markup .='
					<div class="gutenify-slider-content-wrapper">
					<div class="gutenify-slider-content-inner">';
				if ( ! empty( $title ) ) {
					$list_items_markup .= '<h2 class="gutenify-slider-title">' . $title . '</h2>';
				}
				if ( ! empty( $sub_title ) ) {
					$list_items_markup .= '<h2 class="gutenify-slider-sub-title">' . $sub_title . '</h2>';
				}

					$list_items_markup .= '<div class="gutenify-slider-content-inner-wrapper">';
				if ( ! empty( $description ) ) {
					$list_items_markup .= '<div class="gutenify-slider-content clear-fix">
								<p>' . $description . '</p>
							</div>';
				}
				// if ( ! empty( $button_text ) ) {
				// $list_items_markup .= '<p class="gutenify-slider-buttons clear-fix gutenify-button-link wp-block-button__link">
				// ' . $button_text . '
				// </p>';
				// }
					$list_items_markup .= '</div>';
					$list_items_markup .= '</div>
				</div></div>';
			}
		}

		$block_content .= $list_items_markup;
		$block_content .= '</div>';
		$has_navigation = ! empty( $attributes['hasNavigation'] ) && true === $attributes['hasNavigation'];
		$has_pagination = ! empty( $attributes['hasPagination'] ) && true === $attributes['hasPagination'];
		if ( $has_pagination ) {
			$block_content .= '<div class="swiper-pagination"></div>';
		}
		if ( $has_navigation ) {
			$block_content .= '<div class="navigation-wrap">
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>';
		}
		$block_content .= '</div>';

		return $block_content;

	}
 }

Gallery_Carousel::init();
