<?php

namespace gutenify;


defined('ABSPATH') || exit;

class Button{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));
		add_filter('gutenify_render_block_gutenify/button', array(__CLASS__, 'render_block'), 10, 4);
	}

	public static function register_block() {
		register_block_type(__DIR__);
	}

	public static function render_block($block_content, $block, $instance, $block_id){

		$root_selector = '.' . $block_id;
		$css = '';

		/**
		 * Padding.
		 */
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['padding']  ) ) {
			$css .= Dynamic_Styles::get_spacing_with_media( $root_selector . ' .gutenify-button-link.wp-block-button__link', $block['attrs']['blockAdvanceOptions']['padding'], 'padding-' );
		}

		/**
		 * Margin.
		 */
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['margin']  ) ) {
			$css .= Dynamic_Styles::get_spacing_with_media( $root_selector . ' .gutenify-button-link.wp-block-button__link', $block['attrs']['blockAdvanceOptions']['margin'], 'margin-' );
		}

		$css .= $root_selector . ' .gutenify-button-link.wp-block-button__link{font: inherit;';

	/**
	 * Normal state.
	 */
	if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
		$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['textColor'] ) . ';';
	}
	if (!empty($block['attrs']['backgroundGradient'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
	}elseif (!empty($block['attrs']['backgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['backgroundColor'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundGradient'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundGradient'] ) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundColor'] ) . ';';
	} else {
		$css .= '';
	}

	// Border.
	if (!empty($block['attrs']['blockAdvanceOptions']['borderColor'])) {
		$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderColor'] ) . ';';
	}
	if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderWidth'] )  ) {
		$css .= 'border-style:solid;';
		if ( ! is_array( $block['attrs']['blockAdvanceOptions']['borderWidth'] ) ) {
			$css .= 'border-width: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth'] ) . 'px;';
		} else {
			$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['borderWidth'], 'border-', '-width');
		}
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) ) {
		$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['borderRadius'] );
	}
	// Icon.
	if (!empty($block['attrs']['icon']['position']) && 'after' === $block['attrs']['icon']['position']) {
		$css .= 'flex-direction: row-reverse;';
	}
	if (!empty($block['attrs']['icon']['spacing'])) {
		$css .= 'gap: ' . esc_attr( $block['attrs']['icon']['spacing'] ) . 'px;';
	}

	$css .= '}';

	/**
	 * Hover state.
	 */
	$css .= $root_selector . ' .wp-block-button__link:hover{ ';
	if (!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])) {
		$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverTextColor'] ) . ';';
	}


	if (!empty($block['attrs']['hoverBackgroundGradient'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
	} elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
	}elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'] ) . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'])) {
		$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'] ) . ';';
	} else {
		$css .= '';
	}

	// Border.
	if (!empty($block['attrs']['blockAdvanceOptions']['hoverBorderColor'])) {
		$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBorderColor'] ) . ';';
	}
	$css .= '}';

	$handle = 'gutenify_'. str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css);
		return $block_content;
	}
}

Button::init();

