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

		$css .= self::position_style( $block, $block_id, $instance );
		$css .= self::transform_style( $block, $block_id, $instance );
		$css .= self::effects_style( $block, $block_id, $instance );
		$css .= self::colors_style( $block, $block_id, $instance );
		$css .= self::transition_style( $block, $block_id, $instance );
		$css .= self::box_shadow_style( $block, $block_id, $instance );
		$css .= self::layout_style( $block, $block_id, $instance );
		$css .= self::container_layout_style( $block, $block_id, $instance );

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

	/**
	 * Generates dynamic position styles (position, offsets, z-index, width,
	 * height, overflow) for the block, including any block-level custom
	 * breakpoints (e.g. AdvancedGroup's `customBlockBreakpoints` attribute).
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function position_style( $block, $block_id, $instance ) {
		$position = ! empty( $instance->attributes['advancedStyling']['position']['normal'] ) ? $instance->attributes['advancedStyling']['position']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['position']['normal'] ) ) {
			$position = wp_parse_args( $block['attrs']['advancedStyling']['position']['normal'], $position );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $position ) ) {
			$css .= Dynamic_Styles::get_position_with_media( '.' . $block_id, $position, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — evaluated independently of the
		// Normal bucket above, since a block can have a state-only effect
		// (e.g. "scale up on hover") with no base position/transform at all.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'position',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_position_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}

	/**
	 * Generates dynamic transform styles (transform, transform-origin) for
	 * the block, including any block-level custom breakpoints.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function transform_style( $block, $block_id, $instance ) {
		$transform = ! empty( $instance->attributes['advancedStyling']['transform']['normal'] ) ? $instance->attributes['advancedStyling']['transform']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['transform']['normal'] ) ) {
			$transform = wp_parse_args( $block['attrs']['advancedStyling']['transform']['normal'], $transform );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $transform ) ) {
			$css .= Dynamic_Styles::get_transform_with_media( '.' . $block_id, $transform, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — see position_style() above for
		// why this runs regardless of whether the Normal bucket was empty.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'transform',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_transform_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}

	/**
	 * Generates dynamic effects styles (opacity, filter, backdrop-filter) for
	 * the block, including any block-level custom breakpoints.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function effects_style( $block, $block_id, $instance ) {
		$effects = ! empty( $instance->attributes['advancedStyling']['effects']['normal'] ) ? $instance->attributes['advancedStyling']['effects']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['effects']['normal'] ) ) {
			$effects = wp_parse_args( $block['attrs']['advancedStyling']['effects']['normal'], $effects );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $effects ) ) {
			$css .= Dynamic_Styles::get_effects_with_media( '.' . $block_id, $effects, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — see position_style() above for
		// why this runs regardless of whether the Normal bucket was empty.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'effects',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_effects_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}

	/**
	 * Generates dynamic color styles (background-color, color) for the
	 * block, including any block-level custom breakpoints.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function colors_style( $block, $block_id, $instance ) {
		$colors = ! empty( $instance->attributes['advancedStyling']['colors']['normal'] ) ? $instance->attributes['advancedStyling']['colors']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['colors']['normal'] ) ) {
			$colors = wp_parse_args( $block['attrs']['advancedStyling']['colors']['normal'], $colors );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $colors ) ) {
			$css .= Dynamic_Styles::get_colors_with_media( '.' . $block_id, $colors, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — see position_style() above for
		// why this runs regardless of whether the Normal bucket was empty.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'colors',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_colors_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}

	/**
	 * Generates dynamic `transition-duration`/`transition-timing-function`/
	 * `transition-delay` CSS for the block, including any block-level custom
	 * breakpoints. Normal-only — unlike position/transform/effects/colors,
	 * this deliberately does NOT call get_state_variants_css(): `transition`
	 * must live on the resting selector to animate a state change in both
	 * directions (entering AND leaving :hover/:active/:focus-within), so it
	 * has no Hover/Active/Focus bucket of its own to read.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function transition_style( $block, $block_id, $instance ) {
		$transition = ! empty( $instance->attributes['advancedStyling']['transition']['normal'] ) ? $instance->attributes['advancedStyling']['transition']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['transition']['normal'] ) ) {
			$transition = wp_parse_args( $block['attrs']['advancedStyling']['transition']['normal'], $transition );
		}

		if ( empty( $transition ) ) {
			return '';
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		return Dynamic_Styles::get_transition_with_media( '.' . $block_id, $transition, $custom_breakpoints );
	}

	/**
	 * Generates dynamic `box-shadow` CSS for the block, including any
	 * block-level custom breakpoints. State-crossed like colors_style() —
	 * box-shadow-on-hover is a common effect.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function box_shadow_style( $block, $block_id, $instance ) {
		$box_shadow = ! empty( $instance->attributes['advancedStyling']['boxShadow']['normal'] ) ? $instance->attributes['advancedStyling']['boxShadow']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['boxShadow']['normal'] ) ) {
			$box_shadow = wp_parse_args( $block['attrs']['advancedStyling']['boxShadow']['normal'], $box_shadow );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $box_shadow ) ) {
			$css .= Dynamic_Styles::get_box_shadow_with_media( '.' . $block_id, $box_shadow, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — see position_style() above for why this runs regardless of whether the Normal bucket was empty.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'boxShadow',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_box_shadow_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}

	/**
	 * Generates dynamic `flex-grow`/`flex-shrink`/`flex-basis`/`align-self`/
	 * `order`/`cursor` CSS for the block, including any block-level custom
	 * breakpoints. Normal-only — see get_layout_with_media()'s docblock for
	 * why this has no Hover/Active/Focus variant.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function layout_style( $block, $block_id, $instance ) {
		$layout = ! empty( $instance->attributes['advancedStyling']['layout']['normal'] ) ? $instance->attributes['advancedStyling']['layout']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['layout']['normal'] ) ) {
			$layout = wp_parse_args( $block['attrs']['advancedStyling']['layout']['normal'], $layout );
		}

		if ( empty( $layout ) ) {
			return '';
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		return Dynamic_Styles::get_layout_with_media( '.' . $block_id, $layout, $custom_breakpoints );
	}

	/**
	 * Generates dynamic `flex-direction`/`flex-wrap`/`gap`/`align-items`/
	 * `justify-content` CSS for the block — the CONTAINER side of flex/grid
	 * layout (how this block lays out its children), as opposed to
	 * layout_style() above (the CHILD side). State-crossed like colors_style()
	 * — these fields share the same resolved Display value the "Position"
	 * section's Display field already supports per state.
	 *
	 * @param array  $block    The block settings.
	 * @param string $block_id Unique ID for the block instance.
	 * @param object $instance The block instance.
	 * @return string CSS styles.
	 */
	public static function container_layout_style( $block, $block_id, $instance ) {
		$container_layout = ! empty( $instance->attributes['advancedStyling']['containerLayout']['normal'] ) ? $instance->attributes['advancedStyling']['containerLayout']['normal'] : array();

		if ( ! empty( $block['attrs']['advancedStyling']['containerLayout']['normal'] ) ) {
			$container_layout = wp_parse_args( $block['attrs']['advancedStyling']['containerLayout']['normal'], $container_layout );
		}

		$custom_breakpoints = ! empty( $block['attrs']['customBlockBreakpoints'] ) && is_array( $block['attrs']['customBlockBreakpoints'] )
			? $block['attrs']['customBlockBreakpoints']
			: array();

		$css = '';

		if ( ! empty( $container_layout ) ) {
			$css .= Dynamic_Styles::get_container_layout_with_media( '.' . $block_id, $container_layout, $custom_breakpoints );
		}

		// Hover/Active/Focus-within variants — see position_style() above for why this runs regardless of whether the Normal bucket was empty.
		$css .= Dynamic_Styles::get_state_variants_css(
			'.' . $block_id,
			$block['attrs'] ?? array(),
			$instance->attributes,
			'containerLayout',
			$custom_breakpoints,
			function ( $sel, $bucket, $cbp ) {
				return Dynamic_Styles::get_container_layout_with_media( $sel, $bucket, $cbp );
			}
		);

		return $css;
	}
}

// Initialize the class.
Dynamic_Block_Classname::init();
