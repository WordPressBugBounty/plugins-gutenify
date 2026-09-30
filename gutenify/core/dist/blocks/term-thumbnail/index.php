<?php
/**
 * Term Thumbnail Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\Term_Thumbnail;

defined( 'ABSPATH' ) || exit;

class Term_Thumbnail {

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'init', array( __CLASS__, 'register_term_meta' ) );
	}

	/**
	 * Exposes the `thumbnail_id` term meta convention this block already
	 * reads (see render.php) over the REST API, for any taxonomy.
	 *
	 * Without this, `GET /wp/v2/<taxonomy>/<id>` returns an empty `meta`
	 * object regardless of whether term meta exists — WordPress never
	 * exposes a meta key over REST unless it's explicitly registered with
	 * `show_in_rest`, confirmed live against this site's own
	 * `product_cat` terms. That silently blocked the block editor's own
	 * Focal Point picker (edit.js) from ever finding a real image to show
	 * a preview against, since it has no other way to resolve "this
	 * term's thumbnail" client-side.
	 */
	public static function register_term_meta() {
		register_term_meta(
			'',
			'thumbnail_id',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Register the block.
	 *
	 * block.json's `render` field wires render.php as the render callback
	 * automatically. Unlike category-list/single-product-gallery, this
	 * block is registered unconditionally (not gated behind WooCommerce or
	 * WP_DEBUG) — it works for any taxonomy that happens to store a
	 * thumbnail under the `thumbnail_id` term meta key, and render.php
	 * itself only reaches into WooCommerce for its own placeholder-image
	 * fallback when the taxonomy is `product_cat`.
	 */
	public static function register_block() {
		register_block_type( __DIR__ );
	}
}

Term_Thumbnail::init();
