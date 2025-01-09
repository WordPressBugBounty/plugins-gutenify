<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Dynamic_Block_Classname
{
	public static function init()
	{
		add_filter('render_block', array(__CLASS__, 'render_block'), 10, 3);
	}

	public static function render_block($block_content, $block, $instance)
	{
		$block_id = wp_unique_id('gtfy-');
		$block_content = new \WP_HTML_Tag_Processor($block_content);
		$block_content->next_tag();
		$block_content->add_class($block_id);

		if( ! is_wp_error( $block ) && ! empty( $block ) ) {
			self::enqueue_dyanmic_style($block, $block_id, $instance);
		}
		$block_content = apply_filters('gutenify_render_block', $block_content->get_updated_html(), $block, $instance, $block_id);

		return apply_filters('gutenify_render_block_' . $block['blockName'], $block_content, $block, $instance, $block_id);
	}

	public static function enqueue_dyanmic_style($block, $block_id, $instance)
	{
		if ( empty( $block['blockName'] ) ) {
			return false;
		}
		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;

		wp_register_style($handle, false);
		wp_enqueue_style($handle);

		$css_thread = '';
		if ( ! in_array( $block['blockName'], array( 'gutenify/button' ) ) ) {
			$css_thread = self::spacing_style($block, $block_id, $instance);
		}

		wp_add_inline_style($handle, $css_thread);
	}

	public static function spacing_style($block, $block_id, $instance) {
		$css_thread = '';

		$margin = ! empty( $instance->attributes['blockAdvanceOptions']['margin'] ) ? $instance->attributes['blockAdvanceOptions']['margin'] : array();
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['margin'] ) ) {
			$margin = wp_parse_args( $block['attrs']['blockAdvanceOptions']['margin'], $margin );
		}
		$css_thread .= Dynamic_Styles::get_spacing_with_media( '.' . $block_id, $margin, 'margin-' );

		$padding = ! empty( $instance->attributes['blockAdvanceOptions']['padding'] ) ? $instance->attributes['blockAdvanceOptions']['padding'] : array();
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['padding'] ) ) {
			$padding = wp_parse_args( $block['attrs']['blockAdvanceOptions']['padding'], $padding );
		}
		$css_thread .= Dynamic_Styles::get_spacing_with_media( '.' . $block_id, $padding, 'padding-' );
		return $css_thread;
	}
}

Dynamic_Block_Classname::init();
