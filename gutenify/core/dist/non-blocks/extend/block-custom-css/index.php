<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Block_Custom_CSS {
	public static function init() {
		add_filter('gutenify_render_block', array(__CLASS__, 'custom_css'), 10, 4);
	}
	public static function custom_css( $block_content, $block,  $instance, $block_id ) {
		if ( ! empty( $block['attrs']['customCss'] ) && ! empty( $block_id ) ) {
			$pattern = '/\$selector/m';
			$replacement = '.' . $block_id;
			$custom_css = preg_replace($pattern, $replacement,  $block['attrs']['customCss'] );
			$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
			wp_add_inline_style( $handle, $custom_css );
		}

		return $block_content;
	}
}
Block_Custom_CSS::init();
