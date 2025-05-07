<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Notice_Bar{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));
		add_action('init',array( __CLASS__, 'register_patterns'));
		add_filter('gutenify_render_block_gutenify/notice-bar', array(__CLASS__, 'render_block'), 10, 4);
	}

	public static function register_block() {
		register_block_type(__DIR__, array(
			'render_callback' => array(__CLASS__, 'render_cb')
		));
	}

	public static function get_position_styles($closeBtnAlignment) {
		switch ($closeBtnAlignment) {
			case 'top left':
				return 'top: 0px; left: 0px;';
			case 'top center':
				return 'top: 0px; left: 50%; transform: translate(-50%, 5%);';
			case 'top right':
				return 'top: 0px; right: 0px;';
			case 'bottom left':
				return 'bottom: 0px; left: 0px;';
			case 'bottom center':
				return 'bottom: 0px; left: 50%; transform: translate(-40%, 25%);';
			case 'bottom right':
				return 'bottom: 0px; right: 0px;';
			case 'center center':
				return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
			case 'center left':
				return 'top: 50%; left: 10px; transform: translate(-50%, -50%);';
			case 'center right':
				return 'top: 50%; right: -10px; transform: translate(-50%, -50%);';
			default:
				return 'top: 0px; right: 0px;';
		}
	}

	public static function render_cb($block, $content)  {
		$wrapper_attributes = get_block_wrapper_attributes();
		$close_alignment = self::get_position_styles(esc_attr($block['closeBtnAlignment']));
		$output ='';
		$cookie_status =!empty($block['enableCookie'])? 'true': 'false';
		$cookie_time = !empty($block['cookieTime']) ? esc_attr($block['cookieTime']) : '0';
		$output .= '<div ' . $wrapper_attributes. ' data-enable-cookie='. esc_attr($cookie_status).'
		data-cookie-time='. esc_attr($cookie_time).'>';
		$output .= $content;

		if (!empty($block['showCloseBtn']) && $block['showCloseBtn'] === true) {

		$close_icon = !empty($block['closeIcon']) ? $block['closeIcon'] : '<svg class="gutenify-notice-bar-close" fill="#8e2828" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path></svg>';
		$output .= '<div class="gutenify-notice-bar-close" style = "position: absolute; ' . $close_alignment . '">';
		$output .='<span>'.$close_icon.'</span>';
		$output .='</div>';
		}
		$output .= '</div>';
		return $output;
	}

	public static function render_block($block_content, $block, $instance, $block_id) {

		 $cookie_id = 'notice-bar-' . esc_attr($block_id);
		if( ! empty( $block['attrs']['enableCookie'] ) && ! empty( $_COOKIE[ $cookie_id ] ) && 'true' === $_COOKIE[ $cookie_id ] ) {
			return '';
		}
		$root_selector = '.' . $block_id;
		$css ='';
		$wrapper_background = !empty($instance->attributes['style']['color']['background'])? $instance->attributes['style']['color']['background']: '';

		if(!empty($wrapper_background)){
			$css .= $root_selector.'{ background-color:' .$wrapper_background.';}';
		}

		$color =  !empty($block['attrs']['closeColor'])
       				? esc_attr($block['attrs']['closeColor'])
        			: '';


		// $background = !empty( $instance->attributes['closeBackground'] ) ?  esc_attr( $instance->attributes['closeBackground']) : '';
		$background = !empty( $block['attrs']['closeBackground']) ? esc_attr($block['attrs']['closeBackground']):'';
		$border_color = !empty( $block['attrs']['closeBorderColor']) ? esc_attr($block['attrs']['closeBorderColor']):'';
		// $border_color = !empty( $instance->attributes['closeBorderColor'] ) ?  esc_attr( $instance->attributes['closeBorderColor']) : '';
		$border_width = !empty( $instance->attributes['closeBorderWidth'] ) ?  esc_attr( $instance->attributes['closeBorderWidth']) : '0px';
		$border_radius = !empty( $instance->attributes['closeBorderRadius'] ) ?  esc_attr( $instance->attributes['closeBorderRadius']) : '14px';
		$size = !empty( $instance->attributes['closeSize'] ) ?  esc_attr( $instance->attributes['closeSize']) : '14px';
		$margin_top = !empty( $instance->attributes['margin']['top'] ) ?  esc_attr( $instance->attributes['margin']['top']) : '8px';
		$margin_bottom = !empty( $instance->attributes['margin']['bottom'] ) ?  esc_attr( $instance->attributes['margin']['bottom']) : '8px';
		$margin_left = !empty( $instance->attributes['margin']['left'] ) ?  esc_attr( $instance->attributes['margin']['left']) : '8px';
		$margin_right = !empty( $instance->attributes['margin']['right'] ) ?  esc_attr( $instance->attributes['margin']['right']) : '8px';


		$css .= $root_selector .' .gutenify-notice-bar-close svg{';
		if(!empty($color)){
			$css .='fill:' .$color.';';
		}
		if(!empty($size)){
			$css .='width:' .$size.';';
			$css .='height:' .$size.';';
		}
		if(!empty($background)){
			$css .='background:' .$background.';';
		}
		if(!empty($border_color)){
			$css .='border-color:' .$border_color.';';
		}
		if(!empty($border_width)){
			$css .='border-width:' .$border_width.';';
			$css .='border-style:solid;';
		}
		if(!empty($border_radius)){
			$css .='border-radius:' .$border_radius.';';
		}
		if(!empty($margin_top)){
			$css .='margin-top:' .$margin_top.';';
		}
		if(!empty($margin_bottom)){
			$css .='margin-bottom:' .$margin_bottom.';';
		}
		if(!empty($margin_left)){
			$css .='margin-left:' .$margin_left.';';
		}
		if(!empty($margin_right)){
			$css .='margin-right:' .$margin_right.';';
		}

		 $css .='}';


		$handle = 'gutenify_' . str_replace('/', '_', $block['blockName']) . '_' . $block_id;
		wp_add_inline_style($handle, $css);
		return $block_content;
	}



	public static function register_patterns() {
		register_block_pattern(
			'gutenify/notice-bar-1',
			array(
				'title'       => __( 'Two buttons', 'wpdocs-my-plugin' ),
				'slug'       => 'gutenify/notice-bar-1',
				'blockTypes' => array( 'gutenify/notice-bar' ),
				'description' => _x( 'Two horizontal buttons, the left button is filled in, and the right button is outlined.', 'Block pattern description', 'wpdocs-my-plugin' ),
				'content'     => '<!-- wp:gutenify/notice-bar {"statusType":"success","closeIcon":"\u003csvg width=\u002224\u0022 height=\u002224\u0022 viewBox=\u00220 0 24 24\u0022 fill=\u0022none\u0022 xmlns=\u0022http://www.w3.org/2000/svg\u0022\u003e\u003cpath d=\u0022M5.25 12C5.25 11.5858 5.58579 11.25 6 11.25H18.0007C18.4149 11.25 18.7507 11.5858 18.7507 12C18.7507 12.4142 18.4149 12.75 18.0007 12.75H6C5.58579 12.75 5.25 12.4142 5.25 12Z\u0022 fill=\u0022#323544\u0022/\u003e\u003c/svg\u003e","closeColor":"#e62929","closeBackground":"#ffe2d0","closeBorderWidth":"1px","style":{"border":{"radius":"16px","width":"0px","style":"none"},"elements":{"button":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"text":"#fffefe","background":"#12B76A"},"text":"#fffefe","background":"#F79009"},"text":"#fffefe","background":"#667085"},"text":"#fffefe","background":"#F04438"},"text":"#fffefe","background":"#2E90FA"},"text":"var:preset|color|accent-2","background":"var:preset|color|contrast"},"text":"#fffefe","background":"#12B76A"},"text":"#fffefe","background":"#2E90FA"},"text":"#fffefe","background":"#F79009"},"text":"#fffefe","background":"#2E90FA"},"text":"#fffefe","background":"#12B76A"}}},"color":{"background":"#ECFDF3"}},"cookieTime":"20","backgroundColor":""} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"20px","right":"20px"}},"color":{},"border":{}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:gutenify/icon {"size":24} -->
<div class="wp-block-gutenify-icon flex-col-item is-content-justification-center"><div class="gutenify-icon-wrapper"><i class="fas fa-star"></i></div></div>
<!-- /wp:gutenify/icon -->

<!-- wp:paragraph -->
<p>Thanks for using Gutenify!</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:gutenify/buttons -->
<div class="wp-block-gutenify-buttons wp-block-buttons"><!-- wp:gutenify/button {"text":"Buy Now"} -->
<div class="wp-block-gutenify-button"><a class="gutenify-button-link wp-block-button__link"><span>Buy Now</span></a></div>
<!-- /wp:gutenify/button --></div>
<!-- /wp:gutenify/buttons --></div>
<!-- /wp:group -->
<!-- /wp:gutenify/notice-bar -->',
			)
			);

			register_block_pattern(
			'gutenify/notice-bar-2',
			array(
				'title'       => __( 'Two buttons 2', 'wpdocs-my-plugin' ),
				'slug'       => 'gutenify/notice-bar-2',
				'blockTypes' => array( 'gutenify/notice-bar' ),
				'description' => _x( 'Two horizontal buttons, the left button is filled in, and the right button is outlined.', 'Block pattern description', 'wpdocs-my-plugin' ),
				'content'     => '<!-- wp:gutenify/notice-bar {"statusType":"welcome","showCloseBtn":false,"closeIcon":"\u003csvg width=\u002224\u0022 height=\u002224\u0022 viewBox=\u00220 0 24 24\u0022 fill=\u0022none\u0022 xmlns=\u0022http://www.w3.org/2000/svg\u0022\u003e\u003cpath d=\u0022M5.25 12C5.25 11.5858 5.58579 11.25 6 11.25H18.0007C18.4149 11.25 18.7507 11.5858 18.7507 12C18.7507 12.4142 18.4149 12.75 18.0007 12.75H6C5.58579 12.75 5.25 12.4142 5.25 12Z\u0022 fill=\u0022#323544\u0022/\u003e\u003c/svg\u003e","closeColor":"#A60000","closeBackground":"#FFE2D0","closeBorderWidth":"1px","margin":{"top":"0px","right":"0px","left":"0px","bottom":"0px"},"style":{"border":{"radius":"0px","width":"0px","style":"none","color":"#667085"},"elements":{"button":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"color":{"text":"#FFFEFE","background":"#12B76A"},"text":"#FFFEFE","background":"#F79009"},"text":"#FFFEFE","background":"#667085"},"text":"#FFFEFE","background":"#F04438"},"text":"#FFFEFE","background":"#2E90FA"},"text":"var:preset|color|accent-2","background":"var:preset|color|contrast"},"text":"#FFFEFE","background":"#12B76A"},"text":"#FFFEFE","background":"#2E90FA"},"text":"#FFFEFE","background":"#F79009"},"text":"#FFFEFE","background":"#2E90FA"},"text":"#FFFEFE","background":"#12B76A"},"text":"#FFFEFE","background":"#2E90FA"},"text":"#FFFEFE","background":"#667085"}}},"color":{"background":"#F9FAFB"}},"cookieTime":"20","align":"full","backgroundColor":""} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"20px","right":"20px"}},"color":{},"border":{}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:gutenify/icon {"iconName":"fas fa-rss","size":24} -->
<div class="wp-block-gutenify-icon flex-col-item is-content-justification-center"><div class="gutenify-icon-wrapper"><i class="fas fa-rss"></i></div></div>
<!-- /wp:gutenify/icon -->
<!-- wp:paragraph -->
<p>Welcome to the wordpress world.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:gutenify/buttons -->
<div class="wp-block-gutenify-buttons wp-block-buttons"><!-- wp:gutenify/button {"text":"Buy Now"} -->
<div class="wp-block-gutenify-button"><a class="gutenify-button-link wp-block-button__link"><span>Buy Now</span></a></div>
<!-- /wp:gutenify/button --></div>
<!-- /wp:gutenify/buttons --></div>
<!-- /wp:group -->
<!-- /wp:gutenify/notice-bar -->',
			)
			);
	}
}

Notice_Bar::init();


