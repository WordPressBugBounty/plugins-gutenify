<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Dynamic_Styles
{

	public static function get_spacing_with_media( $selector, $styles, $prefix = 'margin-' ) {
		$style_chunk = '';

		if ( ! empty( $styles['desktop'] ) ) {
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['desktop'], $prefix );
			$style_chunk .= '}';
		}

		if ( ! empty( $styles['tablet'] ) ) {
			$style_chunk .= '@media only screen and (max-width: '.\gutenify\Style_Helpers::$tablet_max_width.') {';
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['tablet'], $prefix );
			$style_chunk .= '}';
			$style_chunk .= '}';
		}

		if ( ! empty( $styles['mobile'] ) ) {
			$style_chunk .= '@media only screen and (max-width: '.\gutenify\Style_Helpers::$mobile_max_width.') {';
			$style_chunk .= $selector . '{';
			$style_chunk .= \gutenify\Style_Helpers::box_control( $styles['mobile'], $prefix );
			$style_chunk .= '}';
			$style_chunk .= '}';
		}
		return $style_chunk;
	}
}
