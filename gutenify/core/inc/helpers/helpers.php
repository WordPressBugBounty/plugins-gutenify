<?php

/**
 * Prevent direct access to the file.
 *
 * Ensures this file is being loaded within the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Helpers file.
 *
 * @package Gutenify
 */
function gutenify_is_pro_active() {
	$constants                   = \gutenify\Helpers::plugin_constants();
	$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
	return apply_filters( $plugin_main_function_prefix . '_pro_activation_status', false );
}

/**
 * Settings.
 *
 * @return array
 */
function gutenify_settings() {
	$default_settings = array(
		'active_post_types'        => array( 'post', 'page' ),
		'skip_gutenburg_post_type' => apply_filters( 'gutenify_skip_gutenburg_post_type', array() ),
	);
	$current_settings = get_option( 'gutenify_settings', array() );
	$settings         = wp_parse_args( $current_settings, $default_settings );
	return $settings;
}

/**
 * Update settings.
 *
 * @param array $new_settings New settings.
 * @return array
 */
function gutenify_update_settings( $new_settings ) {
	$settings = gutenify_settings();
	$settings = wp_parse_args( $new_settings, $settings );
	update_option( 'gutenify_settings', $settings );
	return $settings;
}

add_filter(
	'block_editor_settings_all',
	function ( $args ) {
		$args['__experimentalFeatures']['typography']['fontFamilies']['theme'][] = array(
			'fontFamily' => '"Gilda Display", serif 1',
			'name'       => 'Gilda Display1',
			'slug'       => 'gilda-display1',
		);
		return $args;
	},
	9
);


function gutenify_get_block_asset_file_values( $path ) {
	$asset_path = $path;

	return file_exists( $asset_path )
		? include $asset_path
		: array(
			'dependencies' => array(),
			'version'      => GUTENIFY_VERSION,
		);
}

function gutenify_update_global_styles( $new_settings, $new_styles ) {
	// Get the user's global styles CPT id.
	$user_custom_post_type_id = WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
	$global_styles_controller = new WP_REST_Global_Styles_Controller();

	$update_request = new WP_REST_Request( 'PUT', '/wp/v2/global-styles/' );
	$update_request->set_param( 'id', $user_custom_post_type_id );
	$update_request->set_param( 'settings', $new_settings );
	$update_request->set_param( 'styles', $new_styles );

	$res = $global_styles_controller->update_item( $update_request );

	// Ideally the call to update_item would delete all of the appropriate transients and caches
	delete_transient( 'global_styles' );
	delete_transient( 'global_styles_' . get_stylesheet() );
	delete_transient( 'gutenberg_global_styles' );
	delete_transient( 'gutenberg_global_styles_' . get_stylesheet() );

	if ( class_exists( 'WP_Theme_JSON_Resolver_Gutenberg' ) ) {
		WP_Theme_JSON_Resolver_Gutenberg::clean_cached_data();
	}
}

/**
 * Returns the posts for an internal posts.
 *
 * @param array $posts Current posts.
 *
 * @return array Returns posts.
 */
function gutenify_get_post_info( $posts ) {

	$formatted_posts = array();

	foreach ( $posts as $post ) {

		$formatted_post = null;

		$formatted_post['thumbnailURL'] = get_the_post_thumbnail_url( $post );
		$formatted_post['date']         = esc_attr( get_the_date( 'c', $post ) );
		$formatted_post['dateReadable'] = esc_html( get_the_date( '', $post ) );
		$formatted_post['title']        = get_the_title( $post );
		$formatted_post['postLink']     = esc_url( get_permalink( $post ) );
		$formatted_post['ID']           = absint( $post->ID );
		$formatted_post['author_id']    = absint( $post->post_author );

		$post_excerpt = $post->post_excerpt;

		if ( ! ( $post_excerpt ) ) {

			$post_excerpt = $post->post_content;

		}

		$formatted_post['postExcerpt'] = $post_excerpt;

		$formatted_posts[] = $formatted_post;

	}

	return $formatted_posts;
}
