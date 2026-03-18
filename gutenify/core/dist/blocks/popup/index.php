<?php
/**
 * Popup Block
 *
 * @package gutenify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Popup Block
 */
function gutenify_register_popup_block() {
	// Register the block.
	register_block_type( __DIR__ );
}
add_action( 'init', 'gutenify_register_popup_block' );

/**
 * Enqueue Magnific Popup Assets
 */
function gutenify_enqueue_popup_assets() {
	// Check if block is used on the page.
	if ( ! has_block( 'gutenify/popup' ) ) {
		return;
	}

	// Enqueue jQuery (magnific popup dependency).
	wp_enqueue_script( 'jquery' );

	// Get asset URLs using Helpers class
	$magnific_js_url  = gutenify\Helpers::core_base_url() . 'assets/js/lib/jquery.magnific-popup.min.js';
	$magnific_css_url = gutenify\Helpers::core_base_url() . 'assets/css/lib/magnific-popup.css';

	// Enqueue Magnific Popup JS.
	wp_enqueue_script(
		'gutenify-magnific-popup',
		$magnific_js_url,
		array( 'jquery' ),
		'1.1.0',
		true
	);

	// Enqueue Magnific Popup CSS.
	wp_enqueue_style(
		'gutenify-magnific-popup',
		$magnific_css_url,
		array(),
		'1.1.0'
	);
}
add_action( 'wp_enqueue_scripts', 'gutenify_enqueue_popup_assets' );
