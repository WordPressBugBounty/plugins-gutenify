<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Popup {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! self::is_active() ) {
			return;
		}

		register_block_type(
			__DIR__,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	/**
	 * Whether this PRO-origin block should register — see
	 * Helpers::is_pro_block_active() for the actual PRO-active-or-theme-
	 * support check and why it must run on `init` or later.
	 *
	 * @return bool
	 */
	private static function is_active() {
		return Helpers::is_pro_block_active();
	}

	/**
	 * save() (see save.js) still emits the complete, already-correct markup —
	 * trigger button, popup wrapper, and inner blocks via
	 * `<InnerBlocks.Content />` — for a single, standalone instance of this
	 * block. That's fine when the block only ever appears once per page, but
	 * inside a Post Template / Query Loop the identical saved HTML is
	 * rendered once per post, so every post ends up with the SAME popup DOM
	 * id and the SAME `data-popup-id` selector. jQuery's `$( popupId )`
	 * (used by view-script.js via Magnific Popup) then always resolves to
	 * the FIRST match on the page, so every post's button opens post #1's
	 * popup content.
	 *
	 * This callback does not regenerate any markup (all the per-instance
	 * button/popup styling logic stays in save.js, untouched) — it only
	 * swaps the popup id / data-popup-id selector inside the already
	 * rendered $content so each post gets its own unique pairing.
	 *
	 * Outside of any resolvable post ($post_id stays 0 — e.g. a block
	 * rendered somewhere with no current/global post and no `postId`
	 * block context) $content is returned completely untouched, so a
	 * previously-saved, standalone quick-view block keeps rendering the
	 * exact same DOM id / classes / structure as before this fix.
	 */
	public static function render_callback( $attributes, $content, $block = null ) {
		$block_client_id = ! empty( $attributes['blockClientId'] ) ? $attributes['blockClientId'] : '';

		if ( '' === $block_client_id || empty( $content ) ) {
			return $content;
		}

		$post_id = 0;

		if ( $block && isset( $block->context['postId'] ) && $block->context['postId'] ) {
			$post_id = (int) $block->context['postId'];
		} elseif ( get_the_ID() ) {
			$post_id = (int) get_the_ID();
		}

		if ( ! $post_id ) {
			return $content;
		}

		$base_id = 'gutenify-quick-view-content-' . $block_client_id;
		$new_id  = $base_id . '-' . $post_id;

		return str_replace(
			array(
				'id="' . $base_id . '"',
				'data-popup-id="#' . $base_id . '"',
			),
			array(
				'id="' . $new_id . '"',
				'data-popup-id="#' . $new_id . '"',
			),
			$content
		);
	}
}

Popup::init();
