<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Info_Box
{
	public static function init()
	{
		add_action('init', array(__CLASS__, 'register_block'));
		add_filter('gutenify_render_block_gutenify/info-box', array(__CLASS__, 'render_block'), 10, 4);
	}

	public static function register_block()
	{
		register_block_type(__DIR__);
	}

	public static function render_block($block_content, $block, $instance, $block_id)
	{
		$root_selector = '.' . $block_id;
		$css = '';

		$css_chunk = '';

		//text
		if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['textColor'] . ';';
		}

		//background
		if (!empty($block['attrs']['backgroundGradient'])) {
			$css_chunk .= 'background:' . $block['attrs']['backgroundGradient'] . ';';
		} elseif (!empty($block['attrs']['backgroundColor'])) {
			$css_chunk .= 'background: ' . $block['attrs']['backgroundColor'] . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundGradient'])) {
			$css_chunk .= 'background: ' . $block['attrs']['blockAdvanceOptions']['backgroundGradient'] . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundColor'])) {
			$css_chunk .= 'background: ' . $block['attrs']['blockAdvanceOptions']['backgroundColor'] . ';';
		}

		//border
		//border width

		if ( ! empty( $block['attrs']['borderWidth'] )  ) {
			$css_chunk .= 'border-style:solid;';
			$css_chunk .= \gutenify\Style_Helpers::box_control( $block['attrs']['borderWidth'], 'border-', '-width');
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderWidth'] )  ) {
			$css_chunk .= 'border-style:solid;';
			$css_chunk .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['borderWidth'], 'border-', '-width');
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) ) {
			$css_chunk .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['borderRadius'] );
		}


		//border color
		if (!empty($block['attrs']['blockAdvanceOptions']['borderColor'])) {
			$css_chunk .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['borderColor'] . ';';
		}


		//border radius
		if ( ! empty( $block['attrs']['borderRadius'] ) ) {
			$css_chunk .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['borderRadius'] );
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) ) {
			$css_chunk .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['borderRadius'] );
		}


		if (!empty($css_chunk)) {
			$css .= $root_selector . ' {' . $css_chunk . '}';
		}

		/**
		 * Hover
		 */
		$css_chunk = '';

		//hover color
		if (!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['hoverTextColor'] . ';';
		}

		//hover background
		if (!empty($block['attrs']['hoverBackgroundGradient'])) {
			$css_chunk .= 'background: ' . $block['attrs']['hoverBackgroundGradient'] . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'])) {
			$css_chunk .= 'background: ' . $block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'] . ';';
		} elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
			$css_chunk .= 'background:' . $block['attrs']['hoverBackgroundColor'] . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'])) {
			$css_chunk .= 'background:' . $block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'] . ';';
		} else {
			$css_chunk .= '';
		}

		//hover border
		if (!empty($block['attrs']['blockAdvanceOptions']['hoverBorderColor'])) {
			$css_chunk .= 'border-color:' . $block['attrs']['blockAdvanceOptions']['hoverBorderColor'] . ';';
		}
		if (!empty($css_chunk)) {
			$css .= $root_selector . ':hover {' . $css_chunk . '}';
		}

		/**
		 * Heading
		 */
		$css_chunk = '';
		if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['textColor'] . ';';
		}
		if (!empty($css_chunk)) {
			$css .= $root_selector . ' :where(h1,h2,h3,h4,h5,h6){' . $css_chunk . '}';
		}

		// Hover Heading
		$css_chunk = '';
		if (!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['hoverTextColor'] . ';';
		}
		if (!empty($css_chunk)) {
			$css .= $root_selector . ':hover .wp-block-heading{' . $css_chunk . '}';
		}


		$handle = 'gutenify_' . str_replace('/', '_', $block['blockName']) . '_' . $block_id;
		wp_add_inline_style($handle, $css);
		return $block_content;
	}
}

Info_Box::init();
