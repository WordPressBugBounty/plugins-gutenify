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
}
