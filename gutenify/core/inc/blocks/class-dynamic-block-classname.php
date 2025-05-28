<?php
/**
 * Dynamic Block Classname Handler
 *
 * This file contains the `Dynamic_Block_Classname` class, which allows the dynamic addition
 * of CSS classnames or inline styles to Gutenberg blocks during rendering.
 *
 * @package gutenify
 * @subpackage Functions
 * @since 1.0.0
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit; // Prevent direct access to the file.

/**
 * Class Dynamic_Block_Classname.
 *
 * Handles dynamic addition of CSS classnames to Gutenberg blocks.
 */
class Dynamic_Block_Classname {

	/**
	 * Initializes the class by hooking into 'render_block'.
	 */
	public static function init() {
		add_filter( 'render_block', array( __CLASS__, 'render_block' ), 10, 3 );
	}

	/**
	 * Hook callback for modifying block output during rendering.
	 *
	 * @param string $block_content The original block HTML.
	 * @param array  $block         The block settings.
	 * @param object $instance      The block instance (for reusable blocks).
	 * @return string Modified block HTML after applying custom filters.
	 */
	public static function render_block( $block_content, $block, $instance ) {
		// Skip processing for empty blocks or specific block types.
		if ( empty( $block['blockName'] ) || in_array( $block['blockName'], array( 'structured-content/faq-item', 'structured-content/faq' ), true ) ) {
			return $block_content;
		}

		// Generate a unique CSS class ID for this block.
		$block_id = wp_unique_id( 'gtfy-' );

		$html_processor = new \WP_HTML_Tag_Processor( $block_content );
		if ( $html_processor->next_tag() ) {
			$html_processor->add_class( $block_id );
		}

		// Enqueue dynamic styles if the block is valid.
		if ( is_array( $block ) && ! empty( $block ) ) {
			self::enqueue_dyanmic_style( $block, $block_id, $instance );
		}

		// Get the updated HTML content.
		$updated_html = $html_processor->get_updated_html();

		// Apply general and block-specific filters before returning.
		$filter_prefix = 'gutenify_render_block';
		$updated_html  = apply_filters( $filter_prefix, $updated_html, $block, $instance, $block_id );
		return apply_filters( $filter_prefix . '_' . $block['blockName'], $updated_html, $block, $instance, $block_id );
	}

	/**
	 * Enqueues dynamic styles for the block.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 */
	public static function enqueue_dyanmic_style( $block, $block_id, $instance ) {
		if ( empty( $block['blockName'] ) ) {
			return false; // Exit early if the block has no name.
		}

		// Create a unique style handle based on the block name and block ID.
		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;

		// Register and enqueue an empty stylesheet for attaching inline styles.
		wp_register_style( $handle, false );
		wp_enqueue_style( $handle );

		// Initialize CSS variable.
		$css = '';

		if ( ! in_array( $block['blockName'], array( 'gutenify/button' ), true ) ) {
			$css = self::spacing_style( $block, $block_id, $instance );
		}

		// Add the generated inline styles to the registered handle.
		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Generates dynamic spacing styles for the block.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function spacing_style( $block, $block_id, $instance ) {
		// Initialize the CSS output variable.
		$css = '';

		// Get default margin from instance attributes.
		$margin = ! empty( $instance->attributes['blockAdvanceOptions']['margin'] ) ? $instance->attributes['blockAdvanceOptions']['margin'] : array();

		// Merge with block-level margin if provided.
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['margin'] ) ) {
			$margin = wp_parse_args( $block['attrs']['blockAdvanceOptions']['margin'], $margin );
		}

		// Generate margin CSS.
		$css .= Dynamic_Styles::get_spacing_with_media( '.' . $block_id, $margin, 'margin-' );

		// Get default padding from instance attributes.
		$padding = ! empty( $instance->attributes['blockAdvanceOptions']['padding'] ) ? $instance->attributes['blockAdvanceOptions']['padding'] : array();

		// Merge with block-level padding if provided.
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['padding'] ) ) {
			$padding = wp_parse_args( $block['attrs']['blockAdvanceOptions']['padding'], $padding );
		}

		// Generate padding CSS.
		$css .= Dynamic_Styles::get_spacing_with_media( '.' . $block_id, $padding, 'padding-' );

		// Return the combined spacing styles.
		return $css;
	}
}

// Initialize the class.
Dynamic_Block_Classname::init();
