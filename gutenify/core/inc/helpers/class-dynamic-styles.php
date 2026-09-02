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
	 * @param array  $position           The stored `advancedStyling.position.normal` value (or a state bucket, when called from get_state_variants_css()).
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

	/**
	 * Generate `transform`/`transform-origin` CSS with media queries for
	 * desktop, tablet, mobile, and any block-level custom breakpoints.
	 *
	 * Unlike get_position_with_media() above, this CANNOT just pass each
	 * breakpoint's own bucket straight to Style_Helpers::build_transform_value()
	 * — `transform` and `transform-origin` are each a single CSS property
	 * combining several logical sub-values (translateX, rotate, scaleX, ...).
	 * If tablet only overrides `rotate`, emitting just `transform: rotate(45deg)`
	 * at that media query would silently erase desktop's translateX/scaleX/etc,
	 * since CSS never merges partial transform functions across rules the way
	 * it does independent longhand properties (position/top/z-index/...).
	 *
	 * So each emitted breakpoint carries the full CUMULATIVE resolved value —
	 * desktop's own values, overridden field-by-field by every narrower
	 * breakpoint up to and including this one. Mirrored in the JS side,
	 * block-transform/inline-styles.js — keep both in sync.
	 *
	 * @param string $selector           The CSS selector to apply the styles to.
	 * @param array  $transform          The stored `advancedStyling.transform.normal` value (or a state bucket, when called from get_state_variants_css()).
	 * @param array  $custom_breakpoints The block's `customBlockBreakpoints` attribute, if any.
	 * @return string                    The generated CSS string.
	 */
	public static function get_transform_with_media( $selector, $transform, $custom_breakpoints = array() ) {
		if ( empty( $transform ) || ! is_array( $transform ) ) {
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
			if ( ! empty( $transform[ $bp['id'] ] ) && is_array( $transform[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::transform_control( $transform );
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

		$transform_fields = array( 'translateX', 'translateY', 'rotate', 'scaleX', 'scaleY', 'skewX', 'skewY' );
		$origin_fields    = array( 'originX', 'originY' );

		$cumulative_transform = array();
		$cumulative_origin    = array();
		$css                  = '';

		foreach ( $breakpoints as $bp ) {
			$bucket = ! empty( $transform[ $bp['id'] ] ) && is_array( $transform[ $bp['id'] ] ) ? $transform[ $bp['id'] ] : array();

			$touched_transform = false;
			foreach ( $transform_fields as $field ) {
				if ( isset( $bucket[ $field ] ) && '' !== $bucket[ $field ] ) {
					$cumulative_transform[ $field ] = $bucket[ $field ];
					$touched_transform              = true;
				}
			}

			$touched_origin = false;
			foreach ( $origin_fields as $field ) {
				if ( isset( $bucket[ $field ] ) && '' !== $bucket[ $field ] ) {
					$cumulative_origin[ $field ] = $bucket[ $field ];
					$touched_origin              = true;
				}
			}

			$declarations = '';
			if ( $touched_transform ) {
				$value = \gutenify\Style_Helpers::build_transform_value( $cumulative_transform );
				if ( $value ) {
					$declarations .= 'transform:' . $value . ';';
				}
			}
			if ( $touched_origin ) {
				$value = \gutenify\Style_Helpers::build_transform_origin_value( $cumulative_origin );
				if ( $value ) {
					$declarations .= 'transform-origin:' . $value . ';';
				}
			}

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

	/**
	 * Generate `opacity`/`filter`/`backdrop-filter` CSS with media queries for
	 * desktop, tablet, mobile, and any block-level custom breakpoints.
	 *
	 * `filter` and `backdrop-filter` need the same cumulative-resolution
	 * treatment as get_transform_with_media() above (each is a single CSS
	 * property combining several functions — a narrower breakpoint that only
	 * sets one function would otherwise erase the others inherited from a
	 * wider breakpoint). `opacity` doesn't need this — it's an independent
	 * longhand, handled the same way as Position's simple fields. Mirrored in
	 * the JS side, block-effects/inline-styles.js — keep both in sync.
	 *
	 * @param string $selector           The CSS selector to apply the styles to.
	 * @param array  $effects            The stored `advancedStyling.effects.normal` value (or a state bucket, when called from get_state_variants_css()).
	 * @param array  $custom_breakpoints The block's `customBlockBreakpoints` attribute, if any.
	 * @return string                    The generated CSS string.
	 */
	public static function get_effects_with_media( $selector, $effects, $custom_breakpoints = array() ) {
		if ( empty( $effects ) || ! is_array( $effects ) ) {
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
			if ( ! empty( $effects[ $bp['id'] ] ) && is_array( $effects[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::effects_control( $effects );
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

		$filter_fields   = array();
		$backdrop_fields = array();
		foreach ( \gutenify\Style_Helpers::filter_functions() as $fn ) {
			$filter_fields[]   = \gutenify\Style_Helpers::filter_field_key( $fn['name'], false );
			$backdrop_fields[] = \gutenify\Style_Helpers::filter_field_key( $fn['name'], true );
		}

		$cumulative_filter   = array();
		$cumulative_backdrop = array();
		$css                 = '';

		foreach ( $breakpoints as $bp ) {
			$bucket = ! empty( $effects[ $bp['id'] ] ) && is_array( $effects[ $bp['id'] ] ) ? $effects[ $bp['id'] ] : array();

			$declarations  = '';
			$opacity_value = \gutenify\Style_Helpers::build_opacity_value( $bucket );
			if ( '' !== $opacity_value ) {
				$declarations .= 'opacity:' . esc_attr( $opacity_value ) . ';';
			}

			$declarations .= \gutenify\Style_Helpers::build_blend_clip_value( $bucket );

			$touched_filter = false;
			foreach ( $filter_fields as $field ) {
				if ( isset( $bucket[ $field ] ) && '' !== $bucket[ $field ] ) {
					$cumulative_filter[ $field ] = $bucket[ $field ];
					$touched_filter              = true;
				}
			}

			$touched_backdrop = false;
			foreach ( $backdrop_fields as $field ) {
				if ( isset( $bucket[ $field ] ) && '' !== $bucket[ $field ] ) {
					$cumulative_backdrop[ $field ] = $bucket[ $field ];
					$touched_backdrop              = true;
				}
			}

			if ( $touched_filter ) {
				$value = \gutenify\Style_Helpers::build_filter_functions_value( $cumulative_filter, false );
				if ( $value ) {
					$declarations .= 'filter:' . $value . ';';
				}
			}
			if ( $touched_backdrop ) {
				$value = \gutenify\Style_Helpers::build_filter_functions_value( $cumulative_backdrop, true );
				if ( $value ) {
					$declarations .= 'backdrop-filter:' . $value . ';-webkit-backdrop-filter:' . $value . ';';
				}
			}

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

	/**
	 * Generate `background-color`/`color` CSS with media queries for desktop,
	 * tablet, mobile, and any block-level custom breakpoints.
	 *
	 * Both are independent longhand properties, so — unlike
	 * get_transform_with_media()/get_effects_with_media() — this needs no
	 * cumulative-resolution handling; each breakpoint's own bucket is passed
	 * straight to Style_Helpers::colors_control(). Mirrored in the JS side,
	 * block-colors/inline-styles.js — keep both in sync.
	 *
	 * @param string $selector           The CSS selector to apply the styles to.
	 * @param array  $colors             The stored `advancedStyling.colors.normal` value (or a state bucket, when called from get_state_variants_css()).
	 * @param array  $custom_breakpoints The block's `customBlockBreakpoints` attribute, if any.
	 * @return string                    The generated CSS string.
	 */
	public static function get_colors_with_media( $selector, $colors, $custom_breakpoints = array() ) {
		if ( empty( $colors ) || ! is_array( $colors ) ) {
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
			if ( ! empty( $colors[ $bp['id'] ] ) && is_array( $colors[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::colors_control( $colors );
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
			$values = ! empty( $colors[ $bp['id'] ] ) && is_array( $colors[ $bp['id'] ] ) ? $colors[ $bp['id'] ] : array();

			$declarations = \gutenify\Style_Helpers::colors_control( $values );
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

	/**
	 * Generates dynamic `transition-duration`/`transition-timing-function`/
	 * `transition-delay` CSS for the block, including any block-level custom
	 * breakpoints. Normal-only — see block-transition/inline-styles.js's
	 * docblock for why Transition has no Hover/Active/Focus variant.
	 *
	 * @param string $selector           CSS selector.
	 * @param array  $transition         `advancedStyling.transition.normal` bucket.
	 * @param array  $custom_breakpoints Optional block-level custom breakpoints.
	 * @return string CSS styles.
	 */
	public static function get_transition_with_media( $selector, $transition, $custom_breakpoints = array() ) {
		if ( empty( $transition ) || ! is_array( $transition ) ) {
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
			if ( ! empty( $transition[ $bp['id'] ] ) && is_array( $transition[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::transition_control( $transition );
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
			$values = ! empty( $transition[ $bp['id'] ] ) && is_array( $transition[ $bp['id'] ] ) ? $transition[ $bp['id'] ] : array();

			$declarations = \gutenify\Style_Helpers::transition_control( $values );
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

	/**
	 * Generates dynamic `box-shadow` CSS for the block, including any
	 * block-level custom breakpoints. Unlike colors/transition's independent
	 * longhand fields, `box-shadow` is a single CSS shorthand, so this only
	 * calls `Style_Helpers::custom_box_shadow_control()` (which has no
	 * empty-guard of its own and always fills in defaults for whichever
	 * sub-fields are unset) when a given breakpoint's bucket has at least one
	 * sub-field set — matching the guard already used at every existing
	 * `custom_box_shadow_control()` call site (post-carousel, post-list).
	 *
	 * @param string $selector           CSS selector.
	 * @param array  $box_shadow         `advancedStyling.boxShadow.<state>` bucket.
	 * @param array  $custom_breakpoints Optional block-level custom breakpoints.
	 * @return string CSS styles.
	 */
	public static function get_box_shadow_with_media( $selector, $box_shadow, $custom_breakpoints = array() ) {
		if ( empty( $box_shadow ) || ! is_array( $box_shadow ) ) {
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
			if ( ! empty( $box_shadow[ $bp['id'] ] ) && is_array( $box_shadow[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			if ( empty( $box_shadow ) ) {
				return '';
			}
			$declarations = \gutenify\Style_Helpers::custom_box_shadow_control( $box_shadow );
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
			$values = ! empty( $box_shadow[ $bp['id'] ] ) && is_array( $box_shadow[ $bp['id'] ] ) ? $box_shadow[ $bp['id'] ] : array();
			if ( empty( $values ) ) {
				continue;
			}

			$declarations = \gutenify\Style_Helpers::custom_box_shadow_control( $values );
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

	/**
	 * Generates dynamic `flex-grow`/`flex-shrink`/`flex-basis`/`align-self`/
	 * `order`/`cursor` CSS for the block, including any block-level custom
	 * breakpoints. Normal-only — these are static layout/interaction
	 * concerns, not state-driven effects, so there's no Hover/Active/Focus
	 * bucket to read (same reasoning as get_transition_with_media()).
	 *
	 * @param string $selector           CSS selector.
	 * @param array  $layout             `advancedStyling.layout.normal` bucket.
	 * @param array  $custom_breakpoints Optional block-level custom breakpoints.
	 * @return string CSS styles.
	 */
	public static function get_layout_with_media( $selector, $layout, $custom_breakpoints = array() ) {
		if ( empty( $layout ) || ! is_array( $layout ) ) {
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
			if ( ! empty( $layout[ $bp['id'] ] ) && is_array( $layout[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::layout_control( $layout );
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
			$values = ! empty( $layout[ $bp['id'] ] ) && is_array( $layout[ $bp['id'] ] ) ? $layout[ $bp['id'] ] : array();

			$declarations = \gutenify\Style_Helpers::layout_control( $values );
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

	/**
	 * Generates `flex-direction`/`flex-wrap`/`gap`/`align-items`/
	 * `justify-content` CSS with media queries for desktop, tablet, mobile,
	 * and any block-level custom breakpoints. Independent longhands, same
	 * simple (non-cumulative) treatment as get_position_with_media()/
	 * get_box_shadow_with_media() — state-crossed (called via
	 * get_state_variants_css() from container_layout_style() below), unlike
	 * get_layout_with_media() above which is Normal-only.
	 *
	 * @param string $selector           The CSS selector to apply the styles to.
	 * @param array  $container_layout   The stored `advancedStyling.containerLayout.normal` value (or a state bucket, when called from get_state_variants_css()).
	 * @param array  $custom_breakpoints The block's `customBlockBreakpoints` attribute, if any.
	 * @return string                    The generated CSS string.
	 */
	public static function get_container_layout_with_media( $selector, $container_layout, $custom_breakpoints = array() ) {
		if ( empty( $container_layout ) || ! is_array( $container_layout ) ) {
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
			if ( ! empty( $container_layout[ $bp['id'] ] ) && is_array( $container_layout[ $bp['id'] ] ) ) {
				$is_responsive = true;
				break;
			}
		}

		if ( ! $is_responsive ) {
			$declarations = \gutenify\Style_Helpers::container_layout_control( $container_layout );
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
			$values = ! empty( $container_layout[ $bp['id'] ] ) && is_array( $container_layout[ $bp['id'] ] ) ? $container_layout[ $bp['id'] ] : array();

			$declarations = \gutenify\Style_Helpers::container_layout_control( $values );
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

	/**
	 * Generates Hover/Active/Focus-within CSS for one feature (position,
	 * transform, or effects) — the frontend mirror of the JS side's state
	 * handling in block-position/index.js (see that file's docblock for the
	 * full reasoning). States are crossed WITH the breakpoint axis — each
	 * state bucket can itself be responsive (desktop/tablet/mobile) — and
	 * "Focus" maps to `:focus-within` rather than literal `:focus`.
	 *
	 * Each state bucket is passed straight to `$with_media_callback`
	 * (Dynamic_Styles::get_position_with_media()/get_transform_with_media()/
	 * get_effects_with_media()) — the SAME function used for Normal, since it
	 * already shape-detects flat vs. responsive and handles either
	 * correctly; a state bucket that happens to be flat (no per-breakpoint
	 * override set) takes that function's flat branch automatically.
	 *
	 * For Transform/Effects specifically, this emits each state's own
	 * `transform`/`filter`/`backdrop-filter` value literally — it does NOT
	 * compose with Normal's value the way independent Position fields do
	 * (a Hover-only Scale fully replaces a Normal Translate while hovering,
	 * rather than combining with it) — documented limitation, not a bug;
	 * see the JS-side comment for what fixing it properly would require.
	 *
	 * @param string   $selector            The block's base CSS selector (e.g. `.gtfy-XXXX`).
	 * @param array    $block_attrs         `$block['attrs']` from render_block.
	 * @param array    $instance_attrs      `$instance->attributes` from render_block.
	 * @param string   $feature_key         'position', 'transform', or 'effects' — the base attribute key; state buckets are this plus 'Hover'/'Active'/'Focus'.
	 * @param array    $custom_breakpoints  The block's `customBlockBreakpoints` attribute, if any.
	 * @param callable $with_media_callback get_position_with_media()/get_transform_with_media()/get_effects_with_media(), called as ($selector, $bucket, $custom_breakpoints).
	 * @return string                       The generated CSS string.
	 */
	public static function get_state_variants_css( $selector, $block_attrs, $instance_attrs, $feature_key, $custom_breakpoints, $with_media_callback ) {
		$states = array(
			'hover'       => ':hover',
			'active'      => ':active',
			'focusWithin' => ':focus-within',
		);

		$css = '';

		foreach ( $states as $state_key => $pseudo ) {
			$bucket = ! empty( $instance_attrs['advancedStyling'][ $feature_key ][ $state_key ] ) ? $instance_attrs['advancedStyling'][ $feature_key ][ $state_key ] : array();
			if ( ! empty( $block_attrs['advancedStyling'][ $feature_key ][ $state_key ] ) ) {
				$bucket = wp_parse_args( $block_attrs['advancedStyling'][ $feature_key ][ $state_key ], $bucket );
			}

			if ( empty( $bucket ) ) {
				continue;
			}

			$css .= call_user_func( $with_media_callback, $selector . $pseudo, $bucket, $custom_breakpoints );
		}

		return $css;
	}
}
