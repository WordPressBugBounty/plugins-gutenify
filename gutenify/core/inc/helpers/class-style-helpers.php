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

		// `display:none` at a given breakpoint doubles as responsive visibility — no separate "hide on X" toggle needed.
		if ( ! empty( $args['display'] ) ) {
			$css .= 'display:' . esc_attr( $args['display'] ) . ';';
		}

		return $css;
	}

	/**
	 * Builds the `transform:` function list (translateX/Y, rotate, scaleX/Y,
	 * skewX/Y) from a single bucket of values. Returns '' if none are set.
	 * Order (translate, rotate, scale, skew) matches the JS mirror in
	 * block-transform/inline-styles.js — keep both in sync.
	 */
	public static function build_transform_value( $fields ) {
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return '';
		}

		$functions = array();

		if ( isset( $fields['translateX'] ) && '' !== $fields['translateX'] ) {
			$value       = self::position_value_has_unit( $fields['translateX'] ) ? $fields['translateX'] : $fields['translateX'] . 'px';
			$functions[] = 'translateX(' . esc_attr( $value ) . ')';
		}
		if ( isset( $fields['translateY'] ) && '' !== $fields['translateY'] ) {
			$value       = self::position_value_has_unit( $fields['translateY'] ) ? $fields['translateY'] : $fields['translateY'] . 'px';
			$functions[] = 'translateY(' . esc_attr( $value ) . ')';
		}
		if ( isset( $fields['rotate'] ) && '' !== $fields['rotate'] ) {
			$functions[] = 'rotate(' . esc_attr( (float) $fields['rotate'] ) . 'deg)';
		}
		if ( isset( $fields['scaleX'] ) && '' !== $fields['scaleX'] ) {
			$functions[] = 'scaleX(' . esc_attr( (float) $fields['scaleX'] ) . ')';
		}
		if ( isset( $fields['scaleY'] ) && '' !== $fields['scaleY'] ) {
			$functions[] = 'scaleY(' . esc_attr( (float) $fields['scaleY'] ) . ')';
		}
		if ( isset( $fields['skewX'] ) && '' !== $fields['skewX'] ) {
			$functions[] = 'skewX(' . esc_attr( (float) $fields['skewX'] ) . 'deg)';
		}
		if ( isset( $fields['skewY'] ) && '' !== $fields['skewY'] ) {
			$functions[] = 'skewY(' . esc_attr( (float) $fields['skewY'] ) . 'deg)';
		}

		return implode( ' ', $functions );
	}

	/**
	 * Builds the `transform-origin:` value from a single bucket of values.
	 * Returns '' if neither axis is set. A set axis with the other axis unset
	 * falls back to the CSS default (50%, i.e. center) for that axis only.
	 */
	public static function build_transform_origin_value( $fields ) {
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return '';
		}

		$has_x = isset( $fields['originX'] ) && '' !== $fields['originX'];
		$has_y = isset( $fields['originY'] ) && '' !== $fields['originY'];

		if ( ! $has_x && ! $has_y ) {
			return '';
		}

		$x = $has_x ? ( self::position_value_has_unit( $fields['originX'] ) ? $fields['originX'] : $fields['originX'] . 'px' ) : '50%';
		$y = $has_y ? ( self::position_value_has_unit( $fields['originY'] ) ? $fields['originY'] : $fields['originY'] . 'px' ) : '50%';

		return esc_attr( $x ) . ' ' . esc_attr( $y );
	}

	/**
	 * Non-responsive entry point: builds both `transform` and
	 * `transform-origin` declarations from one flat bucket of values. The
	 * responsive (per-breakpoint) path lives in
	 * Dynamic_Styles::get_transform_with_media() instead, since it needs to
	 * resolve values cumulatively across breakpoints (see that method's
	 * docblock for why `transform` can't be handled the same way as
	 * Position's independent longhand properties).
	 */
	public static function transform_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		$transform_value = self::build_transform_value( $args );
		if ( $transform_value ) {
			$css .= 'transform:' . $transform_value . ';';
		}

		$origin_value = self::build_transform_origin_value( $args );
		if ( $origin_value ) {
			$css .= 'transform-origin:' . $origin_value . ';';
		}

		return $css;
	}

	/**
	 * One table drives both `filter` and `backdrop-filter` — same CSS
	 * functions, applied to two different properties. The backdrop variant's
	 * field key is just the normal one prefixed with "backdrop" (see
	 * filter_field_key()), so both share this table instead of duplicating it.
	 * Mirrored in block-effects/inline-styles.js's FILTER_FUNCTIONS — keep
	 * both in sync.
	 */
	public static function filter_functions() {
		return array(
			array(
				'name'  => 'Blur',
				'cssFn' => 'blur',
				'unit'  => 'px',
			),
			array(
				'name'  => 'Brightness',
				'cssFn' => 'brightness',
				'unit'  => '%',
			),
			array(
				'name'  => 'Contrast',
				'cssFn' => 'contrast',
				'unit'  => '%',
			),
			array(
				'name'  => 'Saturate',
				'cssFn' => 'saturate',
				'unit'  => '%',
			),
			array(
				'name'  => 'Grayscale',
				'cssFn' => 'grayscale',
				'unit'  => '%',
			),
			array(
				'name'  => 'Sepia',
				'cssFn' => 'sepia',
				'unit'  => '%',
			),
			array(
				'name'  => 'HueRotate',
				'cssFn' => 'hue-rotate',
				'unit'  => 'deg',
			),
		);
	}

	public static function filter_field_key( $name, $is_backdrop ) {
		return $is_backdrop ? 'backdrop' . $name : lcfirst( $name );
	}

	/**
	 * Builds the `filter:`/`backdrop-filter:` function list from a single
	 * bucket of values. `$is_backdrop` selects which field-key variant
	 * (blur vs backdropBlur, etc) to read. Returns '' if none are set.
	 */
	public static function build_filter_functions_value( $fields, $is_backdrop ) {
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return '';
		}

		$functions = array();
		foreach ( self::filter_functions() as $fn ) {
			$field = self::filter_field_key( $fn['name'], $is_backdrop );
			if ( isset( $fields[ $field ] ) && '' !== $fields[ $field ] ) {
				$functions[] = $fn['cssFn'] . '(' . esc_attr( (float) $fields[ $field ] ) . $fn['unit'] . ')';
			}
		}

		return implode( ' ', $functions );
	}

	/** `opacity` is an independent longhand (unlike filter/backdrop-filter) — no cumulative merge needed, same treatment as Position's simple fields. */
	public static function build_opacity_value( $fields ) {
		if ( empty( $fields ) || ! is_array( $fields ) || ! isset( $fields['opacity'] ) || '' === $fields['opacity'] ) {
			return '';
		}
		return (string) (float) $fields['opacity'];
	}

	/** `mix-blend-mode`/`clip-path` — independent longhands like opacity, grouped with Effects since both pair visually with filter/backdrop-filter. */
	public static function build_blend_clip_value( $fields ) {
		$css = '';
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $css;
		}

		if ( ! empty( $fields['mixBlendMode'] ) ) {
			$css .= 'mix-blend-mode:' . esc_attr( $fields['mixBlendMode'] ) . ';';
		}

		if ( ! empty( $fields['clipPath'] ) ) {
			$css .= 'clip-path:' . esc_attr( $fields['clipPath'] ) . ';';
		}

		return $css;
	}

	/**
	 * Non-responsive entry point: builds `opacity`/`filter`/`backdrop-filter`
	 * declarations from one flat bucket of values. The responsive
	 * (per-breakpoint) path lives in Dynamic_Styles::get_effects_with_media()
	 * instead — same reasoning as transform_control() above, filter/
	 * backdrop-filter need cumulative cross-breakpoint resolution that this
	 * flat entry point doesn't.
	 */
	public static function effects_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		$opacity_value = self::build_opacity_value( $args );
		if ( '' !== $opacity_value ) {
			$css .= 'opacity:' . esc_attr( $opacity_value ) . ';';
		}

		$css .= self::build_blend_clip_value( $args );

		$filter_value = self::build_filter_functions_value( $args, false );
		if ( $filter_value ) {
			$css .= 'filter:' . $filter_value . ';';
		}

		$backdrop_value = self::build_filter_functions_value( $args, true );
		if ( $backdrop_value ) {
			$css .= 'backdrop-filter:' . $backdrop_value . ';-webkit-backdrop-filter:' . $backdrop_value . ';';
		}

		return $css;
	}

	/**
	 * Builds `background-color`/`color` declarations from a single bucket of
	 * values. Both are independent longhand properties — unlike Transform/
	 * Effects' composed `transform`/`filter`, setting one at a narrower
	 * breakpoint or state never erases the other — so, like
	 * position_control(), this needs no cumulative-merge handling; each
	 * breakpoint/state's own bucket is emitted directly.
	 */
	public static function colors_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		if ( ! empty( $args['backgroundColor'] ) ) {
			$css .= 'background-color:' . esc_attr( $args['backgroundColor'] ) . ';';
		}

		if ( ! empty( $args['color'] ) ) {
			$css .= 'color:' . esc_attr( $args['color'] ) . ';';
		}

		return $css;
	}

	/**
	 * `transition-duration`/`transition-timing-function`/`transition-delay`
	 * — independent longhand properties, same treatment as colors_control().
	 * Deliberately does not emit `transition-property`: its CSS initial value
	 * is already `all`, matching what an unset control should mean.
	 */
	public static function transition_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		if ( isset( $args['duration'] ) && '' !== $args['duration'] ) {
			$css .= 'transition-duration:' . (int) $args['duration'] . 'ms;';
		}

		if ( ! empty( $args['timingFunction'] ) ) {
			$easing = 'cubic-bezier' === $args['timingFunction'] && ! empty( $args['customEasing'] )
				? 'cubic-bezier(' . esc_attr( $args['customEasing'] ) . ')'
				: esc_attr( $args['timingFunction'] );
			$css   .= 'transition-timing-function:' . $easing . ';';
		}

		if ( isset( $args['delay'] ) && '' !== $args['delay'] ) {
			$css .= 'transition-delay:' . (int) $args['delay'] . 'ms;';
		}

		return $css;
	}

	/**
	 * `flex-grow`/`flex-shrink`/`flex-basis`/`align-self`/`order`/`cursor` —
	 * independent longhands grouped as one "layout" feature since none of
	 * them are visual effects (Effects' bucket) or positioning (Position's
	 * bucket). Same treatment as colors_control() — each field is emitted
	 * independently, no cumulative-merge handling needed.
	 */
	public static function layout_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		if ( isset( $args['flexGrow'] ) && '' !== $args['flexGrow'] ) {
			$css .= 'flex-grow:' . esc_attr( (float) $args['flexGrow'] ) . ';';
		}

		if ( isset( $args['flexShrink'] ) && '' !== $args['flexShrink'] ) {
			$css .= 'flex-shrink:' . esc_attr( (float) $args['flexShrink'] ) . ';';
		}

		if ( ! empty( $args['flexBasis'] ) ) {
			$css .= 'flex-basis:' . esc_attr( $args['flexBasis'] ) . ';';
		}

		if ( ! empty( $args['alignSelf'] ) ) {
			$css .= 'align-self:' . esc_attr( $args['alignSelf'] ) . ';';
		}

		if ( isset( $args['order'] ) && '' !== $args['order'] ) {
			$css .= 'order:' . esc_attr( (int) $args['order'] ) . ';';
		}

		if ( ! empty( $args['cursor'] ) ) {
			$css .= 'cursor:' . esc_attr( $args['cursor'] ) . ';';
		}

		return $css;
	}

	/**
	 * `flex-direction`/`flex-wrap`/`gap`/`align-items`/`justify-content`/
	 * `grid-template-columns`/`grid-auto-flow` — the CONTAINER side of
	 * flex/grid layout (how this block lays out its children), as opposed to
	 * layout_control() above, which is the CHILD side (how this block
	 * behaves as a flex/grid item of its own parent). `display` itself is
	 * emitted by position_control(), not here — the UI gates these fields on
	 * that same resolved value (see block-position/index.js), but
	 * storage/emission stay independent. flexDirection/flexWrap only apply
	 * under flex; gridTemplateColumns/gridAutoFlow only under grid;
	 * gap/alignItems/justifyContent apply to both.
	 */
	public static function container_layout_control( $args = array() ) {
		$css = '';
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $css;
		}

		if ( ! empty( $args['flexDirection'] ) ) {
			$css .= 'flex-direction:' . esc_attr( $args['flexDirection'] ) . ';';
		}

		if ( ! empty( $args['flexWrap'] ) ) {
			$css .= 'flex-wrap:' . esc_attr( $args['flexWrap'] ) . ';';
		}

		if ( ! empty( $args['gridTemplateColumns'] ) ) {
			$css .= 'grid-template-columns:' . esc_attr( $args['gridTemplateColumns'] ) . ';';
		}

		if ( ! empty( $args['gridAutoFlow'] ) ) {
			$css .= 'grid-auto-flow:' . esc_attr( $args['gridAutoFlow'] ) . ';';
		}

		if ( ! empty( $args['gap'] ) ) {
			$value = self::position_value_has_unit( $args['gap'] ) ? $args['gap'] : $args['gap'] . 'px';
			$css  .= 'gap:' . esc_attr( $value ) . ';';
		}

		if ( ! empty( $args['alignItems'] ) ) {
			$css .= 'align-items:' . esc_attr( $args['alignItems'] ) . ';';
		}

		if ( ! empty( $args['justifyContent'] ) ) {
			$css .= 'justify-content:' . esc_attr( $args['justifyContent'] ) . ';';
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
