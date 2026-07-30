<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class WC_Product_Carousel {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );

		add_filter( 'gutenify_render_block_gutenify/wc-product-carousel', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	public static function render_callback( $attr, $content, $props ) {
		if ( ! class_exists( 'woocommerce' ) ) {
			return '';
		}
		$layout =  ! empty( $attr['layout'] ) ? esc_attr( $attr['layout'] ) : 1;
		$columns =  ! empty( $attr['columns'] ) ? esc_attr( $attr['columns'] ) : 1;
		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => implode( ' ', array(
				'gutenify--wc-product-carousel-' . $layout,
				'gutenify--wc-product-carousel-col-' . $columns,
			) )
		) );

		$has_navigation = ! ( isset( $attr['hasNavigation']) && false === $attr['hasNavigation'] );
		$has_pagination = ! ( isset( $attr['hasPagination']) && false === $attr['hasPagination'] );

		$new_content = sprintf( '<div %s>', $wrapper_attributes );
		$new_content .= '<div class="swiper-wrapper">';
		$new_content .= self::display_products(   $attr );
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

	public static function display_products( $attributes ) {
		$constants = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];



		$query = $attributes['query'];
		$args  = array(
			'post_status' => 'publish',
			'post_type'   => 'product',
		);

		if ( ! empty( $query['numberOfItems'] ) ) {
			$args['posts_per_page'] = $query['numberOfItems'];
		}
		if ( ! empty( $query['orderBy'] ) ) {
			$args['orderby'] = $query['orderBy'];
		}
		if ( ! empty( $query['order'] ) ) {
			$args['order'] = strtoupper( $query['order'] );
		}

		if ( ! empty( $query['tax']['product_cat'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'id',
				'terms'    => $query['tax']['product_cat'],
			);
		}
		if ( ! empty( $query['tax']['product_tag'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'id',
				'terms'    => $query['tax']['product_tag'],
			);
		}
		$posts = new \WP_Query( $args );

		ob_start();
		if ( $posts->have_posts() ) {

			while ( $posts->have_posts() ) {
				$posts->the_post();
				$product   = wc_get_product( get_the_ID() );
				$permalink = get_the_permalink( $product->get_id() );
				echo '<div class="gutenify--wc-product--item swiper-slide">';
				echo '<div class="gutenify--wc-product--item-wrapper">';

				echo '<div class="gutenify--wc-product--thumb">';
				echo '<a class="image-hover-zoom" href="' . $permalink . '" tabindex="-1">';
				echo woocommerce_get_product_thumbnail();
				echo '</a>'; // Product thumb link
				echo $product->is_on_sale() ? '<div class="gutenify--wc-product--onsale"><span aria-hidden="true">Sale</span><span class="screen-reader-text">Product on sale</span></div>' : '';
				echo '</div>'; // Product thumb


				echo '<div class="gutenify--wc-product--item-content">';
				echo '<h3 class="gutenify--wc-product--title">';
				echo '<a rel="bookmark" href="' . $permalink . '" tabindex="-1">';
				echo $product->get_name();
				echo '</a>'; // Product title link
				echo '</h3>'; // Product title

				echo '<div class="gutenify--wc-product--price">';
				echo $product->get_price_html();
				echo '</div>'; // Product price

				echo '<div class="wp-block-button wc-block-grid__product-add-to-cart">';
				echo gutenify_wc_get_add_to_cart( $product );

				echo '</div>'; // Product add to cart wrapper
				echo '</div>'; // .gutenify--wc-product--item-content

				echo '</div>'; // Product individual wrapper
				echo '</div>'; // Product individual wrapper
			}

			wp_reset_postdata();
		}

		$result = ob_get_clean();
		return $result;
	}

	public static function render_block ($block_content, $block, $instance, $block_id){

		$root_selector = '.' . $block_id ;
		$inner_block_selector = $root_selector . ' .gutenify--wc-product--item';

		$css =$inner_block_selector . '{' ;


		//color
		if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'])){

			$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'] ) . ';';
		}


		//background
		if(!empty($block['attrs']['backgroundGradient'])){
			$css .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'])){
			$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'] ) . ';';
		}
		if(!empty($block['attrs']['backgroundColor'])){
			$css .= 'background-color:' . esc_attr( $block['attrs']['backgroundColor'] ). ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'])){
			$css .= 'background-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) . ';';
		}

		//border color
		if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'])){
			$css .= 'border-color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'] ) . ';';
		}

		//border width
		if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'])){
			if(is_numeric($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'])){
					$css .= 'border-width:' .esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'] ) . 'px; border-style: solid;';
			}
			if(is_string($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'])){
				$css .= 'border-width:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'] ) . ';';
			}
			if(is_array($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'])){
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['top'])){
					$css .='border-top-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['top'] ). '; border-top-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['bottom'])){
					$css .='border-bottom-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['bottom'] ). '; border-bottom-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['left'])){
					$css .='border-left-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['left'] ). '; border-left-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['right'])){
					$css .='border-right-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth']['right'] ). '; border-right-style: solid;';
				}
			}
		}
		//border radius
		if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'])){
			if(is_string($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'])){
				$css .= ' border-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] ) . ';';
			}
			if(is_numeric($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'])){
				$css .= 'border-radius:'. esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] ) . 'px;';
			}
			if(is_object($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'])){
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['topleft'])){
					$css .= 'border-top-left-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['topLeft'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['bottomLeft'])){
					$css .= 'border-bottom-left-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['bottomLeft'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['topRight'])){
					$css .= 'border-top-right-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['topRight'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['bottomRight'])){
					$css .= 'border-bottom-right-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius']['bottomRight'] ) .';';
				}
			}
		}
		$css .= '}';

		//hoverstyle
		$css .=$inner_block_selector . ':hover{' ;
		//hover color
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'])) {
			$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'] ). ';';
		}


		//hover background
		if (!empty($block['attrs']['hoverBackgroundGradient'])) {
			$css .= 'background: ' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
			} elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'])) {
			$css .= 'background: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'] ) . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'] ) . ';';
		} else {
			$css .= '';
		}

		//hover border
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'])) {
			$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'] ) . ';';
		}
		$css .= '}';

		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css );

		return $block_content;
	}
}

WC_Product_Carousel::init();

function gutenify_render_wc_product_carousel_block( $attributes, $content, $props ) {
	if ( ! class_exists( 'woocommerce' ) ) {
		return '';
	}

	$constants = \gutenify\Helpers::plugin_constants();
	$plugin_main_slug = $constants['plugin_main_slug'];
	$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];



	$query = $attributes['query'];
	$args  = array(
		'post_status' => 'publish',
		'post_type'   => 'product',
	);

	if ( ! empty( $query['numberOfItems'] ) ) {
		$args['posts_per_page'] = $query['numberOfItems'];
	}
	if ( ! empty( $query['orderBy'] ) ) {
		$args['orderby'] = $query['orderBy'];
	}
	if ( ! empty( $query['order'] ) ) {
		$args['order'] = strtoupper( $query['order'] );
	}

	if ( ! empty( $query['tax']['product_cat'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'id',
			'terms'    => $query['tax']['product_cat'],
		);
	}
	if ( ! empty( $query['tax']['product_tag'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'id',
			'terms'    => $query['tax']['product_tag'],
		);
	}
	$posts = new WP_Query( $args );

	ob_start();
	if ( $posts->have_posts() ) {

		while ( $posts->have_posts() ) {
			$posts->the_post();
			$product   = wc_get_product( get_the_ID() );
			$permalink = get_the_permalink( $product->get_id() );
			echo '<div class="gutenify--wc-product--item swiper-slide">';
			echo '<div class="gutenify--wc-product--item-wrapper">';

			echo '<div class="gutenify--wc-product--thumb">';
			echo '<a class="image-hover-zoom" href="' . $permalink . '" tabindex="-1">';
			echo woocommerce_get_product_thumbnail();
			echo '</a>'; // Product thumb link
			echo $product->is_on_sale() ? '<div class="gutenify--wc-product--onsale"><span aria-hidden="true">Sale</span><span class="screen-reader-text">Product on sale</span></div>' : '';
			echo '</div>'; // Product thumb


			echo '<div class="gutenify--wc-product--item-content">';
			echo '<h3 class="gutenify--wc-product--title">';
			echo '<a rel="bookmark" href="' . $permalink . '" tabindex="-1">';
			echo $product->get_name();
			echo '</a>'; // Product title link
			echo '</h3>'; // Product title

			echo '<div class="gutenify--wc-product--price">';
			echo $product->get_price_html();
			echo '</div>'; // Product price

			echo '<div class="wp-block-button wc-block-grid__product-add-to-cart">';
			echo gutenify_wc_get_add_to_cart( $product );

			echo '</div>'; // Product add to cart wrapper
			echo '</div>'; // .gutenify--wc-product--item-content

			echo '</div>'; // Product individual wrapper
			echo '</div>'; // Product individual wrapper
		}

		wp_reset_postdata();
	}

	$result = ob_get_clean();
	return $result;
}




// add_action('render_block', 'gutenify_wc_product_carousel_render_block', 200, 2);
