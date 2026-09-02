<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Hover_Card{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));
	}

	public static function register_block( ) {
		if ( ! Helpers::is_pro_block_active() ) {
			return;
		}

		register_block_type( __DIR__, array(
			'render_callback' => array( __CLASS__, 'render_callback' )
		));
	}

	public static function render_callback( $attributes, $content ) {
		$allowed_animations = array(
			'slide-top-bottom',
			'slide-bottom-top',
			'slide-left-right',
			'slide-right-left',
			'zoom-in',
			'fade-in',
			'flip',
		);
		$animation = ! empty( $attributes['animation'] ) && in_array( $attributes['animation'], $allowed_animations, true )
			? $attributes['animation']
			: 'slide-top-bottom';

		$trigger_mode = ! empty( $attributes['triggerMode'] ) && 'tap' === $attributes['triggerMode']
			? 'tap'
			: 'hover';

		$allowed_easings = array( 'ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear' );
		$easing = ! empty( $attributes['animationEasing'] ) && in_array( $attributes['animationEasing'], $allowed_easings, true )
			? $attributes['animationEasing']
			: 'ease';

		$duration = isset( $attributes['animationDuration'] ) ? max( 0.1, min( 3, (float) $attributes['animationDuration'] ) ) : 0.5;
		$delay    = isset( $attributes['animationDelay'] ) ? max( 0, min( 2, (float) $attributes['animationDelay'] ) ) : 0;

		$class_names = array(
			'gutenify-hover-card-animation-' . $animation,
			'gutenify-hover-card-version-2',
		);

		if ( 'tap' === $trigger_mode ) {
			$class_names[] = 'gutenify-hover-card-trigger-tap';
		}

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => implode( ' ', $class_names ),
			'style' => sprintf(
				'--gutenify-hover-card-duration:%ss;--gutenify-hover-card-delay:%ss;--gutenify-hover-card-easing:%s;',
				$duration,
				$delay,
				$easing
			),
		) );
		$output = sprintf( '<div %s>', $wrapper_attributes );
		$output .= $content;
		$output .= '</div>';
		return $output;
	}
}

Hover_Card::init();
