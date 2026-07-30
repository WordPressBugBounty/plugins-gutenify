<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Icon{
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/icon', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {
		$root_selector = '.' . $block_id . '.wp-block-gutenify-icon' ;
		$css='';
		$root_selector .= ' .gutenify-icon-wrapper';


	$css .= $root_selector . '{';
	//icon color
	if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
		$css .= 'color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['textColor'] ) . ';';
	}

	//background color
	if (!empty($block['attrs']['backgroundGradient'])) {
		$css .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
	}elseif (!empty($block['attrs']['backgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['backgroundColor'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundGradient'])) {
		$css .= 'background: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgorundGradient'] ) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundColor'] ) . ';';
	} else {
		$css .= '';
	}


	//border color
	if (!empty($block['attrs']['blockAdvanceOptions']['borderColor'])) {
		$css .= 'border-color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderColor'] ) . ';';
	}

	if ( ! empty( $block['attrs']['borderWidth'] )  ) {
		$css .= 'border-style:solid;';
		if ( ! is_array( $block['attrs']['borderWidth'] ) ) {
			$css .= 'border-width: ' . esc_attr( $block['attrs']['borderWidth'] ) . 'px;';
		} else {
			$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['borderWidth'], 'border-', '-width');
		}
	}

	if ( ! empty( $block['attrs']['borderRadius'] ) ) {
		$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['borderRadius'] );
	}

	//icon padding
	$paddingSides = ['top', 'bottom', 'left', 'right'];
	// Loop through each padding side
	foreach ($paddingSides as $side) {
		$paddingValue = esc_attr( $block['attrs']['blockAdvanceOptions']['iconPadding']['desktop'][$side] ?? '' );
		if (!empty($paddingValue)) {
			$css .= "padding-$side: $paddingValue;";
		}
	}

	//container size
	if (!empty($block['attrs']['blockAdvanceOptions']['containerWidth'])) {
		$css .= 'width: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['containerWidth'] ) . 'px;';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['containerHeight'])) {
		$css .= 'height: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['containerHeight'] ) . 'px;';
	}
	;
	$css .= '}';

	$css .= $root_selector . ' i{';

	//icon size
	if (!empty($block['attrs']['size'])) {
		$css .= 'font-size: ' . esc_attr( $block['attrs']['size'] ) . 'px;';
	}else{
		$css .='font-size: 40px;';
	}
	$css .= '}';

	//image size
	if (!empty($block['attrs']['size'])) {
		$css .= $root_selector . ' img{';
		$css .= 'width: ' . esc_attr( $block['attrs']['size'] ) . 'px;';
		$css .= 'max-width: ' . esc_attr( $block['attrs']['size'] ) . 'px;';
		$css .= '}';
	}

	//border radius
	if (!empty($block['attrs']['iconimgBorderRadius'])) {
		$css .= $root_selector . '.gutenify-image-icon-content img{';
		$css .= 'border-radius: ' . esc_attr( $block['attrs']['iconimgBorderRadius'] ) . 'px;';
		$css .= '}';
	}

	//padding tablet
	$css .= '@media only screen and (max-width: 992px) {';
	//icon padding tablet
	$css .= $root_selector . '{';
	foreach ($paddingSides as $side) {
		$paddingValue = esc_attr( $block['attrs']['blockAdvanceOptions']['iconPadding']['tablet'][$side] ?? '' );
		if (!empty($paddingValue)) {
			$css .= "padding-$side: $paddingValue;";
		}
	}
	$css .= '}';
	$css .= '}';


	//padding mobile
	$css .= '@media only screen and (max-width: 768px) {';
	//icon padding mobile
	$css .= $root_selector . '{';
	foreach ($paddingSides as $side) {
		$paddingValue = esc_attr( $block['attrs']['blockAdvanceOptions']['iconPadding']['mobile'][$side] ?? '' );
		if (!empty($paddingValue)) {
			$css .= "padding-$side: $paddingValue;";
		}
	}
	$css .= '}';
	$css .= '}';

	//hover style
	$css .= $root_selector . ':hover{';

	//hover color
	if (!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])) {
		$css .= 'color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverTextColor'] ) . ';';
	}

	//hover background
	if (!empty($block['attrs']['hoverBackgroundGradient'])) {
		$css .= 'background: ' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
	}elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'])) {
		$css .= 'background: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'] ) . ';';
	} else {
		$css .= '';
	}

	//hover border
	if (!empty($block['attrs']['blockAdvanceOptions']['hoverBorderColor'])) {
		$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBorderColor'] ) . ';';
	}

	$css .= '}';


	$handle = 'gutenify_'. str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
	wp_add_inline_style( $handle, $css);
	return $block_content;
}
}

Icon::init();
