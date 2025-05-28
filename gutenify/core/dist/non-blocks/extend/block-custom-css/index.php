<?php
/**
 * Block Custom CSS
 *
 * Allows user-defined custom CSS to be rendered for individual blocks.
 * Replaces `$selector` in user CSS with the unique block class, and adds the CSS inline to the block's handle.
 *
 * @package gutenify
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Inject block-specific custom CSS if provided in block attributes.
 *
 * If the block has a 'customCss' attribute and a block ID, this function will:
 *  - Replace all occurrences of $selector in the CSS with the unique block class.
 *  - Add the CSS as inline styles for the rendered block.
 *
 * @param string $block_content The content of the block.
 * @param array  $block         The full block data array.
 * @param mixed  $instance      The block instance (not used).
 * @param string $block_id      The unique block ID.
 *
 * @return string The (unmodified) block content.
 */
function gutenify_block_custom_css_callback( $block_content, $block, $instance, $block_id ) {
	// Only proceed if customCss attribute and block_id are present.
	if ( ! empty( $block['attrs']['customCss'] ) && ! empty( $block_id ) ) {
		// Replace $selector in user CSS with the block's unique class.
		$pattern     = '/\$selector/m';
		$replacement = '.' . $block_id;
		$custom_css  = preg_replace( $pattern, $replacement, $block['attrs']['customCss'] );

		// Generate a unique style handle for this block instance.
		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;

		// Add the custom CSS inline for this block.
		wp_add_inline_style( $handle, $custom_css );
	}

	// Always return the original block content.
	return $block_content;
}

// Register the custom CSS handler for blocks.
add_filter( 'gutenify_render_block', __NAMESPACE__ . '\\gutenify_block_custom_css_callback', 10, 4 );
