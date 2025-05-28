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
				$css  .= str_replace( '--', '-', $prefix . $suffix ) . ':' . $value . ';';
			} else {
				foreach ( $args as $key => $val ) {
					if ( in_array( $key, array( 'top', 'bottom', 'left', 'right' ) ) ) {
						$value = self::value_has_unit( $val ) ? $val : $val . 'px';
						$css  .= $prefix . $key . $suffix . ':' . $value . ';';
					}
				}
			}
		}
		return $css;
	}

	public static function border_radius_control( $args, $prefix = 'border-', $suffix = '-radius' ) {
		$css = '';
		if ( ! empty( $args ) && ! is_array( $args ) ) {
			$css .= str_replace( '--', '-', $prefix . $suffix ) . ':' . $args . ';';
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
					$css .= $prefix . $areas[ $key ] . $suffix . ':' . $val . ';';
				}
			}
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

		$css = ' box-shadow:' . $horizontal . ' ' . $vertical . ' ' . $blur . ' ' . $spread . ' ' . $color . ' ' . $position . ';';

		return $css;
	}
}
