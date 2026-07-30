<?php
/**
 * Plugin Name: Gutenify WooCommerce Product List
 * Plugin URI: https://gutenify.com
 * Description: WooCommerce Product List block.
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
 */
function gutenify_load_textdomain_block_wc_product_list() {
	load_plugin_textdomain( 'gutenify', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'gutenify_load_textdomain_block_wc_product_list' );

function gutenify_render_wc_product_list_block( $attributes ) {
	if ( ! class_exists( 'woocommerce' ) ) {
		return '';
	}
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
		$wrapper_class      = array(
			'gutenify-section-' . $attributes['blockClientId'],
			'wp-block-gutenify-wc-product-list',
			'gutenify--wc-product-list-' . $attributes['layout'],
			'gutenify--wc-product-list-col-' . $attributes['columns'],
			'gutenify-wc-product-list-version-2',
		);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => implode( ' ', $wrapper_class ),
			)
		);
		printf( '<div %s>', $wrapper_attributes );
		while ( $posts->have_posts() ) {
			$posts->the_post();
			gutenify_get_wc_product_item( get_the_ID() );
		}
		echo '</div>'; // Product lists wrapper
		wp_reset_postdata();
	}
	$result = ob_get_clean();
	return $result;
}


/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */
function gutenify_register_block_wc_product_list() {

	// Register the block by passing the location of block.json to register_block_type.
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'gutenify_render_wc_product_list_block',
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
		 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
		 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'gutenify-block-wc-product-list', 'gutenify' );
	}
}
add_action( 'init', 'gutenify_register_block_wc_product_list' );


function gutenify_wc_product_list_render_block($block_content, $block){
	if ((isset($block['blockName']) && 'gutenify/wc-product-list' !== $block['blockName'])|| is_admin()) {
		return $block_content;
	}

	$block_id = wp_unique_id('gtfy-');
	$block_client_id = !empty($block['attrs']['blockClientId']) ? 'gutenify-section-' . esc_attr($block['attrs']['blockClientId']) : $block_id;

	$block_content = new WP_HTML_Tag_Processor($block_content);
	$block_content->next_tag();
	$block_content->add_class($block_client_id);

	//selector
	$root_selector = '.' . $block_client_id ;
	$inner_block_selector = $root_selector . ' .gutenify--wc-product--item';
	$color='';
	$css =$inner_block_selector . '{' ;

	if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'])){
		$color .= esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'] ) ;
	}
	if($color){
		$css .= 'color:' . $color . ';';
	}

	//background
	if(!empty($block['attrs']['backgroundGradient'])){
		$css .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
		}elseif(!empty($block['attrs']['backgroundColor'])){
		$css .= 'background:' . esc_attr( $block['attrs']['backgroundColor'] ). ';';
	}elseif(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'])){
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'] ) . ';';
	}elseif(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'])){
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) . ';';
	}

	//border color
	if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'])){
		$css .= 'border-color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'] ) . ';';
	}

	//border width
	if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'] )  ) {
		$css .= 'border-style:solid;';
		$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'], 'border-', '-width');
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] ) ) {
		$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] );
	}
	$css .= '}';

	//hoverstyle
	$css .=$inner_block_selector . ':hover{' ;
	//hover color
	$hover_color='';
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'])) {
		$hover_color .= esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'] );
	}
	if($hover_color){
		$css .= 'color:' . $hover_color . ';';
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
	}

	//hover border
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'])) {
		$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'] ) . ';';
	}
	$css .= '}';

	if($color){
		$css .= $inner_block_selector . ' .gutenify--wc-product--title>a {color: ' . $color . ';}';
	}
	if($hover_color){
		$css .= $inner_block_selector . ':hover .gutenify--wc-product--title>a {color: ' . $hover_color . ';}';
	}


	$handle = 'gutenify-block-inline-handle';
	wp_add_inline_style('wp-block-library', $css);
	return $block_content->get_updated_html();
}
add_action('render_block', 'gutenify_wc_product_list_render_block', 200, 2);
