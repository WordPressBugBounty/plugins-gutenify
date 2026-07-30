<?php
/**
 * Dynamic_Styles class for generating responsive spacing styles with media queries.
 *
 * @package gutenify
 * @since   1.0.0
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Dynamic_Styles class for generating responsive spacing styles with media queries.
 *
 * @since   1.0.0
 */
class Dynamic_Styles {

	/**
	 * Generate spacing CSS with media queries for desktop, tablet, and mobile.
	 *
	 * @param string $selector The CSS selector to apply the styles to.
	 * @param array  $styles   Associative array containing 'desktop', 'tablet', 'mobile' keys with style values.
	 * @param string $prefix   The CSS prefix (default is 'margin-').
	 * @return string          The generated CSS string.
	 */
	public static function get_spacing_with_media( $selector, $styles, $prefix = 'margin-' ) {
		$style_chunk = '';

		if ( ! empty( $styles['desktop'] ) ) {
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['desktop'], $prefix );
			$style_chunk .= '}';
		}

		if ( ! empty( $styles['tablet'] ) ) {
			$style_chunk .= '@media only screen and (max-width: ' . \gutenify\Style_Helpers::$tablet_max_width . ') {';
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['tablet'], $prefix );
			$style_chunk .= '}';
			$style_chunk .= '}';
		}

		if ( ! empty( $styles['mobile'] ) ) {
			$style_chunk .= '@media only screen and (max-width: ' . \gutenify\Style_Helpers::$mobile_max_width . ') {';
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['mobile'], $prefix );
			$style_chunk .= '}';
			$style_chunk .= '}';
		}
		return $style_chunk;
	}

	/**
	 * Generate position CSS with media queries for desktop, tablet, mobile, and
	 * any block-level custom breakpoints (see AdvancedGroup's
	 * `customBlockBreakpoints` attribute — the only current caller of that).
	 *
	 * `$position` is either the flat (non-responsive) shape — {type, top, ...}
	 * directly — or the responsive shape — {desktop: {...}, tablet: {...},
	 * mobile: {...}, [customId]: {...}}. Detected by shape, not by caller, so a
	 * flat value (from a block not yet upgraded to responsive Position controls,
	 * or content saved before the upgrade) keeps rendering exactly as before.
	 * zIndex/overflow are per-breakpoint like everything else here — each
	 * breakpoint's own bucket carries its own zIndex/overflow if the editor
	 * wrote one there; Style_Helpers::position_control() reads both directly
	 * off whatever `$values` it's given, no special-casing needed here.
	 *
	 * @param string $selector           The CSS selector to apply the styles to.
	 * @param array  $position           The stored `blockAdvanceOptions.position` value.
	 * @param array  $custom_breakpoints The block's `customBlockBreakpoints` attribute, if any.
	 * @return string                    The generated CSS string.
	 */
	public static function get_position_with_media( $selector, $position, $custom_breakpoints = array() ) {
		if ( empty( $position ) || ! is_array( $position ) ) {
			return '';
		}

		$breakpoints = array(
			array(
				'id'    => 'desktop',
				'width' => null,
			),
			array(
				'id'    => 'tablet',
				'width' => (int) \gutenify\Style_Helpers::$tablet_max_width,
			),
			array(
				'id'    => 'mobile',
				'width' => (int) \gutenify\Style_Helpers::$mobile_max_width,
			),
		);

		$is_responsive = false;
		foreach ( $breakpoints as $bp ) {
			if ( ! empty( $position[ $bp['id'] ] ) && is_array( $position[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::position_control( $position );
			return $declarations ? $selector . '{' . $declarations . '}' : '';
		}

		if ( is_array( $custom_breakpoints ) ) {
			foreach ( $custom_breakpoints as $custom_bp ) {
				if ( ! empty( $custom_bp['id'] ) && isset( $custom_bp['width'] ) ) {
					$breakpoints[] = array(
						'id'    => (string) $custom_bp['id'],
						'width' => (int) $custom_bp['width'],
					);
				}
			}
		}

		usort(
			$breakpoints,
			function ( $a, $b ) {
				$width_a = null === $a['width'] ? PHP_INT_MAX : $a['width'];
				$width_b = null === $b['width'] ? PHP_INT_MAX : $b['width'];
				return $width_b <=> $width_a;
			}
		);

		$css = '';

		foreach ( $breakpoints as $bp ) {
			$values = ! empty( $position[ $bp['id'] ] ) && is_array( $position[ $bp['id'] ] ) ? $position[ $bp['id'] ] : array();

			$declarations = \gutenify\Style_Helpers::position_control( $values );
			if ( ! $declarations ) {
				continue;
			}

			if ( null === $bp['width'] ) {
				$css .= $selector . '{' . $declarations . '}';
			} else {
				$css .= '@media only screen and (max-width: ' . (int) $bp['width'] . 'px) {' . $selector . '{' . $declarations . '}}';
			}
		}

		return $css;
	}
}
