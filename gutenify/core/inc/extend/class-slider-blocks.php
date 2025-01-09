<?php
namespace gutenify;
defined( 'ABSPATH' ) || exit;

class Extend_Slider_Blocks{
	public static function init() {
		add_filter( 'gutenify_render_block', array( __CLASS__, 'render_block_slider' ), 10, 4 );
	}

	public static function render_block_slider( $block_content, $block, $instance, $block_id ) {
		if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], array( 'gutenify/advance-slider', 'gutenify/post-carousel', 'gutenify/wc-product-carousel', 'gutenify/gallery-carousel', 'gutenify/slider', 'gutenify/slider-v2' ) ) ) {
			return $block_content;
		}

		$constants = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

		$handle = $plugin_main_slug . '-swiper';
		// wp_enqueue_style( $handle );
		// wp_enqueue_script( $handle );

        	$slider_var = 'slider_' . str_replace('-', '_', $block_id);


		$has_navigation = ! ( isset( $block['attrs']['blockAdvanceOptions']['hasNavigation']) && false === $block['attrs']['blockAdvanceOptions']['hasNavigation'] );
		$has_pagination = ! ( isset( $block['attrs']['blockAdvanceOptions']['hasPagination']) && false === $block['attrs']['blockAdvanceOptions']['hasPagination'] );
		$columns = !empty( $instance->attributes['blockAdvanceOptions']['columns'] ) ? absint( $instance->attributes['blockAdvanceOptions']['columns'] ) : 1;
		$columns = !empty($block['attrs']['blockAdvanceOptions']['columns']) ? absint( $block['attrs']['blockAdvanceOptions']['columns'] ) : $columns;

		$loop = !empty( $instance->attributes['loop'] );
		$loop = !empty($block['attrs']['blockAdvanceOptions']['loop']) ?  ( $block['attrs']['blockAdvanceOptions']['loop'] ) : $loop;


		$settings = array();

		// For Posts
		if ( in_array( $block['blockName'], array(  'gutenify/post-carousel', 'gutenify/wc-product-carousel', 'gutenify/gallery-carousel', 'gutenify/slider', 'gutenify/slider-v2' ) ) ) {
			$has_navigation = ! ( isset( $block['attrs']['hasNavigation']) && false === $block['attrs']['hasNavigation'] );
			$has_pagination = ! ( isset( $block['attrs']['hasPagination']) && false === $block['attrs']['hasPagination'] );

			$columns = !empty( $instance->attributes['columns'] ) ? absint( $instance->attributes['columns'] ) :1;
			$columns = !empty($block['attrs']['columns']) ? absint( $block['attrs']['columns'] ) : $columns;

			$loop = !empty( $instance->attributes['loop'] ) ;
			$loop = !empty($block['attrs']['loop']) ?  ( $block['attrs']['loop'] ) : $loop;

			$spaceBetween = !empty($block['attrs']['spaceBetween']) ? absint( $block['attrs']['spaceBetween'] ) : $instance->attributes['spaceBetween'];

			$settings['spaceBetween'] = $spaceBetween;
			$loop = true;
		}

		$settings['loop'] = $loop;
		$settings['slidesPerView'] = $columns;

		if ( true === $has_navigation ) {
			$settings['navigation'] = array(
				'nextEl' => '.'. $block_id .' .swiper-button-next',
				'prevEl' => '.'. $block_id .' .swiper-button-prev',
			);
		}

		if ( true === $has_pagination ) {
			$settings['pagination'] = array(
				'el' => '.'. $block_id .' .swiper-pagination',
				'clickable' => true,
			);
		}

		$settings = apply_filters( 'gutenify/slider/settings', $settings, $block_id, $block, $instance );

		// error_log( print_r( $settings, true ));
		wp_add_inline_script( $handle, '
			document.addEventListener("DOMContentLoaded", function() {
			var swiperSelector =  document.querySelector( ".'.$block_id.'" );
					if ( swiperSelector ) {
			var swiper = gutenifyAdvancedSliderInit(  ".'.$block_id.'", ' . json_encode( $settings ).' );
				}
           });
        ');

		$block_content = new \WP_HTML_Tag_Processor($block_content);
		$block_content->next_tag();
		$block_content->add_class('swiper');

		return $block_content->get_updated_html();

	}
}

Extend_Slider_Blocks::init();
