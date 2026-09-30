<?php
namespace gutenify;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Background image sideloading for the full demo-site importer.
 *
 * Rest_Demo_Importer_V2::create_post() inserts imported posts/pages/
 * templates immediately with their content as-is (still pointing at the
 * demo source's remote images), then queues the post here instead of
 * sideloading inline. This class does the actual downloading afterward, off
 * the request that's blocking the import wizard's UI, via a WP-Cron-style
 * non-blocking loopback request (the same mechanism core's spawn_cron()
 * uses) so it starts immediately rather than waiting on WP-Cron's next
 * site-visit trigger.
 *
 * Single-template/kit import (Gutenify_Rest::get_template_data()) is
 * intentionally NOT routed through this queue: that endpoint only returns
 * content for the browser to insert into the currently-open, not-yet-saved
 * editor session — there is no post row yet to queue or patch, so it stays
 * synchronous (see Helpers::sideload_remote_images() called inline there).
 */
class Image_Sideload_Queue {

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
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		register_rest_route(
			$plugin_main_slug . '/demo-import/v1',
			'/process-image-queue',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'process_queue' ),
				'permission_callback' => array( __CLASS__, 'verify_token' ),
			)
		);
	}

	/**
	 * Marks a just-imported post as having remote images pending sideload,
	 * to be picked up by the next queue run.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function enqueue_post( $post_id ) {
		update_post_meta( $post_id, self::meta_key(), '1' );
	}

	/**
	 * Fires a non-blocking loopback request to kick off (or continue) queue
	 * processing in a separate PHP process, so the caller doesn't wait on it.
	 * Mirrors core's spawn_cron(): a near-zero timeout plus `blocking =>
	 * false` means wp_remote_post() returns almost immediately here while
	 * the request it opened keeps running server-side.
	 *
	 * @return void
	 */
	public static function trigger_async() {
		$constants        = Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];

		$url = add_query_arg(
			'token',
			self::get_secret_token(),
			rest_url( $plugin_main_slug . '/demo-import/v1/process-image-queue' )
		);

		wp_remote_post(
			$url,
			array(
				// Matches core's own spawn_cron() convention exactly:
				// `blocking => false` alone doesn't guarantee this call
				// returns fast in every environment (measured on this local
				// Studio dev server: raising this to a few seconds just
				// made the CALLER wait nearly that long too, instead of
				// returning sooner — apparently the transport polls up to
				// `timeout` rather than exiting the moment the request is
				// sent). A small timeout is the safer default across
				// hosting environments; it can fail to get the request out
				// occasionally, which is why this is self-healing — see the
				// call site in create_post(), which retriggers once per
				// imported post, and process_queue(), which only clears a
				// post's pending flag on full success so a missed trigger
				// just gets retried on the next one.
				'timeout'   => 0.01,
				'blocking'  => false,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			)
		);
	}

	/**
	 * Worker: sideloads images for one batch of queued posts, then re-fires
	 * itself (async) if the batch was full, so a large demo import continues
	 * across multiple short-lived requests instead of one long-running one.
	 *
	 * @return \WP_REST_Response
	 */
	public static function process_queue() {
		$lock_key = self::lock_key();

		// Another run is already in flight — let it continue; it will pick
		// up anything queued after it started on its own next batch.
		if ( get_transient( $lock_key ) ) {
			return new \WP_REST_Response( array( 'success' => true, 'skipped' => 'locked' ), 200 );
		}

		set_transient( $lock_key, 1, 2 * MINUTE_IN_SECONDS );

		$constants                   = Helpers::plugin_constants();
		$plugin_main_site_domain     = $constants['plugin_main_site_domain'];
		$batch_size                  = 10;

		$post_ids = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => $batch_size,
				'fields'         => 'ids',
				'meta_key'       => self::meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'no_found_rows'  => true,
			)
		);

		$processed   = 0;
		$needs_retry = false;
		$max_retries = 5;

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( $post ) {
				$new_content = Helpers::sideload_remote_images( $post->post_content, $plugin_main_site_domain );

				if ( $new_content !== $post->post_content ) {
					wp_update_post(
						array(
							'ID'           => $post_id,
							'post_content' => $new_content,
						)
					);
				}

				// media_sideload_image() can fail for reasons that are
				// transient (a network blip, the demo source briefly down)
				// rather than permanent — only clear the pending flag once
				// nothing is left to retry, so a failed attempt gets picked
				// up again by the next trigger instead of the image being
				// silently abandoned as remote forever.
				if ( Helpers::has_remote_images( $new_content, $plugin_main_site_domain ) ) {
					$attempts = (int) get_post_meta( $post_id, self::attempts_key(), true ) + 1;

					if ( $attempts >= $max_retries ) {
						// Given up — leave whatever images remain remote
						// rather than retrying forever on a permanently
						// unreachable URL.
						delete_post_meta( $post_id, self::meta_key() );
						delete_post_meta( $post_id, self::attempts_key() );
					} else {
						update_post_meta( $post_id, self::attempts_key(), $attempts );
						$needs_retry = true;
					}
				} else {
					delete_post_meta( $post_id, self::meta_key() );
					delete_post_meta( $post_id, self::attempts_key() );
				}
			} else {
				delete_post_meta( $post_id, self::meta_key() );
				delete_post_meta( $post_id, self::attempts_key() );
			}

			++$processed;
		}

		delete_transient( $lock_key );

		// Either there may be more queued than this batch could hold, or
		// something in this batch still needs a retry — either way, another
		// pass is warranted.
		if ( count( $post_ids ) === $batch_size || $needs_retry ) {
			self::trigger_async();
		}

		return new \WP_REST_Response( array( 'success' => true, 'processed' => $processed ), 200 );
	}

	/**
	 * Verifies the shared-secret token on an incoming queue-processing
	 * request. This endpoint is only ever called by this site's own
	 * server-side loopback trigger, never a browser, so it's gated by a
	 * per-site HMAC token derived from a WordPress auth salt rather than
	 * `current_user_can()` — a loopback request carries no reliable user
	 * session to check.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public static function verify_token( \WP_REST_Request $request ) {
		$token = $request->get_param( 'token' );

		return is_string( $token ) && hash_equals( self::get_secret_token(), $token );
	}

	/**
	 * Per-site secret used to authorize the loopback request without
	 * depending on cookies/user session.
	 *
	 * @return string
	 */
	private static function get_secret_token() {
		return hash_hmac( 'sha256', 'gutenify_image_sideload_queue', wp_salt( 'auth' ) );
	}

	/**
	 * Post meta key flagging a post as having pending remote images.
	 *
	 * @return string
	 */
	private static function meta_key() {
		$constants = Helpers::plugin_constants();

		return '_' . $constants['plugin_main_function_prefix'] . '_pending_image_sideload';
	}

	/**
	 * Post meta key counting failed sideload attempts, so a permanently
	 * unreachable image doesn't get retried forever.
	 *
	 * @return string
	 */
	private static function attempts_key() {
		$constants = Helpers::plugin_constants();

		return '_' . $constants['plugin_main_function_prefix'] . '_image_sideload_attempts';
	}

	/**
	 * Transient key used to prevent overlapping queue runs.
	 *
	 * @return string
	 */
	private static function lock_key() {
		$constants = Helpers::plugin_constants();

		return $constants['plugin_main_function_prefix'] . '_image_sideload_lock';
	}
}

Image_Sideload_Queue::init();
