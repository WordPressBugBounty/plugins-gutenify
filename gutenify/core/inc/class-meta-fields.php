<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Meta_Fields {
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_meta_fields' ) );
	}

	public static function register_meta_fields(  ){
		$post_types = apply_filters( 'gutenify_active_post_types', array( 'post', 'page', 'wp_template', 'wp_template_part', 'template-kits' ) );
		if ( ! empty( $post_types ) ) {
			$meta_fields['gutenify_custom_css'] = array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				"default" => "",
				// 'auth_callback' => function () {
				// 	return current_user_can( 'edit_posts' );
				// },
				'auth_callback' => '__return_true',
			);

			$meta_fields['gutenify_custom_css_data'] = array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			);

			foreach ( $post_types as $post_type ) {
				if ( ! empty( $meta_fields ) ) {
					foreach ( $meta_fields as $meta_key => $meta_args ) {
						register_post_meta( $post_type, $meta_key, $meta_args );
					}
				}
			}
		}
	}
}

new Meta_Fields();
