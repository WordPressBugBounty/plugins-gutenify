<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Faqs{
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/faqs', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {
		$root_selector = '.' . $block_id ;
		$css='';


	//border
	// Handle border attributes
	$css .= $root_selector . ' .gutenify-block-content-toggle-item-wrapper {';

		if (!empty($block['attrs']['blockAdvanceOptions']['border']['color'])) {
			$css .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['border']['color'] . '; ';
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['border']['width'] )  ) {
			$css .= 'border-style:solid;';
			if ( ! is_array( $block['attrs']['blockAdvanceOptions']['border']['width'] ) ) {
				$css .= 'border-width: ' . $block['attrs']['blockAdvanceOptions']['border']['width'] . 'px;';
			} else {
				$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['border']['width'], 'border-', '-width');
			}
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['border']['radius'] ) ) {
			$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['border']['radius'] );
		}

	if (!empty($block['attrs']['gap'])) {
		$css .= 'margin-bottom: ' . $block['attrs']['gap'] . ';';
	}

	$css .= '}';


	// Header normal
	if (!empty($block['attrs']['blockAdvanceOptions']['header']['textColor'])) {
		$css .= $root_selector . ' .gutenify-content-toggle-item-header>* { color:' . $block['attrs']['blockAdvanceOptions']['header']['textColor'] . ';}';
	}

	// Header styles
	$css .= $root_selector . ' .gutenify-content-toggle-item-header{';
	if (!empty($block['attrs']['headerBackgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['headerBackgroundGradient']) . ';';
	}elseif (!empty($block['attrs']['headerBackground'])) {
		$css .= 'background:' . ($block['attrs']['headerBackground']) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['header']['backgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['header']['backgroundGradient']) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['header']['backgroundColor'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['header']['backgroundColor']) . ';';
	}
	$css .= '}';

	// Header hover
	if (!empty($block['attrs']['blockAdvanceOptions']['header']['hoverTextColor'])) {
		$css .= $root_selector . ' .gutenify-content-toggle-item-header:hover>* { color:' . ($block['attrs']['blockAdvanceOptions']['header']['hoverTextColor']) . ';}';
	}

	$css .= $root_selector . ' .gutenify-content-toggle-item-header:hover{';
	if (!empty($block['attrs']['headerHoverBackgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['headerHoverBackgroundGradient']) . ';';
	}elseif (!empty($block['attrs']['headerHoverBackground'])) {
		$css .= 'background:' . ($block['attrs']['headerHoverBackground']) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['header']['hoverBackgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['header']['hoverBackgroundGradient']) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['header']['hoverBackgroundColor'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['header']['hoverBackgroundColor']) . ';';
	}
	$css .= '}';

	// Content normal
	$css .= $root_selector . ' .gutenify-content-toggle-item-content{';

	//color
	if (!empty($block['attrs']['blockAdvanceOptions']['content']['textColor'])) {
		$css .= 'color:' . $block['attrs']['blockAdvanceOptions']['content']['textColor'] . ';';
	}

	//background
	if (!empty($block['attrs']['contentBackgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['contentBackgroundGradient']) . ';';
	}elseif (!empty($block['attrs']['contentBackground'])) {
		$css .= 'background:' . ($block['attrs']['contentBackground']) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['content']['backgroundGradient'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['content']['backgroundGradient']) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['content']['backgroundColor'])) {
		$css .= 'background:' . ($block['attrs']['blockAdvanceOptions']['content']['backgroundColor']) . ';';
	}
	$css .= '}';

	$handle = 'gutenify_'. str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
	wp_add_inline_style( $handle, $css);
	return $block_content;
}
}

Faqs::init();
