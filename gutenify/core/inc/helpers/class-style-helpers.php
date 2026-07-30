<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Style_Helpers {
	public static $tablet_max_width = '992px';
	public static $mobile_max_width = '768px';
	public static function value_has_unit( $val ) {
		$val       = strtolower( $val );
		$units     = array();
		$all_units = array( 'px', 'rem' );
		foreach ( $all_units as $key => $unit ) {
			if ( false !== strpos( $val, $unit ) ) {
				$units[] = $unit;
			}
		}

		return count( $units ) > 0;
	}

	public static function box_control( $args = array(), $prefix = '', $suffix = '' ) {
		$css = '';
		if ( ! empty( $args ) ) {
			if ( ! is_array( $args ) ) {
				$value = self::value_has_unit( $args ) ? $args : $args . 'px';
				$css  .= str_replace( '--', '-', $prefix . $suffix ) . ':' . esc_attr( $value ) . ';';
			} else {
				foreach ( $args as $key => $val ) {
					if ( in_array( $key, array( 'top', 'bottom', 'left', 'right' ) ) ) {
						$value = self::value_has_unit( $val ) ? $val : $val . 'px';
						$css  .= $prefix . $key . $suffix . ':' . esc_attr( $value ) . ';';
					}
				}
			}
		}
		return $css;
	}

	public static function border_radius_control( $args, $prefix = 'border-', $suffix = '-radius' ) {
		$css = '';
		if ( ! empty( $args ) && ! is_array( $args ) ) {
			$css .= str_replace( '--', '-', $prefix . $suffix ) . ':' . esc_attr( $args ) . ';';
		}
		if ( ! empty( $args ) && is_array( $args ) ) {
			$areas = array(
				'topLeft'     => 'top-left',
				'topRight'    => 'top-right',
				'bottomLeft'  => 'bottom-left',
				'bottomRight' => 'bottom-right',
			);
			foreach ( $args as $key => $val ) {
				if ( in_array( $key, array_keys( $areas ) ) ) {
					$css .= $prefix . $areas[ $key ] . $suffix . ':' . esc_attr( $val ) . ';';
				}
			}
		}
		return $css;
	}

	/**
	 * Recognizes a broader set of CSS units than value_has_unit() (px, %, em,
	 * rem, vh, vw, vmin, vmax, ch) — used only by position_control() below, kept
	 * separate so the existing box_control()/border_radius_control() behaviour
	 * (and every block currently relying on it) is left untouched.
	 */
	public static function position_value_has_unit( $value ) {
		return (bool) preg_match( '/^-?[\d.]+(px|%|em|rem|vh|vw|vmin|vmax|ch)$/i', trim( (string) $value ) );
	}

	public static function position_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		$offset_types = array( 'relative', 'absolute', 'fixed', 'sticky' );

		// array_key_exists (not just a truthy check on the value) matters here:
		// a responsive breakpoint that explicitly picks "Static" must still emit
		// `position:static` to cancel a wider breakpoint's relative/absolute/fixed/
		// sticky — omitting it entirely (as a "nothing to do" no-op) would let the
		// wider rule silently keep applying at the narrower viewport.
		if ( array_key_exists( 'type', $args ) ) {
			$type = in_array( $args['type'], $offset_types, true ) ? $args['type'] : 'static';
			$css .= 'position:' . esc_attr( $type ) . ';';

			if ( in_array( $type, $offset_types, true ) ) {
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					if ( isset( $args[ $side ] ) && '' !== $args[ $side ] ) {
						$value = self::position_value_has_unit( $args[ $side ] ) ? $args[ $side ] : $args[ $side ] . 'px';
						$css  .= $side . ':' . esc_attr( $value ) . ';';
					}
				}
			}
		}

		// Not nested inside the `type` block above: a breakpoint can validly set
		// its own Z-Index while inheriting `type` from a wider breakpoint (the UI's
		// showOffsets already hides this field unless the *resolved* type needs it,
		// so by the time a value lands here it's already meaningful).
		if ( isset( $args['zIndex'] ) && '' !== $args['zIndex'] ) {
			$css .= 'z-index:' . esc_attr( (int) $args['zIndex'] ) . ';';
		}

		if ( ! empty( $args['width'] ) ) {
			$value = self::position_value_has_unit( $args['width'] ) ? $args['width'] : $args['width'] . 'px';
			$css  .= 'width:' . esc_attr( $value ) . ';';
		}

		if ( ! empty( $args['maxWidth'] ) ) {
			// !important: the theme's `.is-layout-constrained > :where(...)` rule has
			// equal specificity and prints after ours, so it wins on a tie otherwise.
			$value = self::position_value_has_unit( $args['maxWidth'] ) ? $args['maxWidth'] : $args['maxWidth'] . 'px';
			$css  .= 'max-width:' . esc_attr( $value ) . ' !important;';
		} elseif ( ! empty( $args['width'] ) ) {
			$css .= 'max-width:none !important;';
		}

		if ( ! empty( $args['height'] ) ) {
			$value = self::position_value_has_unit( $args['height'] ) ? $args['height'] : $args['height'] . 'px';
			$css  .= 'height:' . esc_attr( $value ) . ';';
		}

		$allowed_overflow = array( 'visible', 'hidden', 'scroll', 'auto' );
		if ( ! empty( $args['overflow'] ) && in_array( $args['overflow'], $allowed_overflow, true ) ) {
			$css .= 'overflow:' . esc_attr( $args['overflow'] ) . ';';
		}

		return $css;
	}

	public static function custom_box_shadow_control( $args ) {
		$color      = '#7a7a7a';
		$horizontal = '0px';
		$vertical   = '0px';
		$blur       = '32px';
		$spread     = '0px';
		$position   = '';

		$color      = ! empty( $args['color'] ) ? $args['color'] : $color;
		$horizontal = ! empty( $args['horizontal'] ) ? $args['horizontal'] . 'px' : $horizontal;
		$vertical   = ! empty( $args['vertical'] ) ? $args['vertical'] . 'px' : $vertical;
		$blur       = ! empty( $args['blur'] ) ? $args['blur'] . 'px' : $blur;
		$spread     = ! empty( $args['spread'] ) ? $args['spread'] . 'px' : $spread;
		$position   = ! empty( $args['position'] ) && 'inset' === $args['position'] ? 'inset' : $position;

		$css = ' box-shadow:' . esc_attr( $horizontal ) . ' ' . esc_attr( $vertical ) . ' ' . esc_attr( $blur ) . ' ' . esc_attr( $spread ) . ' ' . esc_attr( $color ) . ' ' . esc_attr( $position ) . ';';

		return $css;
	}
}
