<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Custom_list {
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_assets' ), 11 );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_scripts' ), 11 );
		add_filter( 'gutenify_render_block_core/list', array( __CLASS__, 'list_render' ), 10, 4 );
		add_filter( 'gutenify_render_block_core/list-item', array( __CLASS__, 'list_item_render' ), 10, 4 );
	}

	public static function register_assets() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir  = Helpers::core_base_dir();
		$base_url  = Helpers::core_base_url();
		$assets    = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/custom-list/index.asset.php', $base_dir ) );
		$handle    = $main_slug . '-extend-custom-list';

		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/custom-list/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/custom-list/index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}

	public static function enqueue_scripts() {
		$constants = Helpers::plugin_constants();
		$main_slug = $constants['plugin_main_slug'];
		$base_dir  = Helpers::core_base_dir();
		$base_url  = Helpers::core_base_url();
		$assets    = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/custom-list/frontend.asset.php', $base_dir ) );
		$handle    = $main_slug . '-extend-custom-list-frontend';

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/custom-list/frontend.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public static function list_render( $content, $block, $instance, $block_id ) {

		if ( empty( $block['blockName'] ) ) {
			return $content;
		}

		$css = '';
		if ( 'core/list' === $block['blockName'] ) {
			$content = new \WP_HTML_Tag_Processor( $content );
			$content->next_tag();
			if ( ! empty( $block['attrs']['activeCustomList'] ) ) {
				$content->add_class( 'gutenify-custom-list-active' );
			}
			$root_selector = 'ul.wp-block-list.' . $block_id . '.gutenify-custom-list-active:not(.is-style-checkmark-list) > li:not(.block-editor-block-list__layout)::before';
			$css          .= $root_selector . '{';

			$list_style = ! empty( $block['attrs']['listStyle'] ) ? $block['attrs']['listStyle'] : '';

			if ( ! empty( $list_style ) ) {
				$encodedSVG = rawurlencode( $list_style );
				$encodedSVG = 'data:image/svg+xml,' . $encodedSVG;
				$css       .= ! empty( $list_style ) ? '-webkit-mask-image:url("' . $encodedSVG . '");' : '';
			}

			$css .= ! empty( $block['attrs']['listStyleSize'] ) ? 'width: ' . esc_attr( $block['attrs']['listStyleSize'] ) . '; height: ' . esc_attr( $block['attrs']['listStyleSize'] ) . ';' : '';

			$css .= ! empty( $block['attrs']['listStyleColor'] ) ? 'background-color:' . $block['attrs']['listStyleColor'] . ';' : '';
			$css .= ! empty( $block['attrs']['gap'] ) ? 'margin-right:' . $block['attrs']['gap'] . ';' : '';
			$css .= '}';

			$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
			wp_add_inline_style( $handle, $css );
			return $content->get_updated_html();
		}
	}

	public static function list_item_render( $content, $block, $instance, $block_id ) {

		$css = '';
		if ( 'core/list-item' === $block['blockName'] ) {
			$content = new \WP_HTML_Tag_Processor( $content );
			$content->next_tag();
			$content->add_class( 'gutenify-list-item' );
			// if ( ! $content->next_tag() ) {
			// 	return $content->get_updated_html();
			// }
			// $content->next_tag();
			if ( ! empty( $block['attrs']['activeCustomList'] ) ) {
				// var_dump( $block['attrs'] );
				$content->add_class( 'gutenify-custom-list-active' );
			}

			$root_selector = 'ul.wp-block-list.gutenify-custom-list-active:not(.is-style-checkmark-list) > li.' . $block_id . '.gutenify-list-item:not(.block-editor-block-list__layout)::before';

			$css .= $root_selector . '{';

			$list_style = ! empty( $block['attrs']['listStyle'] ) ? $block['attrs']['listStyle'] : '';

			if ( ! empty( $list_style ) ) {
				$encodedSVG = rawurlencode( $list_style );
				$encodedSVG = 'data:image/svg+xml,' . $encodedSVG;
				$css       .= ! empty( $list_style ) ? '-webkit-mask-image:url("' . $encodedSVG . '");' : '';
			}

			$css .= ! empty( $block['attrs']['listStyleSize'] ) ? 'width: ' . esc_attr( $block['attrs']['listStyleSize'] ) . '; height: ' . esc_attr( $block['attrs']['listStyleSize'] ) . ';' : '';

			$css .= ! empty( $block['attrs']['listStyleColor'] ) ? 'background-color:' . $block['attrs']['listStyleColor'] . ';' : '';
			$css .= ! empty( $block['attrs']['gap'] ) ? 'margin-right:' . $block['attrs']['gap'] . ';' : '';
			$css .= '}';

			$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
			wp_add_inline_style( $handle, $css );
			return $content->get_updated_html();
		}
	}
}

Custom_List::init();
