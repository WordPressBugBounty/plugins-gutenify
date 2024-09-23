<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Common_Scripts_Slider {
	public static $handle = 'gutenify-common-scripts-slider';
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_script' ) );
	}

	public static function enqueue_script() {
		wp_enqueue_script( self::$handle );
		wp_enqueue_style( self::$handle );
	}
}

Common_Scripts_Slider::init();
