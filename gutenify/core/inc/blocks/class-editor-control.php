<?php
/**
 * Editor control actions.
 *
 * Controls the use of the Gutenberg editor based on post type.
 *
 * @package Gutenify
 */

namespace gutenify;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Class Editor_Control
 *
 * Handles logic for enabling or disabling the Gutenberg editor for specific post types.
 */
class Editor_Control {

	/**
	 * Constructor registers action and filter hooks.
	 */
	public function __construct() {
		// Conditionally disable Gutenberg editor.
		add_action( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );

		// Register post types that should skip Gutenberg check.
		add_filter( 'gutenify_skip_gutenburg_post_type', array( $this, 'skip_gutenburg_post_type' ) );
	}

	/**
	 * Conditionally disables Gutenberg for post types not in the allowed list.
	 *
	 * @param bool   $is_enabled Whether Gutenberg is enabled.
	 * @param string $post_type  Current post type.
	 * @return bool              Modified Gutenberg enablement flag.
	 */
	public function disable_gutenberg( $is_enabled, $post_type ) {
		$filter_name    = 'gutenify_skip_gutenburg_post_type';
		$skip_gutenburg = apply_filters( $filter_name, array() );

		// Skip logic if post type is explicitly excluded.
		if ( ! in_array( $post_type, $skip_gutenburg, true ) ) {
			$settings = gutenify_settings();

			// Fetch allowed post types from settings.
			$active_post_types = ! empty( $settings['active_post_types'] ) ? $settings['active_post_types'] : array();

			// Disable Gutenberg if post type is not active.
			if ( ! in_array( $post_type, $active_post_types, true ) ) {
				return false;
			}
		}

		// Return the original Gutenberg flag if conditions aren't met.
		return $is_enabled;
	}

	/**
	 * Appends specific post types that should bypass Gutenberg editor control logic.
	 *
	 * @param array $post_types Post types already excluded.
	 * @return array            Updated list of excluded post types.
	 */
	public function skip_gutenburg_post_type( $post_types ) {
		// Add built-in and custom post types to skip list.
		$skip_types = array(
			'attachment',
			'wp_template',
			'wp_block',
			'gutenify_template',
		);
		return array_merge( $post_types, $skip_types );
	}
}

// Initialize the class.
new Editor_Control();
