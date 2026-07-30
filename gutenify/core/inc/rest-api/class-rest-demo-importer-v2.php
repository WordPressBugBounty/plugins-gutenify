<?php
namespace gutenify;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rest_Demo_Importer_V2 {
	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'add_endpoint' ) );
	}

	/**
	 * Add API Endpoints.
	 *
	 * @return void
	 */
	public static function add_endpoint() {
		$constants                    = Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_base_url         = $constants['plugin_main_base_url'];
		$plugin_main_version          = $constants['plugin_main_version'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/import',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'import' ),
				'permission_callback' => array( __CLASS__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/demos',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_demos' ),
				'permission_callback' => array( __CLASS__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/demo-content',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_demo_content' ),
				'permission_callback' => array( __CLASS__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/activate-theme',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'activate_theme' ),
				'permission_callback' => array( __CLASS__, 'update_settings_permission' ),
			)
		);

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/verify-theme-installation',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'verify_theme_installation' ),
				'permission_callback' => array( __CLASS__, 'update_settings_permission' ),
			)
		);
	}

	/**
	 * Get edit options permissions.
	 *
	 * @return bool
	 */
	public static function update_settings_permission() {
		return current_user_can( 'manage_options' );
	}

	public static function import( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$type   = ! empty( $params['type'] ) ? $params['type'] : '';

		if ( empty( $type ) ) {
			wp_send_json(
				array(
					'message' => 'error',
					'reason'  => 'missing required param: type',
				),
				400
			);
		}

		// Sentinel — left as null if no branch matches the type, so we can
		// tell "unknown type" apart from "handler legitimately returned empty".
		$response_data = null;

		if ( in_array( $type, array( 'pages', 'posts', 'navigations', 'plugin_templates' ), true ) ) {
			$response      = self::import_posts( $req );
			$response_data = ! empty( $params['data'] ) ? $response : array( 'success' => true );
		} elseif ( in_array( $type, array( 'template_parts', 'templates' ), true ) ) {
			$response      = self::import_templates( $req );
			$response_data = ! empty( $params['data'] ) ? $response : array( 'success' => true );
		} elseif ( 'plugin_settings' === $type ) {
			$response_data = self::import_plugin_settings( $req );
		} elseif ( 'set_home' === $type || 'wp_settings' === $type ) {
			$response_data = self::import_set_home( $req );
		} elseif ( 'global_styles_settings' === $type ) {
			$response_data = self::import_global_styles_settings( $req );
		} elseif ( 'global_styles' === $type ) {
			$response_data = self::import_global_styles( $req );
		} elseif ( 'global_settings' === $type ) {
			$response_data = self::import_global_settings( $req );
		}

		if ( null === $response_data ) {
			wp_send_json(
				array(
					'message' => 'error',
					'reason'  => 'unknown import type: ' . $type,
				),
				400
			);
		}

		// A handler returning false / [] / 0 means "nothing to do for this
		// step on this site" (e.g. set_home had no matching imported page).
		// That is a successful no-op, not an error — normalize so the JS
		// importer can continue to the next step.
		if ( false === $response_data || 0 === $response_data || array() === $response_data ) {
			$response_data = array(
				'success' => true,
				'noop'    => true,
				'type'    => $type,
			);
		}

		wp_send_json( $response_data );
	}

	public static function import_posts( \WP_REST_Request $req ) {

		$constants                    = Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$types['posts']            = 'post';
		$types['pages']            = 'page';
		$types['navigations']      = 'wp_navigation';
		$types['plugin_templates'] = $plugin_main_post_type_prefix . '_template';

		$params        = (array) $req->get_params();
		$created_posts = array();
		if ( ! empty( $params['data'] ) ) {
			$demo_posts = $params['data'];
			foreach ( $demo_posts as $demo_post ) {
				$demo_post                         = (array) $demo_post;
				$demo_post['theme']                = ! empty( $params['theme'] ) ? $params['theme'] : '';
				$post_id                           = self::create_post( $demo_post, $types[ $params['type'] ] );
				$created_posts[ $demo_post['ID'] ] = ( ! is_wp_error( $post_id ) ) ? $post_id : false;
			}
		}
		return $created_posts;
	}

	private static function import_set_home( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		if ( ! empty( $params['data']['page_on_front'] ) && ! empty( $params['importedData']['pages'][ $params['data']['page_on_front'] ] ) ) {
			$page_id = $params['importedData']['pages'][ $params['data']['page_on_front'] ];
			update_option( 'page_on_front', $page_id );
			update_option( 'show_on_front', 'page' );
			return true;
		}
		return false;
	}

	private static function import_plugin_settings( \WP_REST_Request $req ) {

		$constants                    = Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$params = (array) $req->get_params();
		if ( ! empty( $params['data']['plugin_settings'] ) ) {
			gutenify_update_settings( $params['data']['plugin_settings'] );
		}
		if ( ! empty( $params['data']['plugin_site_options'] ) ) {
			$options         = (array) $params['data']['plugin_site_options'];
			$current_options = get_option( $plugin_main_function_prefix . '_site_options', array() );
			$current_options = ! empty( $current_options ) ? $current_options : array();
			$merged_options  = array_merge( $current_options, $options );
			update_option( $plugin_main_function_prefix . '_site_options', $merged_options );
		}

		if ( ! empty( $params['data']['plugin_admin_global_style'] ) ) {
			update_option( $plugin_main_function_prefix . '_admin_global_style', wp_strip_all_tags( $params['data']['plugin_admin_global_style'] ) );
		}

		if ( ! empty( $params['data']['plugin_global_style'] ) ) {
			update_option( $plugin_main_function_prefix . '_global_style', wp_strip_all_tags( $params['data']['plugin_global_style'] ) );
		}
		return true;
	}

	public static function import_global_styles_settings( \WP_REST_Request $req ) {
		$post_id   = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		$post_data = false;
		$params    = (array) $req->get_params();
		if ( ! empty( $params['data'] ) ) {
			$data = self::normalize_request_data( $params['data'] );

			// Move the demo's "theme" fontFamilies into "custom" so the
			// font references in the imported styles still resolve. We
			// can't keep them in the "theme" bucket because that gets
			// populated at runtime from the active theme's theme.json.
			if ( ! empty( $data['settings'] ) ) {
				$data['settings'] = self::merge_demo_theme_fonts_into_custom( $data['settings'] );
			}
			$settings = ! empty( $data['settings'] ) ? $data['settings'] : array();
			$styles   = ! empty( $data['styles'] ) ? $data['styles'] : array();
			gutenify_update_global_styles( $settings, $styles );
		}

		return true;
	}

	/**
	 * Normalizes the `data` field of an import request so handlers can treat
	 * it uniformly as a PHP array regardless of how the demo source
	 * serialized it.
	 *
	 * - If the demo ships the payload as a JSON-encoded string, we decode it.
	 * - If it ships as a plain object/array, we cast through array.
	 * - Returns an empty array on null/empty so callers can short-circuit
	 *   via `! empty(...)` safely.
	 */
	private static function normalize_request_data( $raw ) {
		if ( null === $raw || '' === $raw ) {
			return array();
		}
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
				return array();
			}
			return (array) $decoded;
		}
		return (array) $raw;
	}

	/**
	 * Converts the demo's `settings.typography.fontFamilies.theme` entries
	 * into `settings.typography.fontFamilies.custom` entries, deduping by
	 * slug against any custom fonts already in the payload.
	 *
	 * The `theme` bucket is owned by the active theme's theme.json at
	 * runtime, so anything we write there gets clobbered. Moving the demo's
	 * theme-registered fonts into `custom` preserves them as user-defined
	 * fonts, which makes the imported style references (e.g. `var(--wp--
	 * preset--font-family--inter)`) still resolve.
	 *
	 * Note: actual font FILES (woff2, etc.) are not transferred. If a demo
	 * font's `fontFace` entries reference files hosted on the demo site,
	 * those URLs will still need to be reachable from the user's site.
	 */
	private static function merge_demo_theme_fonts_into_custom( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}
		$theme_fonts = isset( $settings['typography']['fontFamilies']['theme'] )
			? $settings['typography']['fontFamilies']['theme']
			: null;
		if ( empty( $theme_fonts ) ) {
			return $settings;
		}

		$custom_fonts = isset( $settings['typography']['fontFamilies']['custom'] )
			? (array) $settings['typography']['fontFamilies']['custom']
			: array();

		// Slugs already in custom take precedence — don't duplicate them.
		$seen_slugs = array();
		foreach ( $custom_fonts as $font ) {
			$font = (array) $font;
			if ( ! empty( $font['slug'] ) ) {
				$seen_slugs[] = $font['slug'];
			}
		}

		foreach ( (array) $theme_fonts as $font ) {
			$font = (array) $font;
			$slug = ! empty( $font['slug'] ) ? $font['slug'] : '';
			if ( empty( $slug ) || in_array( $slug, $seen_slugs, true ) ) {
				continue;
			}
			$custom_fonts[] = $font;
			$seen_slugs[]   = $slug;
		}

		unset( $settings['typography']['fontFamilies']['theme'] );
		if ( ! empty( $custom_fonts ) ) {
			$settings['typography']['fontFamilies']['custom'] = array_values( $custom_fonts );
		}
		return $settings;
	}

	public static function import_global_styles( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		if ( empty( $params['data'] ) ) {
			return true;
		}

		$styles = self::normalize_request_data( $params['data'] );

		// If the demo ships `global_styles` as a wrapped `{settings, styles}`
		// envelope (same shape as global_styles_settings), unwrap so we only
		// keep the inner styles subtree here.
		if ( isset( $styles['styles'] ) && ! isset( $styles[0] ) ) {
			$styles = (array) $styles['styles'];
		}

		// Preserve the user's existing settings so we only update the
		// `styles` subtree of the user-global-styles post. Going through the
		// REST controller ensures WP_Theme_JSON_Resolver's static + object
		// caches are invalidated — direct wp_update_post() leaves them
		// stale and the site keeps rendering the old resolved theme.json.
		$existing_settings = self::get_user_global_styles_field( 'settings' );

		gutenify_update_global_styles( $existing_settings, $styles );
		return true;
	}

	/**
	 * Reads the named subtree (`settings` or `styles`) out of the current
	 * user_global_styles post's JSON content. Used so the per-subtree
	 * handlers (import_global_styles, import_global_settings) can hand the
	 * untouched complement back to the REST controller, keeping the unwritten
	 * subtree intact.
	 */
	private static function get_user_global_styles_field( $field ) {
		$post_id = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		if ( empty( $post_id ) ) {
			return array();
		}
		$post = get_post( $post_id, ARRAY_A );
		if ( empty( $post['post_content'] ) ) {
			return array();
		}
		$decoded = json_decode( $post['post_content'], true );
		if ( ! is_array( $decoded ) || empty( $decoded[ $field ] ) ) {
			return array();
		}
		return (array) $decoded[ $field ];
	}

	/**
	 * Replaces the `settings` subtree of the user global-styles post with the
	 * demo's settings payload. Mirrors import_global_styles() but for the
	 * `settings` key instead of `styles`. Top-level replace, NOT a deep merge —
	 * the demo's settings overwrite the user's existing settings wholesale.
	 *
	 * Demo `typography.fontFamilies.theme` entries are moved into `custom`
	 * via merge_demo_theme_fonts_into_custom() so they survive (the `theme`
	 * bucket is owned by the active theme at runtime).
	 */
	public static function import_global_settings( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		if ( empty( $params['data'] ) ) {
			return true;
		}

		$settings = self::normalize_request_data( $params['data'] );

		// If the demo ships `global_settings` as a wrapped `{settings,
		// styles}` envelope, unwrap to just the settings subtree.
		if ( isset( $settings['settings'] ) && ! isset( $settings[0] ) ) {
			$settings = (array) $settings['settings'];
		}
		$settings = self::merge_demo_theme_fonts_into_custom( $settings );

		// Preserve the user's existing styles so we only update the
		// `settings` subtree. See note in import_global_styles() for why
		// we go through the REST controller path.
		$existing_styles = self::get_user_global_styles_field( 'styles' );

		gutenify_update_global_styles( $settings, $existing_styles );
		return true;
	}

	private static function replace_navigation( $navs, $content ) {
		if ( ! empty( $navs ) ) {
			$theme = wp_get_theme()->get_stylesheet();
			foreach ( $navs as $old_key => $id ) {
				// replace id.
				$pattern = '/<!--[ \t]+wp:navigation[ \t]+{.*"ref":' . absint( $old_key ) . ',/m';
				$content = preg_replace( $pattern, '<!-- wp:navigation {"ref":' . absint( $id ) . ',', $content );
			}
		}

		return $content;
	}

	private static function replace_theme_name( $old_theme, $content ) {
		$theme   = wp_get_theme()->get_stylesheet();
		$pattern = '/"theme":"' . $old_theme . '"/m';
		$content = preg_replace( $pattern, '"theme":"' . $theme . '"', $content );

		return $content;
	}

	public static function import_templates( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$data   = array();
		if ( ! empty( $params['data'] ) ) {
			$posts = $params['data'];
			foreach ( $posts as $post ) {
				$post    = (array) $post;
				$content = $post['content'];
				if ( ! empty( $params['importedData']['navigations'] ) ) {
					$content = self::replace_navigation( $params['importedData']['navigations'], $post['content'] );
				}

				$post_data = array();

				if ( ! empty( $post['theme'] ) ) {
					$content = self::replace_theme_name( $post['theme'], $content );
				}
				$post_data['theme']        = ! empty( $params['theme'] ) ? $params['theme'] : '';
				$post_data['post_title']   = $post['title'];
				$post_data['post_content'] = $content;
				$post_data['area']         = $post['area'];
				$post_data['post_name']    = $post['slug'];
				$post_id                   = self::create_post( $post_data, $post['type'] );
				$data[ $post['wp_id'] ]    = ( ! is_wp_error( $post_id ) ) ? $post_id : false;
			}
		}
		if ( empty( $data ) ) {
			wp_send_json( array(), 404 );
		}
		wp_send_json( $data );
	}

	private static function create_post( $data, $type = 'post' ) {
		$constants                    = Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		$theme   = wp_get_theme()->get_stylesheet();
		$data    = (array) $data;
		$content = $data['post_content'];
		if ( in_array( $type, array( 'wp_navigation' ) ) ) {
			$old_theme = ! empty( $data['theme'] ) ? $data['theme'] : '';
			$content   = str_replace( '/' . $old_theme . '/', '/', $data['post_content'] );
		}
		// Create post object.
		$my_post = array(
			'post_title'   => wp_strip_all_tags( $data['post_title'] ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => 'publish',
			'post_type'    => $type,
		);

		if ( ! empty( $data['post_name'] ) ) {
			$my_post['post_name'] = $data['post_name'];
		}

		if ( in_array( $type, array( 'wp_template_part', 'wp_template' ) ) ) {
			$my_post['tax_input'] = array(
				'wp_theme' => $theme,
			);
			if ( ! empty( $data['area'] ) ) {
				$my_post['tax_input']['wp_template_part_area'] = _filter_block_template_part_area( $data['area'] );
			}
		}

		// Insert the post into the database.
		$post_id = wp_insert_post( $my_post );
		if ( ! is_wp_error( $post_id ) ) {
			if ( in_array( $type, array( 'post', 'page', $plugin_main_post_type_prefix . '_template' ), true ) ) {
				if ( ! empty( $data['_meta_data'] ) ) {
					foreach ( $data['_meta_data'] as $meta_key => $meta ) {
						$meta_key = sanitize_key( $meta_key );
						$meta     = is_array( $meta ) ? array_map( 'sanitize_text_field', $meta ) : sanitize_text_field( $meta );
						update_post_meta( $post_id, $meta_key, $meta );
					}
				}
			}

			update_post_meta( $post_id, '_is_' . $plugin_main_function_prefix . '_demo_imported', true );
			update_post_meta( $post_id, '_' . $plugin_main_function_prefix . '_demo_imported_data', $data );
			// if ( ! empty( $data['area'] ) ) {
			// $result = wp_set_post_terms( $post_id, 'wp_template_part_area', _filter_block_template_part_area( $data['area'] ) );
			// error_log( print_r( $result, true ) );
		}

		// $result = wp_set_post_terms( $post_id, 'wp_theme', $theme );
		// error_log( print_r( $result, true ) );
		// }
		// }
		return $post_id;
	}

	/**
	 * Get all demos.
	 *
	 * @return void
	 */
	public static function get_demos() {
		$constants                   = Helpers::plugin_constants();
		$plugin_main_slug            = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_site_domain     = $constants['plugin_main_site_domain'];
		$demo_import_base_url        = ! empty( $constants['demo_import_base_url'] ) ? $constants['demo_import_base_url'] : 'https://demo.' . $plugin_main_site_domain;

		$filters = array(
			'posts_per_page' => -1,
		);
		if ( ! empty( $constants['demo_importer']['api_filters']['theme_category'] ) ) {
			$filters['theme_category'] = $constants['demo_importer']['api_filters']['theme_category'];
		}
		$url = add_query_arg( $filters, $demo_import_base_url . '/wp-json/liger/v1/demos' );

		// [TODO] Update url.
		$json_data = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'liger_hkey' => 'sskdfjks3qw4sdfjs',
				),
			)
		);

		$total_pages = ! empty( $json_data['headers']['x-total-pages'] ) ? absint( $json_data['headers']['x-total-pages'] ) : 0;
		$total_posts = ! empty( $json_data['headers']['x-total-posts'] ) ? absint( $json_data['headers']['x-total-posts'] ) : 0;
		
		try {
			$json = json_decode( $json_data['body'] );
			$json = ! empty( $json ) ? $json : array();
		} catch ( Exception $ex ) {
			$json = array();
		}

		// wp_send_json( $json );
		// Create the response object
		$response = new \WP_REST_Response( $json );
		
		// Add a custom header
		$response->header( 'X-Total-Pages', $total_pages );
		$response->header( 'X-Total-Posts', $total_posts );
		
		return $response;
	}

	public static function get_demo_content( $request ) {
		$constants                   = Helpers::plugin_constants();
		$plugin_main_slug            = $constants['plugin_main_slug'];
		$plugin_main_function_prefix = $constants['plugin_main_function_prefix'];
		$plugin_main_site_domain     = $constants['plugin_main_site_domain'];
		$demo_import_base_url        = ! empty( $constants['demo_import_base_url'] ) ? $constants['demo_import_base_url'] : 'https://demo.' . $plugin_main_site_domain;

		$params = (array) $request->get_params();
		$name   = ! empty( $params['name'] ) ? $params['name'] : '';
		$json   = array();
		if ( ! empty( $name ) ) {
			$json_data = wp_remote_get(
				$demo_import_base_url . '/' . sanitize_title( $name ) . '/wp-json/liger/v1/demo-content',
				array(
					'timeout' => 10,
					'headers' => array(
						'liger_hkey' => 'sskdfjks3qw4sdfjs',
					),
				)
			);
			try {
				$json = json_decode( $json_data['body'] );
				$json = ! empty( $json ) ? $json : array();
			} catch ( Exception $ex ) {
				$json = array();
			}
		}
		wp_send_json( $json );
	}

	public static function activate_theme( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$result = false;
		if ( ! empty( $params['slug'] ) ) {
			$theme = sanitize_text_field( trim( $params['slug'] ) );
			switch_theme( $theme );
			$result = true;
		}
		wp_send_json( $result );
	}

	public static function verify_theme_installation( \WP_REST_Request $req ) {
		$params = (array) $req->get_params();
		$result = array();
		if ( ! empty( $params['slug'] ) ) {
			$all_themes      = wp_get_themes();
			$theme           = trim( $params['slug'] );
			$themes          = array_keys( $all_themes );
			$in_theme_active = get_stylesheet() === $theme;
			$result          = in_array( $theme, $themes, true );
			wp_send_json(
				array(
					'installed_theme' => $result,
					'theme_activated' => $in_theme_active,
				)
			);
		}
	}
}

Rest_Demo_Importer_V2::init();
