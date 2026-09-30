<?php
/**
 * Rest API functions.
 *
 * @package Gutenify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gutenify_Rest
 */
class Gutenify_Rest extends WP_REST_Controller {
	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'gutenify/v';

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected $version = '1';

	/**
	 * Gutenify_Rest constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register rest routes.
	 */
	public function register_routes() {
		$namespace = $this->namespace . $this->version;

		// Get Templates.
		register_rest_route(
			$namespace,
			'/get_templates/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_templates' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Get template data.
		register_rest_route(
			$namespace,
			'/get_template_data/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_template_data' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Get settings data.
		register_rest_route(
			$namespace,
			'/get_settings/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Update Settings.
		register_rest_route(
			$namespace,
			'/update_settings/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Get Templates.
		register_rest_route(
			$namespace,
			'/get_site_options/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_site_options' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Update Site Optoins
		register_rest_route(
			$namespace,
			'/update_site_options/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_site_options' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Update Site Optoins
		register_rest_route(
			$namespace,
			'/update_global_styles/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_global_styles' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Get get_demo_categories.
		register_rest_route(
			$namespace,
			'/get_demo_categories/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_demo_categories' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

	}

	/**
	 * Get templates.
	 *
	 * @return mixed
	 */
	public function get_templates( WP_REST_Request $request ) {
		$constants                    = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_base_url         = $constants['plugin_main_base_url'];
		$plugin_main_version          = $constants['plugin_main_version'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$api_site  = defined( 'GUTENIFY_API_URL' ) ? GUTENIFY_API_URL : 'https://demo.gutenify.com/';
		$url       = $api_site . 'wp-json/gutenify-library/v1/get_library/';
		$templates = get_transient( 'gutenify_remote_templates', false );

		$data = $request->get_params();

		if ( ! empty( $data['reset'] ) && 'true' === $data['reset'] ) {
			$templates = array();
		}

		/*
		* Get remote templates.
		*/
		if ( ! $templates ) {
			$requested_templates = wp_remote_get(
				add_query_arg(
					array(
						'gutenify_version' => '2.19.3',
						'gutenify_pro' => false,
						'gutenify_pro_version' => null,
					),
					$url
				)
			);

			if ( ! is_wp_error( $requested_templates ) ) {
				$new_templates = wp_remote_retrieve_body( $requested_templates );
				$new_templates = json_decode( $new_templates, true );

				if ( $new_templates && isset( $new_templates['response'] ) && is_array( $new_templates['response'] ) ) {
					$templates = $new_templates['response'];
					set_transient( 'gutenify_remote_templates', $templates, DAY_IN_SECONDS );
				}
			}
		}

		// Remove Pro templates from array, cause for now there is no way to check if pro addon is activated.
		if ( $templates ) {
			foreach ( $templates as $k => $template ) {
				$is_pro = false;

				if ( isset( $template['types'] ) && is_array( $template['types'] ) ) {
					foreach ( $template['types'] as $type ) {
						$is_pro = $is_pro || 'pro' === $type['slug'];
					}
				}

				if ( $is_pro ) {
					unset( $templates[ $k ] );
				}
			}
		}

		/*
		 * Get user templates from db.
		 */

		// Stupid hack.
		// https://core.trac.wordpress.org/ticket/18408.
		global $post;
		$backup_global_post = $post;
		$local_templates    = array();

		$local_templates_query = new WP_Query(
			array(
				'post_type'      => $plugin_main_post_type_prefix . '_template',
				'posts_per_page' => -1,
				'showposts'      => -1,
				'paged'          => -1,
			)
		);

		while ( $local_templates_query->have_posts() ) {
			$local_templates_query->the_post();
			$db_template = get_post();

			$categories     = array();
			$category_terms = get_the_terms( $db_template->ID, $plugin_main_post_type_prefix . '_template_category' );

			if ( $category_terms ) {
				foreach ( $category_terms as $cat ) {
					$categories[] = array(
						'slug' => $cat->slug,
						'name' => $cat->name,
					);
				}
			}

			$image_id   = get_post_thumbnail_id( $db_template->ID );
			$image_data = wp_get_attachment_image_src( $image_id, 'large' );

			$local_templates[] = array(
				'id'               => $db_template->ID,
				'title'            => $db_template->post_title,
				'types'            => array(
					array(
						'slug' => 'local',
					),
				),
				'categories'       => empty( $categories ) ? false : $categories,
				'url'              => get_post_permalink( $db_template->ID ),
				'thumbnail'        => isset( $image_data[0] ) ? $image_data[0] : false,
				'thumbnail_width'  => isset( $image_data[1] ) ? $image_data[1] : false,
				'thumbnail_height' => isset( $image_data[2] ) ? $image_data[2] : false,
			);
		}

		// Restore the global $post as it was before custom WP_Query.
		// phpcs:ignore
		$post = $backup_global_post;

		/*
		 * Get theme templates.
		 */
		$theme_templates      = array();
		$theme_templates_data = array();

		foreach ( glob( get_template_directory() . '/gutenify/templates/*/content.php' ) as $template ) {
			$template_path = dirname( $template );
			$template_url  = get_template_directory_uri() . str_replace( get_template_directory(), '', $template_path );
			$slug          = basename( $template_path );

			$theme_templates_data[ $slug ] = array(
				'slug' => $slug,
				'path' => $template_path,
				'url'  => $template_url,
			);
		}

		// get child theme templates.
		if ( get_stylesheet_directory() !== get_template_directory() ) {
			foreach ( glob( get_stylesheet_directory() . '/gutenify/templates/*/content.php' ) as $template ) {
				$template_path = dirname( $template );
				$template_url  = get_stylesheet_directory_uri() . str_replace( get_stylesheet_directory(), '', $template_path );
				$slug          = basename( $template_path );

				$theme_templates_data[ $slug ] = array(
					'slug' => $slug,
					'path' => $template_path,
					'url'  => $template_url,
				);
			}
		}

		// natural sort.
		array_multisort( array_keys( $theme_templates_data ), SORT_NATURAL, $theme_templates_data );

		foreach ( $theme_templates_data as $template_data ) {
			$file_data = get_file_data(
				$template_data['path'] . '/content.php',
				array(
					'name'     => 'Name',
					'category' => 'Category',
					'source'   => 'Source',
				)
			);

			$thumbnail        = false;
			$thumbnail_width  = false;
			$thumbnail_height = false;

			if ( file_exists( $template_data['path'] . '/thumbnail.png' ) ) {
				$thumbnail = $template_data['url'] . '/thumbnail.png';

				list($thumbnail_width, $thumbnail_height) = getimagesize( $thumbnail );
			}

			if ( file_exists( $template_data['path'] . '/thumbnail.jpg' ) ) {
				$thumbnail = $template_data['url'] . '/thumbnail.jpg';
			}

			$theme_templates[] = array(
				'id'               => basename( $template_data['path'] ),
				'title'            => $file_data['name'],
				'types'            => array(
					array(
						'slug' => 'theme',
					),
				),
				'categories'       => isset( $file_data['category'] ) && $file_data['category'] ? array(
					array(
						'slug' => $file_data['category'],
						'name' => $file_data['category'],
					),
				) : false,
				'url'              => false,
				'thumbnail'        => $thumbnail,
				'thumbnail_width'  => $thumbnail_width,
				'thumbnail_height' => $thumbnail_height,
			);
		}

		// merge all available templates.
		$templates = ! empty( $templates ) ? $templates : array();
		$templates = array_merge( $templates, $local_templates, $theme_templates );

		if ( is_array( $templates ) ) {
			return $this->success( $templates );
		} else {
			return $this->error( 'no_templates', __( 'Templates not found.', 'gutenify' ) );
		}
	}

	/**
	 * Get templates.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function get_template_data( WP_REST_Request $request ) {
		$constants                    = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_base_url         = $constants['plugin_main_base_url'];
		$plugin_main_version          = $constants['plugin_main_version'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$api_site      = defined( 'GUTENIFY_API_URL' ) ? GUTENIFY_API_URL : 'https://demo.gutenify.com/';
		$url           = $api_site . 'wp-json/gutenify-library/v1/get_library_data/';
		$id            = $request->get_param( 'id' );
		$type          = $request->get_param( 'type' );
		$template_data = false;

		if ( 1 > absint( $id ) || empty( $type ) ) {
			return $this->error( 'no_template_data', __( 'Template data not found.', 'gutenify' ) );
		}

		$id   = absint( $id );
		$fast = ! empty( $request->get_param( 'fast' ) );

		switch ( $type ) {
			case 'remote':
				$cache_key     = 'gutenify_template_' . $type . '_' . $id;
				$template_data = get_transient( $cache_key, false );

				if ( ! $template_data ) {
					$url = add_query_arg(
						apply_filters(
							'gutenify_rest_template_data_url_args',
							array(
								'id'       => $id,
								'gutenify_version' => GUTENIFY_VERSION,
								'site_url' => site_url( '/' ),
							)
						),
						$url
					);

					$requested_template_data = wp_remote_get( $url );

					if ( ! is_wp_error( $requested_template_data ) ) {
						$new_template_data = wp_remote_retrieve_body( $requested_template_data );
						$new_template_data = json_decode( $new_template_data, true );

						if ( $new_template_data && isset( $new_template_data['response'] ) && is_array( $new_template_data['response'] ) ) {
							$template_data = $new_template_data['response'];

							if ( $fast ) {
								// Editor-insert fast path: return the raw
								// content immediately with remote image URLs
								// still in place, so the click isn't blocked
								// on every image's download. Deliberately
								// NOT cached — the client follows up with a
								// non-fast request (see 'needs_sideload'
								// below) that does the real sideload and
								// caches the result once it's actually done.
							} elseif ( ! empty( $template_data['content'] ) ) {
								$template_data['content'] = \gutenify\Helpers::sideload_remote_images( $template_data['content'], $api_site );
								set_transient( $cache_key, $template_data, DAY_IN_SECONDS );
							}
						}
					}
				}
				break;
			case 'local':
				$post = get_post( $id );

				if ( $post && $plugin_main_post_type_prefix . '_template' === $post->post_type && 'publish' === $post->post_status ) {
					$template_data = array(
						'id'      => $post->ID,
						'title'   => $post->post_title,
						'content' => $post->post_content,
					);
				}

				break;
		}

		if ( is_array( $template_data ) ) {
			// Tells the client whether this response still has remote image
			// URLs left to resolve — true for a 'fast' response, and also
			// true if a normal (non-fast) sideload pass didn't fully
			// succeed (e.g. one image's download failed). The editor uses
			// this to decide whether a background follow-up + block-patch
			// is needed at all.
			$template_data['needs_sideload'] = ! empty( $template_data['content'] )
				&& \gutenify\Helpers::has_remote_images( $template_data['content'], $api_site );

			return $this->success( $template_data );
		} else {
			return $this->error( 'no_template_data', __( 'Template data not found.', 'gutenify' ) );
		}
	}

	/**
	 * Get settings.
	 *
	 * @return mixed
	 */
	public function get_settings() {
		$settings = gutenify_settings();

		if ( is_array( $settings ) ) {
			return $this->success( $settings );
		} else {
			return $this->error( 'no_settings', __( 'Settings data not found.', 'gutenify' ) );
		}
	}

	/**
	 * Get edit options permissions.
	 *
	 * @return bool
	 */
	public function update_settings_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Update Settings.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function update_settings( WP_REST_Request $request ) {
		$new_settings = $request->get_param( 'settings' );

		if ( is_array( $new_settings ) ) {
			$current_settings = get_option( 'gutenify_settings', array() );
			update_option( 'gutenify_settings', array_merge( $current_settings, $new_settings ) );
		}

		return $this->success( true );
	}

	public function get_site_options() {
		$options = get_option( 'gutenify_site_options' );
		return $this->success( $options );
	}

	/**
	 * Update site options.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function update_site_options( WP_REST_Request $request ) {
		$options        = $request->get_params();
		$merged_options = array();
		if ( is_array( $options ) ) {
			$current_options = get_option( 'gutenify_site_options', array() );
			if ( empty( $current_options ) ) {
				$current_options = array();
			}
			$merged_options = array_merge( $current_options, $options );

			// $site_fonts = array();
			// if ( ! empty( $merged_options['styles']['siteFonts'] ) ) {
			// $site_settings = wp_get_global_settings();
			// $site_styles = wp_get_global_styles();
			// $default_font_families = ! empty( $site_settings['typography']['fontFamilies']['theme'] )? $site_settings['typography']['fontFamilies']['theme']  : array();
			// $default_font_families_slugs = wp_list_pluck( $default_font_families, 'slug' );
			// error_log( print_r( $default_font_families_slugs, true  ));
			// foreach(  $merged_options['styles']['siteFonts']  as $font ) {
			// if ( ! in_array( $font['value'], $default_font_families_slugs, true ) ) {
			// $site_fonts[] = array(
			// 'fontFamily' => $font['label'],
			// 'name' => $font['label'],
			// 'slug' => $font['value'],
			// );
			// }
			// }
			// $new_font_families = array_merge( $default_font_families, $site_fonts );
			// error_log( print_r( $new_font_families, true  ));
			// $site_settings['typography']['fontFamilies']['theme'] = $new_font_families;

			// gutenify_update_global_styles( $site_settings, $site_styles );
			// }

			// Ideally the call to update_item would delete all of the appropriate transients and caches
			delete_transient( 'global_styles' );
			delete_transient( 'global_styles_' . get_stylesheet() );
			delete_transient( 'gutenberg_global_styles' );
			delete_transient( 'gutenberg_global_styles_' . get_stylesheet() );

			if ( class_exists( 'wp_theme_json_resolver' ) ) {
				wp_theme_json_resolver::clean_cached_data();
			}
			update_option( 'gutenify_site_options', array_merge( $current_options, $options ) );
		}

		return $this->success( $merged_options );
	}

	/**
	 * Update site options.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function update_global_styles( WP_REST_Request $request ) {
		$options = (array) $request->get_params();

		if ( isset( $options['css'] ) ) {
			// Sanitize CSS at save-time: strip any HTML tags before storing.
			update_option( 'gutenify_global_style', wp_strip_all_tags( $options['css'] ) );
		}

		return $this->success( $options );
	}

	public function get_demo_categories( WP_REST_Request $request ) {
		$options         = (array) $request->get_params();
		$demo_categories = false;
		if ( empty( $options['force'] ) || 'false' === $options['force'] ) {
			$demo_categories = get_transient( 'gutenify_demo_categories', false );
		}
		if ( false === $demo_categories ) {
			$error = $this->error( 'error_demo_categories', __( 'Error listing demo', 'gutenify' ) );

			try {
				$url = 'https://demo.gutenify.com/wp-json/demo-api/v1/get_demo_categories';
				// 'https://api.github.com/repos/gutenify/demo-content/contents/themes'
				/** @var array|WP_Error $response */
				$response = wp_remote_get(
					$url,
					array(
						'timeout'     => 120,
						'httpversion' => '1.1',
						'headers'     => array(
							'Accept' => 'application/json',
						),
					)
				);
				if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
					if ( $body = wp_remote_retrieve_body( $response ) ) {
						$responseBody = json_decode( $body );
						$categories   = array();
						if ( ! empty( $responseBody ) && ! empty( $responseBody->response ) ) {
							$categories = $responseBody->response;
						}
						set_transient( 'gutenify_demo_categories', json_encode( $categories ), DAY_IN_SECONDS );
						return $this->success( $categories );
					}
					return $error;
				}
			} catch ( Exception $ex ) {
				return $error;
			}
		}
		return $this->success( json_decode( $demo_categories ) );
	}

	/**
	 * Success rest.
	 *
	 * @param mixed $response response data.
	 * @return mixed
	 */
	public function success( $response ) {
		return new WP_REST_Response(
			array(
				'success'  => true,
				'response' => $response,
			),
			200
		);
	}

	/**
	 * Error rest.
	 *
	 * @param mixed $code     error code.
	 * @param mixed $response response data.
	 * @return mixed
	 */
	public function error( $code, $response ) {
		return new WP_REST_Response(
			array(
				'error'      => true,
				'success'    => false,
				'error_code' => $code,
				'response'   => $response,
			),
			401
		);
	}
}
new Gutenify_Rest();
