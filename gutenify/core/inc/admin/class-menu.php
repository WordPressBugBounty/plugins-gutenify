<?php
/**
 * Admin Menu for Gutenify
 *
 * Handles registration and rendering of the Gutenify admin menu and submenus.
 *
 * @package Gutenify
 */

namespace gutenify;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Menu
 *
 * Handles the admin menu and related callbacks for Gutenify.
 */
class Menu {

	/**
	 * Menu constructor.
	 *
	 * Adds required WordPress hooks for menu and redirection.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_sub_menu' ) );
		add_action( 'init', array( $this, 'redirect_template_kit_page' ) );
	}

	/**
	 * Redirects old 'gutenify-template-kits' submenu to 'gutenify-demo-importer'.
	 *
	 * Prevents users from landing on deprecated submenu.
	 *
	 * @return void
	 */
	public function redirect_template_kit_page() {
		if ( ! is_admin() ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( ! empty( $page ) && 'gutenify-template-kits' === $page ) {
			wp_safe_redirect( admin_url( 'admin.php?page=gutenify-demo-importer' ) );
			exit;
		}
	}

	/**
	 * Registers the main Gutenify menu and all submenus.
	 *
	 * @return void
	 */
	public function register_sub_menu() {
		// Main menu page.
		add_menu_page(
			__( 'Gutenify: Getting Started', 'gutenify' ),
			__( 'Gutenify', 'gutenify' ),
			'manage_options',
			'gutenify',
			array( $this, 'getting_started_page_callback' ),
			GUTENIFY_BRAND_LOGO,
			90
		);

		// First submenu: label for first submenu.
		add_submenu_page(
			'gutenify',
			esc_html__( 'Gutenify: Getting Started', 'gutenify' ),
			esc_html__( 'Getting Started', 'gutenify' ),
			'manage_options',
			'gutenify',
			array( $this, 'getting_started_page_callback' )
		);

		// Demo Importer submenu.
		add_submenu_page(
			'gutenify',
			__( 'Gutenify Demo Importer', 'gutenify' ),
			__( 'Demo Importer', 'gutenify' ),
			'manage_options',
			'gutenify-demo-importer',
			array( $this, 'demo_importer_page_callback' )
		);

		// Settings submenu.
		add_submenu_page(
			'gutenify',
			__( 'Gutenify Settings', 'gutenify' ),
			__( 'Settings', 'gutenify' ),
			'manage_options',
			'gutenify-settings',
			array( $this, 'settings_page_callback' )
		);
	}

	/**
	 * Callback for the Demo Importer submenu page.
	 *
	 * @return void
	 */
	public function demo_importer_page_callback() {
		$handle = Demo_Importer_V2::$handle;
		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );
		echo '<div class="wrap">';
		echo '<div id="gutenify-demo-importer-app">Loading...</div>';
		echo '</div>';
	}

	/**
	 * Callback for the (optional) Start Up submenu page.
	 *
	 * @return void
	 */
	public function startup_page_callback() {
		echo '<div class="wrap">';
		echo '<div id="gutenify-start-up-app">Loading...</div>';
		echo '</div>';
	}

	/**
	 * Callback for the Settings submenu page.
	 *
	 * @return void
	 */
	public function settings_page_callback() {
		$handle = Settings_Admin_Page::$handle;
		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );
		echo '<div class="wrap">';
		echo '<div id="gutenify-settings-app">Loading...</div>';
		echo '</div>';
	}

	/**
	 * Callback for the Getting Started main menu page.
	 *
	 * @return void
	 */
	public function getting_started_page_callback() {
		$handle = Getting_Started::$handle;
		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );

		$output  = '<div class="wrap">';
		$output .= '<div id="gutenify-getting-started-app">Loading...</div>';
		$output .= '</div>';

		echo wp_kses_post( $output );
	}
}

// Initialize the admin menu.
new Menu();
