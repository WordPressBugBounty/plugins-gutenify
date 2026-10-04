<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Makes a whole block clickable (core/group for now, see linkable.json).
 *
 * The link is added when the block renders, so the saved markup never changes:
 * existing blocks stay valid, and without the plugin the block is just a group.
 * An empty link is inserted as the last child of the root element and stretched
 * over the block with CSS (styles/frontend.scss).
 */
class Block_Link {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_style' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_editor_style' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'register_editor_script' ) );
		add_filter( 'register_block_type_args', array( __CLASS__, 'add_post_context' ), 10, 2 );

		foreach ( self::blocks() as $block_name ) {
			add_filter( 'gutenify_render_block_' . $block_name, array( __CLASS__, 'render' ), 10, 4 );
		}
	}

	/**
	 * Blocks that can be linked. The same file feeds the editor.
	 *
	 * @return string[]
	 */
	private static function blocks() {
		static $blocks = null;
		if ( null === $blocks ) {
			$config = wp_json_file_decode( __DIR__ . '/linkable.json', array( 'associative' => true ) );
			$blocks = is_array( $config ) && ! empty( $config['blocks'] ) && is_array( $config['blocks'] ) ? array_values( array_filter( $config['blocks'], 'is_string' ) ) : array();
		}
		return $blocks;
	}

	private static function handle() {
		$constants = Helpers::plugin_constants();
		return $constants['plugin_main_slug'] . '-extend-block-link';
	}

	public static function register_style() {
		$base_dir = Helpers::core_base_dir();
		$base_url = Helpers::core_base_url();
		$assets   = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/block-link/frontend.asset.php', $base_dir ) );

		wp_register_style( self::handle(), $base_url . 'dist/non-blocks/extend/block-link/frontend.css', array(), $assets['version'] );
	}

	/**
	 * The editor canvas loads the stylesheet for the "linked" chip. On the
	 * frontend it is only enqueued when a block is linked.
	 */
	public static function enqueue_editor_style() {
		if ( is_admin() ) {
			wp_enqueue_style( self::handle() );
		}
	}

	public static function register_editor_script() {
		$base_dir = Helpers::core_base_dir();
		$base_url = Helpers::core_base_url();
		$assets   = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/block-link/index.asset.php', $base_dir ) );
		$handle   = self::handle() . '-editor';

		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/block-link/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/block-link/index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}

	/**
	 * Core blocks declare no context, so a linked block would not know which
	 * post it sits in. Core fills postId from the global post, and a Query Loop
	 * fills it for every item.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_name Block name.
	 * @return array
	 */
	public static function add_post_context( $args, $block_name ) {
		if ( in_array( $block_name, self::blocks(), true ) ) {
			$existing             = isset( $args['uses_context'] ) && is_array( $args['uses_context'] ) ? $args['uses_context'] : array();
			$args['uses_context'] = array_values( array_unique( array_merge( $existing, array( 'postId', 'postType' ) ) ) );
		}
		return $args;
	}

	/**
	 * The accessible name of the link: a custom label, then the first heading in
	 * the block, then the post title, then the host of the URL.
	 *
	 * @param array  $link    The gtfyLink attribute.
	 * @param string $content Rendered block HTML.
	 * @param string $url     Link URL.
	 * @param int    $post_id Post id for a post link, or 0.
	 * @return string Plain text.
	 */
	private static function label( $link, $content, $url, $post_id ) {
		$custom = isset( $link['label'] ) && is_string( $link['label'] ) ? trim( wp_strip_all_tags( $link['label'] ) ) : '';
		if ( '' !== $custom ) {
			return $custom;
		}

		if ( preg_match( '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $content, $matches ) ) {
			$heading = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $matches[1] ) ) );
			if ( '' !== $heading ) {
				return $heading;
			}
		}

		if ( $post_id ) {
			$title = trim( wp_strip_all_tags( get_the_title( $post_id ) ) );
			if ( '' !== $title ) {
				return $title;
			}
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		return is_string( $host ) && '' !== $host ? $host : __( 'Read more', 'gutenify' );
	}

	public static function render( $content, $block, $instance, $block_id ) {
		if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], self::blocks(), true ) || '' === $content ) {
			return $content;
		}

		$link = isset( $block['attrs']['gtfyLink'] ) && is_array( $block['attrs']['gtfyLink'] ) ? $block['attrs']['gtfyLink'] : array();
		if ( empty( $link ) ) {
			return $content;
		}

		$post_id = 0;
		if ( isset( $link['source'] ) && 'post' === $link['source'] ) {
			$post_id = $instance instanceof \WP_Block && ! empty( $instance->context['postId'] ) ? (int) $instance->context['postId'] : (int) get_the_ID();
			$url     = $post_id ? get_permalink( $post_id ) : '';
		} else {
			$url = isset( $link['url'] ) && is_string( $link['url'] ) ? $link['url'] : '';
		}

		// esc_url drops unsafe protocols such as javascript:.
		$url = '' !== $url ? esc_url( $url ) : '';
		if ( '' === $url ) {
			return $content;
		}

		$processor = new \WP_HTML_Tag_Processor( $content );
		if ( ! $processor->next_tag() ) {
			return $content;
		}
		$tag = strtolower( (string) $processor->get_tag() );
		$processor->add_class( 'gtfy-has-link' );
		$html = $processor->get_updated_html();

		// The root element is the first tag, so its closing tag is the last one.
		$position = strripos( $html, '</' . $tag . '>' );
		if ( false === $position ) {
			return $content;
		}

		$new_tab = ! empty( $link['newTab'] );
		$rel     = array();
		if ( $new_tab ) {
			$rel[] = 'noreferrer';
			$rel[] = 'noopener';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}

		$anchor = '<a class="gtfy-block-link" href="' . $url . '"'
			. ( $new_tab ? ' target="_blank"' : '' )
			. ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' )
			. '><span class="gtfy-block-link__label">' . esc_html( self::label( $link, $content, $url, $post_id ) ) . '</span></a>';

		wp_enqueue_style( self::handle() );

		return substr( $html, 0, $position ) . $anchor . substr( $html, $position );
	}
}

Block_Link::init();
