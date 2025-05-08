<?php
/**
 * gutenify Global_Code Class
 *
 * This file contains the `Global_Code` class, which is responsible for injecting
 * custom code into the header, body, and footer sections of a WordPress site.
 * The custom code can be defined in the plugin settings and will be output
 * at the appropriate places in the frontend.
 *
 * The class hooks into the following WordPress actions:
 * - `wp_head`: Adds custom code to the <head> section of the site.
 * - `wp_body_open`: Adds custom code right after the opening <body> tag.
 * - `wp_footer`: Adds custom code before the closing </body> tag.
 *
 * The custom code is retrieved from plugin settings and displayed if available.
 *
 * Prevent direct access to this file.
 *
 * @package gutenify
 */

namespace gutenify;

/**
 * Prevent direct access to the file.
 *
 * Ensures this file is being loaded within the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Global_Code Class for injecting custom code in WordPress frontend.
 */
class Global_Code {

	/**
	 * Store settings globally for reuse.
	 *
	 * @var array
	 */
	private static $settings;

	/**
	 * Initializes settings and hooks for displaying code.
	 */
	public static function init() {
		self::$settings = gutenify_settings(); // Get plugin settings
		// Register actions for header, body, and footer code.
		add_action( 'wp_head', array( __CLASS__, 'output_header_code' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'output_body_open_code' ) );
		add_action( 'wp_footer', array( __CLASS__, 'output_footer_code' ) );
	}

	/**
	 * Output custom code in the <head> section.
	 */
	public static function output_header_code() {
		self::output_code( 'global_header_code' );
	}

	/**
	 * Output custom code after opening the <body> tag.
	 */
	public static function output_body_open_code() {
		self::output_code( 'global_body_open_code' );
	}

	/**
	 * Output custom code before closing the </body> tag.
	 */
	public static function output_footer_code() {
		self::output_code( 'global_footer_code' );
	}

	/**
	 * Helper function to echo code from settings if available.
	 *
	 * @param string $setting_key The key for the code setting.
	 */
	private static function output_code( $setting_key ) {
		if ( ! empty( self::$settings[ $setting_key ] ) ) {
			echo self::$settings[ $setting_key ];
		}
	}
}

// Instantiate the Global_Code class.
Global_Code::init();
