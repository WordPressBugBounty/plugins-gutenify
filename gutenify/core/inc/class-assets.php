<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Assets {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ), 9 );
	}

	/**
	 * Register plugin assets.
	 *
	 * @return void
	 */
	public static function register_assets() {
		$constants                    = Helpers::plugin_constants();
		$plugin_main_slug             = $constants['plugin_main_slug'];
		$plugin_main_function_prefix  = $constants['plugin_main_function_prefix'];
		$plugin_main_base_url         = Helpers::core_base_url();
		$plugin_main_base_dir         = Helpers::core_base_dir();
		$plugin_main_version          = $constants['plugin_main_version'];
		$plugin_main_post_type_prefix = $constants['plugin_main_post_type_prefix'];

		global $wp_version;

		// Admin and Frontend localized vars.
		$localized_vars = array(
			'site_url'                                => esc_url( site_url() ),
			$plugin_main_function_prefix . '_version' => $plugin_main_version,
			'is_woocommerce_activated'                => class_exists( 'woocommerce' ),
			'wp_version'                              => $wp_version,
			'add_template_url'                        => esc_url( admin_url( 'post-new.php?post_type=' . $plugin_main_post_type_prefix . '_template' ) ),
			'pro_account_url'                         => esc_url( admin_url( 'admin.php?page=' . $plugin_main_slug . '-pro-license' ) ),
		);

		// Register anonymous handle.
		$handle = $plugin_main_slug . '-global-inline-handle';
		wp_register_style( $handle, false );
		wp_register_script( $handle, false );

		if ( is_admin() ) {
			$localized_vars = array_merge(
				$localized_vars,
				array(
					'site_url'                 => esc_url( site_url() ),
					'plugin_directory_url'     => esc_url( $plugin_main_base_url ),
					$plugin_main_function_prefix . '_version' => $plugin_main_version,
					'pro_license_status'       => apply_filters( $plugin_main_function_prefix . '_pro_license_status', false ),
					'is_pro_activated'         => Helpers::is_pro_active(),
					'is_block_theme'           => wp_is_block_theme(), // function_exists( 'wp_get_theme' ) && ! empty( wp_get_theme()->is_block_theme() ),
					'is_woocommerce_activated' => class_exists( 'woocommerce' ),
					'gutenify_com_server_url' => defined( 'GUTENIFY_COM_SERVER_URL' ) ? trailingslashit( GUTENIFY_COM_SERVER_URL ) : trailingslashit( 'https://api.gutenify.com/' ),
					'font_families'            => gutenify_font_families(),
					'wp_version'               => $wp_version,
					'active_blocks'            => Helpers::active_blocks(),
				)
			);

		}
		$defaults       = Helpers::plugin_constants();
		$localized_vars = wp_parse_args( $localized_vars, $defaults );

		// Scrollmagic JS.
		$name     = $plugin_main_slug . '-scrollmagic';
		$filepath = 'assets/js/lib/scrollmagic';
		wp_register_script( $name, $plugin_main_base_url . $filepath . '/ScrollMagic.min.js', array( 'jquery' ), '2.0.8', false );
		wp_register_script( $name, $plugin_main_base_url . $filepath . '/ScrollMagic.min.js', array( 'jquery' ), '2.0.8', false );

		// Isotope JS.
		$name     = 'gutenify-isotope';
		$filepath = 'assets/js/lib/isotope.pkgd';
		wp_register_script( $name, $plugin_main_base_url . $filepath . '.js', array( 'jquery' ), '3.0.6', false );

		// Regsiter fonts url.
		$fonts_url = gutenify_fonts_url();
		wp_register_style( $plugin_main_slug . '-fonts', $fonts_url, array(), null );

		wp_register_style( $plugin_main_slug . '-fontawesome', $plugin_main_base_url . 'assets/fontawesome/css/all.css', array(), 'v4' );

		// Swiper JS.
		wp_register_script( $plugin_main_slug . '-swiper', $plugin_main_base_url . 'assets/js/lib/swiper-bundle.js', array( 'jquery' ), '6.8.2', true );

		// Swiper styles.
		wp_register_style( $plugin_main_slug . '-swiper', $plugin_main_base_url . 'assets/css/lib/swiper-bundle.min.css', array(), '6.8.2' );

		// Magnific JS.
		wp_register_script( 'jquery-magnific-popup', $plugin_main_base_url . 'assets/js/lib/jquery.magnific-popup.js', array( 'jquery' ), '1.1.0', true );

		// Magnific style.
		wp_register_style( 'jquery-magnific-popup', $plugin_main_base_url . 'assets/css/lib/magnific-popup.css', array(), '1.1.0' );

		// Marquee JS.
		wp_register_script( 'jquery-marquee', $plugin_main_base_url . 'assets/js/lib/jquery.marquee.js', array( 'jquery' ), '1.6.0', true );

		$asset_paths = array(
			// Frontend.
			'frontend' => array(
				'path'               => 'dist/non-blocks/frontend',
				'js_dependencies'    => array( $plugin_main_slug . '-scrollmagic' ),
				'style_dependencies' => array( $plugin_main_slug . '-fontawesome', $plugin_main_slug . '-fonts' ), // , 'global-styles', 'woocommerce-layout'
			),
		);

		foreach ( $asset_paths as $handle => $asset ) {
			$path       = $asset['path'];
			$asset_file = gutenify_get_block_asset_file_values( sprintf( '%s' . $path . '/index.asset.php', $plugin_main_base_dir ) );
			$handle     = $plugin_main_slug . '-' . str_replace( '/', '-', $handle );

			$deps = ! empty( $asset['js_dependencies'] ) ? array_merge( $asset_file['dependencies'], $asset['js_dependencies'] ) : $asset_file['dependencies'];
			wp_register_script( $handle, $plugin_main_base_url . $path . '/index.js', $deps, $asset_file['version'], true );

			if ( file_exists( $plugin_main_base_dir . $path . '/index.css' ) ) {
				$deps = ! empty( $asset['style_dependencies'] ) ? $asset['style_dependencies'] : array();
				wp_register_style( $handle, $plugin_main_base_url . $path . '/index.css', $deps, $asset_file['version'] );
			}
		}

		// Localize vars.
		wp_localize_script( $plugin_main_slug . '-global-inline-handle', '_' . $plugin_main_function_prefix . '_vars', apply_filters( $plugin_main_slug . '--editor--localized-vars', $localized_vars ) );

		wp_register_script( $plugin_main_slug . '-lib-aos', $plugin_main_base_url . '/assets/js/lib/aos.js', array(), '2.3.4', true );
		wp_register_style( $plugin_main_slug . '-lib-aos', $plugin_main_base_url . '/assets/css/lib/aos.css', array(), '2.3.4' );
	}

	/**
	 * Enqueue front-end assets for blocks.
	 *
	 * @access public
	 * @since 1.9.5
	 */
	public static function enqueue_frontend_scripts() {
		$plugin_constants = Helpers::plugin_constants();
		$plugin_main_slug = $plugin_constants['plugin_main_slug'];
		wp_enqueue_script( $plugin_main_slug . '-frontend' );
	}
}

Assets::init();
