<?php
/**
 * Plugin Name: Gutenify - Visual Site Builder Blocks & Starter Templates
 * Description: Gutenify - Visual Site Builder Blocks & Starter Templates is a collection of Gutenberg Advance Fullsite Editing Blocks & starter templates that is compatible with WordPress Full Site Editing to help you create the website you always wanted.
 * Author: Gutenify
 * Author URI: https://www.gutenify.com
 * Plugin URI: https://www.gutenify.com
 * Version: 1.7.0
 * Text Domain: gutenify
 * Domain Path: /languages
 * Tested up to: 7.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 * @package Gutenify
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Define constants.
define('GUTENIFY_VERSION', '1.7.0');
define('GUTENIFY_BASE_DIR', plugin_dir_path(__FILE__));
define('GUTENIFY_BASE_URL', trailingslashit(plugin_dir_url(__FILE__)));
define('GUTENIFY_BASE_FILE', __FILE__);
// First PRO version that no longer bundles its own copies of the blocks
// that moved into this plugin (Popup, Hover Card, Hover Card Normal/Hover,
// WooCommerce Product Images Slider) -- PRO versions below this still
// declare the exact same class/function names as this plugin's copies.
// Used by exclude_blocks_still_bundled_in_old_pro() below to avoid a fatal
// "Cannot redeclare" error when an old PRO is active.
define('GUTENIFY_MOVED_BLOCKS_MIN_PRO_VERSION', '1.5.0');
define(
	'GUTENIFY_BRAND_LOGO',
	'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDEwODAgMTA4MCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTA4MCAxMDgwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPg0KICAuc3QwIHsNCiAgICBmaWxsOiAjRkZGRkZGOw0KICB9DQoNCiAgLnN0MSB7DQogICAgZmlsbDogIzY3QkM0NTsNCiAgfQ0KDQo8L3N0eWxlPg0KPGc+DQogIDxnPg0KICAgIDxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04MjguNSw1NTIuOWMtNi44LDE1Mi45LTEzMy4zLDI3NS4xLTI4Ny45LDI3NS4xYy0xNTguOSwwLTI4OC4yLTEyOS4zLTI4OC4yLTI4OC4yDQoJCSAgICAgIGMwLTE1MC42LDExNi4yLTI3NC41LDI2My41LTI4Ny4xVjAuNEMyMjkuMSwxMy4yLDAuNSwyNDkuOSwwLjUsNTM5LjljMCwyOTguMiwyNDEuNyw1NDAuMSw1NDAuMSw1NDAuMQ0KCQkgICAgICBjMjkzLjksMCw1MzMtMjM0LjgsNTM5LjgtNTI3SDgyOC41VjU1Mi45eiIgLz4NCiAgICA8cmVjdCB4PSI1MTguOSIgeT0iMjU0LjYiIGNsYXNzPSJzdDEiIHdpZHRoPSIzMDkuOCIgaGVpZ2h0PSIyOTguMiIgLz4NCiAgPC9nPg0KPC9nPg0KPC9zdmc+DQo='
);

if (!class_exists('Gutenify')) {

	final class Gutenify
	{

		/**
		 * Holds the class instance.
		 *
		 * @var Gutenify
		 */
		private static $instance = null;

		/**
		 * Plugin base URL.
		 *
		 * @var string
		 */
		private static $base_url = '';

		/**
		 * Get the singleton instance.
		 *
		 * @return Gutenify
		 */
		public static function instance()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Gutenify constructor.
		 */
		private function __construct()
		{
			self::$base_url = GUTENIFY_BASE_URL;
			$this->define_hooks();
			$this->bootstrap();
		}

		/**
		 * Define plugin hooks.
		 */
		private function define_hooks()
		{
			add_filter('gutenify_plugin_constants', array($this, 'add_plugin_constants'));
			add_filter('gutenify_active_blocks', array($this, 'exclude_blocks_still_bundled_in_old_pro'));
			// Uncomment if you want activation redirect.
			// add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
		}

		/**
		 * This plugin's own bootstrap() always requires its own block files
		 * first, then -- only if PRO is already loaded -- calls gutenify_pro(),
		 * which runs PRO's bootstrap and requires PRO's block files too (see
		 * bootstrap() below). PRO versions older than
		 * GUTENIFY_MOVED_BLOCKS_MIN_PRO_VERSION still carry their own copies
		 * of Popup, Hover Card, Hover Card Normal/Hover, and WooCommerce
		 * Product Images Slider under the identical class/function names, so
		 * letting both plugins load their own copies fatals with "Cannot
		 * redeclare class/function ...".
		 *
		 * Deferring to the old PRO's own (already-working, ungated)
		 * registration of these blocks avoids the collision entirely -- PRO
		 * being active is exactly the condition under which these blocks
		 * should be active anyway.
		 *
		 * Relies on GUTENIFY_PRO_VERSION being defined by this point, same as
		 * the version_compare() check in bootstrap() below -- both depend on
		 * PRO's plugin file having already loaded, which is guaranteed by the
		 * standard install flow (PRO's activation hook installs/activates
		 * this plugin, so PRO's entry in `active_plugins` precedes this
		 * plugin's).
		 *
		 * @param array $blocks
		 * @return array
		 */
		public function exclude_blocks_still_bundled_in_old_pro($blocks)
		{
			$old_pro_still_has_these_blocks = defined('GUTENIFY_PRO_VERSION')
				&& version_compare(GUTENIFY_PRO_VERSION, GUTENIFY_MOVED_BLOCKS_MIN_PRO_VERSION, '<');

			if (!$old_pro_still_has_these_blocks) {
				return $blocks;
			}

			return array_diff(
				$blocks,
				array('popup', 'hover-card', 'hover-card-normal', 'hover-card-hover', 'wc-product-images-slider')
			);
		}

		/**
		 * Provide plugin constants merged with args.
		 *
		 * @param array $args
		 * @return array
		 */
		public function add_plugin_constants($args)
		{
			return wp_parse_args($args, $this->get_constants());
		}

		/**
		 * Get all plugin constants as array.
		 *
		 * @return array
		 */
		public function get_constants()
		{
			$title = 'Gutenify';
			return array(
				'title' => $title,
				'prefix' => 'gutenify',
				'slug' => 'gutenify',
				'authorWebSite' => 'https://gutenify.com',
				'authorDemoWebSite' => 'https://demo.gutenify.com',
				'authorWebSiteProPage' => 'https://gutenify.com/pricing',
				'authorWebSiteSupport' => 'https://gutenify.com/product-support',
				'plugin_main_slug' => 'gutenify',
				'plugin_main_camel_case_name' => 'gutenify',
				'plugin_main_function_prefix' => 'gutenify',
				'plugin_main_base_url' => GUTENIFY_BASE_URL,
				'plugin_main_base_dir' => GUTENIFY_BASE_DIR,
				'plugin_main_version' => GUTENIFY_VERSION,
				'plugin_main_post_type_prefix' => 'gutenify',
				'plugin_main_site_domain' => 'gutenify.com',
				'core_base_dir' => GUTENIFY_BASE_DIR . 'core/',
				'core_base_url' => GUTENIFY_BASE_URL . 'core/',
				'brand_color' => '#2196f3',
				'pro_title' => $title . ' Pro',
			);
		}

		/**
		 * Load bootstrap and pro if available.
		 */
		private function bootstrap()
		{
			require_once GUTENIFY_BASE_DIR . 'core/inc/bootstrap.php';
			if (
				function_exists('gutenify_pro')
				&& defined('GUTENIFY_PRO_VERSION')
				&& version_compare(GUTENIFY_PRO_VERSION, '1.1.5', '>')
			) {
				gutenify_pro();
			}
		}

		/**
		 * Optionally redirect to Gutenify page on plugin activation.
		 *
		 * @param string $plugin Plugin basename.
		 */
		public function activation_redirect($plugin)
		{
			if (function_exists('get_current_screen')) {
				$screen = get_current_screen();
				if (!empty($screen->id) && 'appearance_page_tgmpa-install-plugins' === $screen->id) {
					return false;
				}
			}
			if ($plugin === plugin_basename(__FILE__)) {
				wp_safe_redirect(admin_url('admin.php?page=gutenify'));
				exit;
			}
		}
	}
}

/**
 * Initialize the Gutenify plugin.
 */
function gutenify()
{
	return Gutenify::instance();
}

// Initialize plugin immediately.
gutenify();
