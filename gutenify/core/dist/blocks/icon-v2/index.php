<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Icon_v2 {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/icon-v2', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {

		$root_selector = '.' . $block_id . ' .gutenify-icon-v2-wrapper';
		$css           = $root_selector . '{';

		// Icon color.
		if ( ! empty( $block['attrs']['color'] ) ) {
			$css .= 'color: ' . esc_attr( $block['attrs']['color'] ) . ';';
		}
		if ( ! empty( $block['attrs']['color'] ) ) {
			$css .= 'fill: ' . esc_attr( $block['attrs']['color'] ) . ';';
		} else {
			$css .= 'fill: currentColor;';
		}

		// Icon size.
		$icon_size = ! empty( $block['attrs']['iconSize'] ) ? esc_attr( $block['attrs']['iconSize'] ) : '40px';  // Default to 24px if empty.
		$css      .= 'display: inline-block;';
		// $css      .= 'height: ' . $icon_size . ';';

		// Background color or gradient
		if ( ! empty( $block['attrs']['backgroundGradient'] ) ) {
			$css .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
		} elseif ( ! empty( $block['attrs']['backgroundColor'] ) ) {
			$css .= 'background: ' . esc_attr( $block['attrs']['backgroundColor'] ) . ';';
		}
		// Border styles
		// if (!empty($block['attrs']['borderColor'])) {
		// $css .= 'border-color: ' . esc_attr($block['attrs']['borderColor']) . ';';
		// }
		$border_color = ! empty( $block['attrs']['borderColor'] ) ? $block['attrs']['borderColor'] : ( ! empty( $block['attrs']['color'] ) ? $block['attrs']['color'] : null );
		if ( $border_color ) {
			$css .= 'border-color: ' . esc_attr( $border_color ) . ';';
		}
		if ( ! empty( $block['attrs']['borderWidth'] ) ) {
			$css .= 'border-style: solid;';
			if ( ! is_array( $block['attrs']['borderWidth'] ) ) {
				$css .= 'border-width: ' . esc_attr( $block['attrs']['borderWidth'] ) . ';';
			} else {
				$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['borderWidth'], 'border-', '-width' );
			}
		}

		if ( ! empty( $block['attrs']['borderRadius'] ) ) {
			$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['borderRadius'] );
		}

		// Padding.
		if ( ! empty( $block['attrs']['iconPadding'] ) ) {
			if ( is_array( $block['attrs']['iconPadding'] ) ) {
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					if ( isset( $block['attrs']['iconPadding'][ $side ] ) ) {
						$padding = is_numeric( $block['attrs']['iconPadding'][ $side ] ) ?
								$block['attrs']['iconPadding'][ $side ] . 'px' :
								$block['attrs']['iconPadding'][ $side ];
						$css    .= "padding-{$side}: " . esc_attr( $padding ) . ';';
					}
				}
			} else {
				$padding = is_numeric( $block['attrs']['iconPadding'] ) ?
							$block['attrs']['iconPadding'] . 'px' :
							$block['attrs']['iconPadding'];
				$css    .= 'padding: ' . esc_attr( $padding ) . ';';
			}
		}
		$css .= '}';

		// Hover styles
		$css .= $root_selector . ':hover {';
		if ( ! empty( $block['attrs']['hoverColor'] ) ) {
			$css .= 'fill: ' . esc_attr( $block['attrs']['hoverColor'] ) . ';';
		}
		if ( ! empty( $block['attrs']['hoverBackgroundColor'] ) ) {
			$css .= 'background-color: ' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
		}
		if ( ! empty( $block['attrs']['hoverBackgroundGradient'] ) ) {
			$css .= 'background: ' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
		}
		if ( ! empty( $block['attrs']['hoverBorderColor'] ) ) {
			$css .= 'border-color: ' . esc_attr( $block['attrs']['hoverBorderColor'] ) . ';';
		}
		$css .= '}';

		// SVG styles
		$css .= $root_selector . ' svg {';
		if ( ! empty( $block['attrs']['iconSize'] ) ) {
			$css .= 'width: ' . esc_attr( $block['attrs']['iconSize'] ) . ';';
			$css .= 'height: ' . esc_attr( $block['attrs']['iconSize'] ) . ';';
		}
		$css .= '}';

		$css .= $root_selector . ' svg:hover {';
		if ( ! empty( $block['attrs']['hoverColor'] ) ) {
			$css .= 'fill: ' . esc_attr( $block['attrs']['hoverColor'] ) . ';';
		}
		$css .= '}';

		// Inline style addition
		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css );

		return $block_content;
	}
}

Icon_v2::init();
