<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Star_Rating
{
	public static function init()
	{
		add_action('init', array(__CLASS__, 'register_block'));
		add_filter('gutenify_render_block_gutenify/star-rating', array(__CLASS__, 'render_block'), 10, 4);
	}

	public static function register_block()
	{
		register_block_type(__DIR__);
	}

	public static function render_block($block_content, $block, $instance, $block_id)
	{
		$root_selector = '.' . $block_id;
		$css = '';

		$css .= $root_selector . ' .gutenify-star-rating-filled,' . $root_selector . ' .gutenify-star-rating-half{';
		if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
			$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['textColor'] ) . ';';
		} else {
			$css .= 'color: #ff9800;';
		}
		$css .= '}';

		$css .= $root_selector . ' .gutenify-star-rating-empty{';
		if (!empty($block['attrs']['blockAdvanceOptions']['iconUnmarkedColor'])) {
			$css .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['iconUnmarkedColor'] ) . ';';
		}
		$css .= '}';

		$css .= $root_selector . ' span{';
		if (!empty($block['attrs']['blockAdvanceOptions']['iconSize'])) {
			$css .= 'font-size:' . esc_attr( $block['attrs']['blockAdvanceOptions']['iconSize'] ) . 'px;';
		}
		$css .= '}';

		$css .= $root_selector . ' .gutenify-star-rating-section{';
		if (!empty($block['attrs']['blockAdvanceOptions']['iconGap'])) {
			$css .= 'gap:' . esc_attr( $block['attrs']['blockAdvanceOptions']['iconGap'] ) . 'px;';
		}

		/**
		 * Pro direction feature.
		 */
		if (!empty($block['attrs']['direction'] )) {
			$css .= 'flex-direction:' . esc_attr( $block['attrs']['direction'] ) . ';';
		}
		$css .= '}';


		$handle = 'gutenify_' . str_replace('/', '_', $block['blockName']) . '_' . $block_id;
		wp_add_inline_style($handle, $css);
		return $block_content;
	}
}

Star_Rating::init();
