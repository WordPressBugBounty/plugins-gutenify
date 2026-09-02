<?php
/**
 * Prompts the admin for a WordPress.org rating once the plugin has been
 * in use for 7+ days, dismissible forever or snoozable for another 7 days.
 *
 * @package gutenify
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and dismisses the "rate us" admin notice.
 */
class Rating_Notice {
	/**
	 * Shared script/style handle for this notice's registered assets.
	 *
	 * @var string
	 */
	public static $handle = 'gutenify-rating-notice';

	/**
	 * Option name storing when this plugin was first detected active.
	 *
	 * @var string
	 */
	const ACTIVATED_AT_OPTION = 'gutenify_activated_at';

	/**
	 * User meta key storing this notice's dismissal state — 'forever',
	 * or a Unix timestamp to snooze until.
	 *
	 * @var string
	 */
	const DISMISSED_META_KEY = 'gutenify_rating_notice_dismissed';

	/**
	 * Hooks the notice render, its assets, and its dismiss action.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_rating_notice' ) );
		add_action( 'wp_ajax_gutenify_dismiss_rating_notice', array( __CLASS__, 'dismiss_rating_notice' ) );
	}

	/**
	 * Returns the activation timestamp, lazily initializing it to "now"
	 * the first time this runs if it was never recorded — the plugin's
	 * activation hook lives in the consumer plugin, not this framework,
	 * so first-run-of-this-code is the reference point. For installs
	 * that predate this feature the 7-day countdown therefore starts
	 * when this code first ships to them, not from their real original
	 * activation date — an accepted approximation; there's no reliable
	 * way to recover the true date after the fact.
	 *
	 * @return int Unix timestamp.
	 */
	private static function get_activated_at() {
		$activated_at = get_option( self::ACTIVATED_AT_OPTION );
		if ( ! $activated_at ) {
			$activated_at = time();
			update_option( self::ACTIVATED_AT_OPTION, $activated_at );
		}
		return (int) $activated_at;
	}

	/**
	 * Whether the notice should currently be shown to this user.
	 *
	 * @return bool
	 */
	private static function should_show() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ( time() - self::get_activated_at() ) < 7 * DAY_IN_SECONDS ) {
			return false;
		}

		$dismissed = get_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, true );
		if ( 'forever' === $dismissed ) {
			return false;
		}
		if ( is_numeric( $dismissed ) && time() < (int) $dismissed ) {
			return false;
		}

		return true;
	}

	/**
	 * Registers and localizes the rating notice's script and style —
	 * only when it will actually be shown, so the asset doesn't ship on
	 * every admin page load regardless of relevance.
	 */
	public static function register_assets() {
		if ( ! self::should_show() ) {
			return;
		}

		$constants = Helpers::plugin_constants();
		$base_url  = Helpers::core_base_url();

		$asset_file_values = Helpers::asset_file_values( __DIR__ . '/index.asset.php' );
		$deps              = $asset_file_values['dependencies'];
		$ver               = $asset_file_values['version'];

		wp_register_script( self::$handle, $base_url . 'dist/non-blocks/admin/rating-notice/index.js', $deps, $ver, true );
		wp_register_style( self::$handle, $base_url . 'dist/non-blocks/admin/rating-notice/index.css', array(), $ver );

		/**
		 * Filters the URL the "rate us" notice sends the admin to.
		 * Defaults to the plugin's WordPress.org review form; consumers
		 * not hosted on WordPress.org should override this.
		 *
		 * @param string $review_url Review form URL.
		 */
		$review_url = apply_filters(
			'gutenify_rating_notice_review_url',
			'https://wordpress.org/support/plugin/gutenify/reviews/#new-post'
		);

		wp_localize_script(
			self::$handle,
			'gutenify_rating_vars',
			array(
				'plugin_name' => isset( $constants['title'] ) ? $constants['title'] : 'Gutenify',
				'review_url'  => esc_url( $review_url ),
				'nonce'       => wp_create_nonce( 'gutenify-rating-nonce' ),
			)
		);
	}

	/**
	 * Renders the notice mount point, if it should currently show.
	 */
	public static function admin_rating_notice() {
		if ( ! self::should_show() ) {
			return;
		}

		wp_enqueue_style( self::$handle );
		wp_enqueue_script( self::$handle );
		echo '<div class="notice notice-info gutenify-rating-notice" id="gutenify_rating_notice"></div>';
	}

	/**
	 * AJAX handler: persists this user's rating-notice dismissal —
	 * 'forever', or a 7-day snooze for anything else.
	 */
	public static function dismiss_rating_notice() {
		check_ajax_referer( 'gutenify-rating-nonce', 'gutenify-rating-nonce-name' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$type  = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		$value = 'forever' === $type ? 'forever' : ( time() + 7 * DAY_IN_SECONDS );

		update_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, $value );

		wp_die( 1 );
	}
}

Rating_Notice::init();
