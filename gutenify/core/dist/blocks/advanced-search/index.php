<?php
/**
 * Advanced Search Block
 *
 * @package gutenify
 */

namespace gutenify\Blocks\Advanced_Search;

defined( 'ABSPATH' ) || exit;

class Advanced_Search {

	/**
	 * REST namespace, matching the framework's own
	 * gutenify/v1 convention (see inc/rest-api/class-rest.php).
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'gutenify/v1';

	/**
	 * Init function.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register the block.
	 *
	 * Skip registering entirely when WooCommerce isn't active — there is
	 * nothing to search. render.php also bails independently, as defense
	 * in depth for a block instance saved from before WooCommerce was
	 * deactivated.
	 */
	public static function register_block() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		register_block_type( __DIR__ );
	}

	/**
	 * Register the `/products/search` REST route the frontend
	 * view-script.js fetches from (see render.php's `data-rest-url`).
	 *
	 * Ported from the original "Firefly Blocks Pro" source, which
	 * referenced a `Firefly_Blocks_Pro_Product_Search` REST controller
	 * class that does not exist anywhere in this codebase — the block
	 * would have fatal-errored the moment it rendered. This replaces
	 * that missing dependency with a self-contained route.
	 */
	public static function register_routes() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		register_rest_route(
			self::REST_NAMESPACE,
			'/products/search',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'search_products' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'search' => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'limit'  => array(
						'type'              => 'integer',
						'default'           => 6,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * REST callback: search published products by title/SKU and
	 * matching product categories, in the shape view-script.js expects —
	 * `{ products: [ { url, imageHtml, name, priceHtml } ], categories: [ { url, name } ] }`.
	 *
	 * @param \WP_REST_Request $request Request instance.
	 * @return \WP_REST_Response
	 */
	public static function search_products( $request ) {
		$search = trim( (string) $request->get_param( 'search' ) );
		$limit  = max( 1, min( 20, absint( $request->get_param( 'limit' ) ) ) );

		if ( '' === $search ) {
			return new \WP_REST_Response(
				array(
					'products'   => array(),
					'categories' => array(),
				)
			);
		}

		$product_ids = self::find_product_ids( $search, $limit );

		$products = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$products[] = array(
				'url'       => get_permalink( $product_id ),
				'imageHtml' => $product->get_image( 'woocommerce_gallery_thumbnail' ),
				'name'      => $product->get_name(),
				'priceHtml' => wp_strip_all_tags( $product->get_price_html() ),
			);
		}

		$categories = array();
		$terms      = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'name__like' => $search,
				'number'     => 5,
				'hide_empty' => true,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$categories[] = array(
					'url'  => get_term_link( $term ),
					'name' => $term->name,
				);
			}
		}

		return new \WP_REST_Response(
			array(
				'products'   => $products,
				'categories' => $categories,
			)
		);
	}

	/**
	 * Finds published product IDs matching the search term by title or
	 * SKU, title matches ranked first.
	 *
	 * @param string $search Search term.
	 * @param int    $limit  Max results.
	 * @return int[]
	 */
	private static function find_product_ids( $search, $limit ) {
		$title_matches = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $search,
				'posts_per_page' => $limit,
				'fields'         => 'ids',
			)
		);

		if ( count( $title_matches ) >= $limit ) {
			return $title_matches;
		}

		$sku_matches = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $limit - count( $title_matches ),
				'fields'         => 'ids',
				'post__not_in'   => $title_matches,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_sku',
						'value'   => $search,
						'compare' => 'LIKE',
					),
				),
			)
		);

		return array_merge( $title_matches, $sku_matches );
	}
}

Advanced_Search::init();
