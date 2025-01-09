<?php
namespace gutenify;
defined( 'ABSPATH' ) || exit;

class Slider_v2{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));

	}

	public static function register_block() {
		register_block_type(__DIR__,array(
			'render_callback' => array( __CLASS__, 'render_callback' ),
		));
	}

	public static function render_callback( $attr, $block_content, $block ){
		// echo "<pre>";
		$block_content = new \WP_HTML_Tag_Processor($block_content);
		if( ! empty( $block->parsed_block['innerBlocks'] ) ) {
			$main_swiper_el = '';
			$main_swiper_wrapper_el = '';
			$main_swiper_slide_el = '';
			if ( 'core/query' === $block->parsed_block['innerBlocks'][0]['blockName'] ) {
				$main_swiper_el = 'wp-block-query';
				$main_swiper_wrapper_el = 'wp-block-post-template';
				$main_swiper_slide_el = 'wp-block-post';
			} else if ( 'woocommerce/product-collection' === $block->parsed_block['innerBlocks'][0]['blockName'] ) {
				$main_swiper_el = 'wp-block-woocommerce-product-collection';
				$main_swiper_wrapper_el = 'wc-block-product-template';
				$main_swiper_slide_el = 'wc-block-product';
			}

			// error_log( print_r( $block->parsed_block['innerBlocks'][0]['blockName'], true ) );
			// error_log( $main_swiper_el );
			$block_content->next_tag(array( 'class_name' => $main_swiper_el ));
			$block_content->add_class('swiper');

			$block_content->next_tag(array( 'class_name' => $main_swiper_wrapper_el ));
			$block_content->add_class('swiper-wrapper');
			// error_log( print_r( $block_content->next_tag(array( 'class_name' => 'wp-block-post' )), true ));
			while( $block_content->next_tag(array( 'class_name' => $main_swiper_slide_el )) ) {

				$block_content->add_class('swiper-slide');
			}
		}
		// echo "</pre>";
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => implode( ' ', array(
				// 'swiper'
			) )
		) );
		$has_navigation = ! ( empty($attr['hasNavigation']) || false === $attr['hasNavigation'] );
		$has_pagination = ! ( empty($attr['hasPagination']) || false === $attr['hasPagination'] );

		$new_content = sprintf( '<div %s>', $wrapper_attributes );
		// $new_content .= '<div class="swiper-wrapper">';
		$new_content .= $block_content->get_updated_html();
		// $new_content .= '</div>';
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
Slider_v2::init();

add_filter( 'post_class', function( $class ){
	$class[] = 'swiper-slide';
	return $class;
} );
