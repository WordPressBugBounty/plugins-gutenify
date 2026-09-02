<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Hover_Card_Hover{
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
		$wrapper_attributes = get_block_wrapper_attributes( );
		$output = sprintf( '<div %s>', $wrapper_attributes );
		$output .= $content;
		$output .= '</div>';
		return $output;
	}
}

Hover_Card_Hover::init();
