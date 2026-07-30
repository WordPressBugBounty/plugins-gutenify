<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Section_Title {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/section-title', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	public static function render_callback( $attr ) {

		// Only allow safe heading tags for title_level to prevent XSS.
		$allowed_title_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' );

		$prefix_level = ! empty( $attr['prefixTag'] ) && in_array( strtolower( $attr['prefixTag'] ), $allowed_title_tags, true ) ? strtolower( $attr['prefixTag'] ) : 'h4';

		$prefix_css_classes        = ! empty( $attr['blockAdvanceOptions']['prefixTitleClasses'] ) ? $attr['blockAdvanceOptions']['prefixTitleClasses'] : '';
		$prefix_css_classes        = $prefix_css_classes . ' gutenify-section-title-prefix';
		$prefix_wrapper_attributes = '';
		if ( ! empty( $attr['prefixColor'] ) ) {
			$safe_prefix_color         = sanitize_hex_color( $attr['prefixColor'] ) ? sanitize_hex_color( $attr['prefixColor'] ) : esc_attr( $attr['prefixColor'] );
			$prefix_wrapper_attributes = 'style="color:' . $safe_prefix_color . '"';
		}
		$prefix_wrapper_attributes .= ' class="' . esc_attr( $prefix_css_classes ) . '"';
		$prefix_content             = ! empty( $attr['prefixContent'] ) ? $attr['prefixContent'] : '';

		$prefix = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			$prefix_level,
			$prefix_wrapper_attributes,
			$prefix_content
		);

		// Heading.
		$title_level      = ! empty( $attr['titleTag'] ) ? esc_attr( $attr['titleTag'] ) : 'h2';
		$main_css_classes = ! empty( $attr['blockAdvanceOptions']['mainTitleClasses'] ) ? $attr['blockAdvanceOptions']['mainTitleClasses'] : '';
		$main_css_classes = $main_css_classes . ' gutenify-section-title-main';

		$title_wrapper_attributes = '';
		if ( ! empty( $attr['titleColor'] ) ) {
			$safe_title_color         = sanitize_hex_color( $attr['titleColor'] ) ? sanitize_hex_color( $attr['titleColor'] ) : esc_attr( $attr['titleColor'] );
			$title_wrapper_attributes = 'style="color:' . $safe_title_color . '"';
		}
		$title_wrapper_attributes .= ' class="' . esc_attr( $main_css_classes ) . '"';
		$title_content             = ! empty( $attr['titleContent'] ) ? $attr['titleContent'] : '';

		$safe_title_level = in_array( strtolower( $title_level ), $allowed_title_tags, true ) ? strtolower( $title_level ) : 'h2';
		$title            = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			$safe_title_level,
			$title_wrapper_attributes,
			$title_content
		);

		// Suffix.
		// $suffix_level       = ! empty( $attr['suffixTag'] ) ? $attr['suffixTag'] : 'p';
		$suffix_level       = ! empty( $attr['suffixTag'] ) && in_array( strtolower( $attr['suffixTag'] ), $allowed_title_tags, true ) ? strtolower( $attr['suffixTag'] ) : 'p';
		$suffix_css_classes = ! empty( $attr['blockAdvanceOptions']['suffixTitleClasses'] ) ? $attr['blockAdvanceOptions']['suffixTitleClasses'] : '';
		$suffix_css_classes = $suffix_css_classes . ' gutenify-section-title-suffix';

		$suffix_wrapper_attributes = '';
		if ( ! empty( $attr['suffixColor'] ) ) {
			$safe_suffix_color         = sanitize_hex_color( $attr['suffixColor'] ) ? sanitize_hex_color( $attr['suffixColor'] ) : esc_attr( $attr['suffixColor'] );
			$suffix_wrapper_attributes = 'style="color:' . $safe_suffix_color . '"';
		}
		$suffix_wrapper_attributes .= ' class="' . esc_attr( $suffix_css_classes ) . '"';
		$suffix_content             = ! empty( $attr['suffixContent'] ) ? $attr['suffixContent'] : '';

		$suffix = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			$suffix_level,
			$suffix_wrapper_attributes,
			$suffix_content
		);

		/**
		 * End output.
		 */

		$textAlignment      = ! empty( $attr['textAlignment'] ) ? esc_attr( $attr['textAlignment'] ) : 'center';
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => implode(
					' ',
					array(
						'has-text-align-' . $textAlignment,
					)
				),
			)
		);
		$new_content        = sprintf( '<div %s>', $wrapper_attributes );

		$new_content .= '<div class="gutenify-section-title-section ">';
		if ( ! empty( $attr['displayPrefix'] ) ) {
			$new_content .= $prefix;
		}
		$new_content .= $title;
		if ( ! empty( $attr['displaySuffix'] ) ) {
			$new_content .= $suffix;
		}
		$new_content .= '</div>';

		$new_content .= '</div>';

		return $new_content;
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {

		$block_content = new \WP_HTML_Tag_Processor( $block_content );
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['prefixTitleClasses'] ) ) {
			$block_content->next_tag( array( 'class_name' => 'gutenify-section-title-prefix' ) );
			$block_content->add_class( esc_attr( $block['attrs']['blockAdvanceOptions']['prefixTitleClasses'] ) );
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['mainTitleClasses'] ) ) {
			$block_content->next_tag( array( 'class_name' => 'gutenify-section-title-main' ) );
			$block_content->add_class( esc_attr( $block['attrs']['blockAdvanceOptions']['mainTitleClasses'] ) );
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['suffixTitleClasses'] ) ) {
			$block_content->next_tag( array( 'class_name' => 'gutenify-section-title-suffix' ) );
			$block_content->add_class( esc_attr( $block['attrs']['blockAdvanceOptions']['suffixTitleClasses'] ) );
		}

		$block_content = $block_content->get_updated_html();

		$root_selector = '.' . $block_id;
		$css           = '';

		/**
		 * Prefix
		 */
		$css_chunk  = '';
		$css_chunk .= ! empty( $block['attrs']['prefixFontSize'] ) ? 'font-size:' . esc_attr( $block['attrs']['prefixFontSize'] ) . ';' : '';
		$css_chunk .= ! empty( $block['attrs']['prefixColor'] ) ? 'color:' . esc_attr( sanitize_hex_color( $block['attrs']['prefixColor'] ) ? sanitize_hex_color( $block['attrs']['prefixColor'] ) : $block['attrs']['prefixColor'] ) . ';' : '';
		$css_chunk .= isset( $block['attrs']['displayPrefix'] ) && false === $block['attrs']['displayPrefix'] ? 'display:none;' : '';
		if ( ! empty( $css_chunk ) ) {
			$css .= $root_selector . ' .gutenify-section-title-prefix{' . $css_chunk . '}';
		}

		/**
		 * Title
		 */
		$css_chunk  = '';
		$css_chunk .= ! empty( $block['attrs']['titleFontSize'] ) ? 'font-size:' . esc_attr( $block['attrs']['titleFontSize'] ) . ';' : '';
		$css_chunk .= ! empty( $block['attrs']['titleColor'] ) ? 'color:' . esc_attr( sanitize_hex_color( $block['attrs']['titleColor'] ) ? sanitize_hex_color( $block['attrs']['titleColor'] ) : $block['attrs']['titleColor'] ) . ';' : '';
		if ( ! empty( $css_chunk ) ) {
			$css .= $root_selector . ' .gutenify-section-title-main{' . $css_chunk . '}';
		}

		/**
		 * Suffix Title
		 */
		$css_chunk  = '';
		$css_chunk .= ! empty( $block['attrs']['suffixFontSize'] ) ? 'font-size:' . esc_attr( $block['attrs']['suffixFontSize'] ) . ';' : '';
		$css_chunk .= ! empty( $block['attrs']['suffixColor'] ) ? 'color:' . esc_attr( sanitize_hex_color( $block['attrs']['suffixColor'] ) ? sanitize_hex_color( $block['attrs']['suffixColor'] ) : $block['attrs']['suffixColor'] ) . ';' : '';
		$css_chunk .= isset( $block['attrs']['displaySuffix'] ) && false === $block['attrs']['displaySuffix'] ? 'display:none;' : '';
		if ( ! empty( $css_chunk ) ) {
			$css .= $root_selector . ' .gutenify-section-title-suffix{' . $css_chunk . '}';
		}

		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css );
		return $block_content;
	}
}

Section_Title::init();
