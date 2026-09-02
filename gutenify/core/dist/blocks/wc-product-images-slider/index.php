<?php

defined( 'ABSPATH' ) || exit;

/**
 * Resolve which product this block instance should render.
 *
 * @param array    $attributes Block attributes.
 * @param WP_Block $block      Block instance (gives access to query-loop context).
 * @return WC_Product|null
 */
function gutenify_block_wc_product_images_slider_resolve_product( $attributes, $block ) {
	$product_id = 0;

	if ( isset( $block->context['postId'] ) && 'product' === get_post_type( $block->context['postId'] ) ) {
		$product_id = absint( $block->context['postId'] );
	} elseif ( ! empty( $attributes['productId'] ) ) {
		$product_id = absint( $attributes['productId'] );
	}

	if ( ! $product_id ) {
		return null;
	}

	$product = wc_get_product( $product_id );

	return $product instanceof WC_Product ? $product : null;
}

/**
 * Build the slide list (main + thumb image src) for a product.
 *
 * @param WC_Product $product
 * @param array      $attributes Block attributes (showFeaturedImage, imageCount).
 * @return array
 */
function gutenify_block_wc_product_images_slider_get_slides( $product, $attributes = array() ) {
	$show_featured_image = ! isset( $attributes['showFeaturedImage'] ) || ! empty( $attributes['showFeaturedImage'] );

	$image_ids = $show_featured_image
		? array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() )
		: $product->get_gallery_image_ids();

	$image_ids = array_unique( array_filter( $image_ids ) );

	// Fall back to the featured image if the product has no gallery images to show.
	if ( empty( $image_ids ) && ! $show_featured_image ) {
		$image_ids = array_filter( array( $product->get_image_id() ) );
	}

	if ( ! empty( $attributes['imageCount'] ) ) {
		$image_ids = array_slice( $image_ids, 0, absint( $attributes['imageCount'] ) );
	}

	$slides = array();

	foreach ( $image_ids as $attachment_id ) {
		$full  = wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' );
		$thumb = wp_get_attachment_image_src( $attachment_id, 'woocommerce_gallery_thumbnail' );

		if ( ! $full ) {
			continue;
		}

		$slides[] = array(
			'id'    => $attachment_id,
			'full'  => $full[0],
			'thumb' => $thumb ? $thumb[0] : $full[0],
			'alt'   => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		);
	}

	return $slides;
}

function gutenify_block_wc_product_images_slider_render( $attributes, $content, $block ) {
	if ( ! class_exists( 'woocommerce' ) ) {
		return '';
	}

	$product = gutenify_block_wc_product_images_slider_resolve_product( $attributes, $block );

	if ( ! $product ) {
		return '';
	}

	$slides = gutenify_block_wc_product_images_slider_get_slides( $product, $attributes );

	if ( empty( $slides ) ) {
		return '';
	}

	// Arrows, pagination and thumbnails all require more than one slide to do anything.
	$has_multiple_slides = count( $slides ) > 1;
	$has_navigation   = ! empty( $attributes['hasNavigation'] ) && $has_multiple_slides;
	$pagination_type  = $has_multiple_slides && ! empty( $attributes['paginationType'] ) ? $attributes['paginationType'] : 'none';
	$has_thumbnails   = ! empty( $attributes['hasThumbnails'] ) && $has_multiple_slides;
	// Fade always shows exactly one slide at a time, regardless of the stored setting.
	$slides_per_view  = 'fade' === $attributes['effect'] ? 1 : max( 1, absint( $attributes['slidesPerView'] ?? 1 ) );
	$thumbs_position  = ! empty( $attributes['thumbsPosition'] ) ? $attributes['thumbsPosition'] : 'bottom';
	$has_zoom         = ! empty( $attributes['hasZoom'] );
	$link_to_product  = ! empty( $attributes['linkToProduct'] );
	// Images already link out to the product page, so the fullscreen lightbox never applies.
	$has_lightbox     = ! empty( $attributes['hasLightbox'] ) && ! $link_to_product;
	$is_variable      = $product->is_type( 'variable' );
	$has_variation_sync = ! empty( $attributes['hasVariationSync'] ) && $is_variable && is_product();
	$has_keyboard_nav = ! empty( $attributes['hasKeyboardNav'] );
	$has_mousewheel   = ! empty( $attributes['hasMousewheel'] );
	$thumbs_hover_swap = ! empty( $attributes['thumbsHoverSwap'] );
	$is_sticky        = ! empty( $attributes['isSticky'] );

	$wrapper_classes = array(
		'gutenify--wc-product-images-slider',
		'gutenify--wc-product-images-slider-thumbs-' . esc_attr( $thumbs_position ),
	);

	if ( ! $has_thumbnails ) {
		$wrapper_classes[] = 'gutenify--wc-product-images-slider-no-thumbs';
	}

	$wrapper_extra_attrs = array(
		'class' => implode( ' ', $wrapper_classes ),
	);

	if ( $is_sticky ) {
		$wrapper_classes[] = 'gutenify--wc-product-images-slider-sticky';
		$wrapper_extra_attrs['class'] = implode( ' ', $wrapper_classes );
		$wrapper_extra_attrs['style'] = sprintf(
			'--liger-images-slider-sticky-offset:%dpx;',
			absint( $attributes['stickyOffset'] )
		);
	}

	$wrapper_attributes = get_block_wrapper_attributes( $wrapper_extra_attrs );

	$aspect_ratio       = ! empty( $attributes['aspectRatio'] ) ? $attributes['aspectRatio'] : 'auto';
	$aspect_ratio_style = 'auto' !== $aspect_ratio ? sprintf( ' style="--liger-images-slider-aspect-ratio:%s;"', esc_attr( $aspect_ratio ) ) : '';
	$product_permalink  = $link_to_product ? get_the_permalink( $product->get_id() ) : '';

	ob_start();

	printf( '<div %s>', $wrapper_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	printf(
		'<div class="gutenify--wc-product-images-slider__main swiper" data-effect="%1$s" data-loop="%2$s" data-autoplay="%3$s" data-autoplay-speed="%4$s" data-autoplay-hover="%5$s" data-space-between="%6$s" data-slides-per-view="%7$s" data-navigation="%8$s" data-pagination="%9$s" data-zoom="%10$s" data-lightbox="%11$s" data-variation-sync="%12$s" data-product-id="%13$s" data-keyboard="%14$s" data-mousewheel="%15$s" data-link-to-product="%16$s" data-product-permalink="%17$s"%18$s>',
		esc_attr( $attributes['effect'] ),
		esc_attr( $attributes['loop'] ? '1' : '0' ),
		esc_attr( $attributes['isAutoplay'] ? '1' : '0' ),
		esc_attr( absint( $attributes['autoplaySpeed'] ) ),
		esc_attr( ! empty( $attributes['autoplayOnHover'] ) ? '1' : '0' ),
		esc_attr( absint( $attributes['spaceBetween'] ) ),
		esc_attr( $slides_per_view ),
		esc_attr( $has_navigation ? '1' : '0' ),
		esc_attr( $pagination_type ),
		esc_attr( $has_zoom ? '1' : '0' ),
		esc_attr( $has_lightbox ? '1' : '0' ),
		esc_attr( $has_variation_sync ? '1' : '0' ),
		esc_attr( $product->get_id() ),
		esc_attr( $has_keyboard_nav ? '1' : '0' ),
		esc_attr( $has_mousewheel ? '1' : '0' ),
		esc_attr( $link_to_product ? '1' : '0' ),
		esc_attr( $product_permalink ),
		$aspect_ratio_style // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped above.
	);

	echo '<div class="swiper-wrapper">';
	foreach ( $slides as $slide ) {
		echo '<div class="swiper-slide">';
		echo '<div class="swiper-zoom-container">';
		if ( $link_to_product ) {
			printf( '<a href="%s">', esc_url( $product_permalink ) );
		}
		printf(
			'<img src="%1$s" data-full="%1$s" alt="%2$s" loading="lazy" />',
			esc_url( $slide['full'] ),
			esc_attr( $slide['alt'] )
		);
		if ( $link_to_product ) {
			echo '</a>';
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>'; // .swiper-wrapper

	if ( $has_navigation ) {
		echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
	}

	if ( 'none' !== $pagination_type ) {
		echo '<div class="swiper-pagination"></div>';
	}

	if ( ! empty( $attributes['hasOverlay'] ) && ! empty( trim( (string) $content ) ) ) {
		$overlay_orientation   = 'stack' === ( $attributes['overlayOrientation'] ?? '' ) ? 'column' : 'row';
		$overlay_justification = ! empty( $attributes['overlayJustification'] ) ? $attributes['overlayJustification'] : 'center';
		$overlay_alignment     = ! empty( $attributes['overlayAlignment'] ) ? $attributes['overlayAlignment'] : 'center';

		printf(
			'<div class="gutenify--wc-product-images-slider__overlay" style="flex-direction:%s;justify-content:%s;align-items:%s;">',
			esc_attr( $overlay_orientation ),
			esc_attr( $overlay_justification ),
			esc_attr( $overlay_alignment )
		);
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already-rendered inner block HTML from WP core.
		echo '</div>';
	}

	echo '</div>'; // .gutenify--wc-product-images-slider__main

	if ( $has_thumbnails ) {
		printf(
			'<div class="gutenify--wc-product-images-slider__thumbs swiper" data-space-between="%1$s" data-count="%2$s" data-direction="%3$s" data-hover-swap="%4$s">',
			esc_attr( absint( $attributes['thumbsSpaceBetween'] ) ),
			esc_attr( absint( $attributes['thumbsCount'] ) ),
			esc_attr( 'left' === $thumbs_position || 'right' === $thumbs_position ? 'vertical' : 'horizontal' ),
			esc_attr( $thumbs_hover_swap ? '1' : '0' )
		);
		echo '<div class="swiper-wrapper">';
		foreach ( $slides as $slide ) {
			printf(
				'<div class="swiper-slide"><img src="%1$s" alt="%2$s" loading="lazy" /></div>',
				esc_url( $slide['thumb'] ),
				esc_attr( $slide['alt'] )
			);
		}
		echo '</div>'; // .swiper-wrapper
		echo '</div>'; // .gutenify--wc-product-images-slider__thumbs
	}

	if ( $has_lightbox ) {
		echo '<div class="gutenify--wc-product-images-slider__lightbox" hidden>';
		echo '<button type="button" class="gutenify--wc-product-images-slider__lightbox-close" aria-label="' . esc_attr__( 'Close', 'liger-pro' ) . '">&times;</button>';
		echo '<div class="gutenify--wc-product-images-slider__lightbox-swiper swiper">';
		echo '<div class="swiper-wrapper"></div>';
		echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
		echo '</div>';
		echo '</div>'; // .gutenify--wc-product-images-slider__lightbox
	}

	echo '</div>'; // wrapper

	return ob_get_clean();
}

/**
 * Inject each variation's own gallery images into the `woocommerce_available_variation`
 * payload, since WooCommerce core only ever sends the single variation image.
 *
 * @param array               $variation_data
 * @param WC_Product          $product
 * @param WC_Product_Variation $variation
 * @return array
 */
function gutenify_block_wc_product_images_slider_inject_variation_gallery( $variation_data, $product, $variation ) {
	$image_ids = array_filter( array_merge( array( $variation->get_image_id() ), $variation->get_gallery_image_ids() ) );

	// No variation-specific images: tell the frontend to fall back to the parent gallery.
	if ( empty( $image_ids ) || (int) $variation->get_image_id() === (int) $product->get_image_id() ) {
		$variation_data['liger_gallery'] = array();
		return $variation_data;
	}

	$slides = array();
	foreach ( array_unique( $image_ids ) as $attachment_id ) {
		$full  = wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' );
		$thumb = wp_get_attachment_image_src( $attachment_id, 'woocommerce_gallery_thumbnail' );

		if ( ! $full ) {
			continue;
		}

		// Unlike the initial-render $slides array above (escaped at echo
		// time, index.php lines ~163/208), this one ships straight to the
		// browser as JSON via WooCommerce's found_variation event and is
		// concatenated into HTML by view.js — escape here since there is
		// no later escaping step on that path.
		$slides[] = array(
			'id'    => $attachment_id,
			'full'  => esc_url( $full[0] ),
			'thumb' => esc_url( $thumb ? $thumb[0] : $full[0] ),
			'alt'   => esc_attr( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
		);
	}

	$variation_data['liger_gallery'] = $slides;

	return $variation_data;
}
add_filter( 'woocommerce_available_variation', 'gutenify_block_wc_product_images_slider_inject_variation_gallery', 10, 3 );

/**
 * Per-instance style (colors/border), following the same convention as
 * gutenify/wc-product-carousel.
 */
function gutenify_block_wc_product_images_slider_render_block( $block_content, $block, $instance, $block_id ) {
	$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
	$inner = isset( $attrs['blockAdvanceOptions']['innerBlock'] ) ? $attrs['blockAdvanceOptions']['innerBlock'] : array();

	$root_selector = '.' . $block_id;
	$css           = '';

	if ( ! empty( $attrs['backgroundGradient'] ) ) {
		$css .= $root_selector . '{background:' . $attrs['backgroundGradient'] . ';}';
	} elseif ( ! empty( $attrs['backgroundColor'] ) ) {
		$css .= $root_selector . '{background-color:' . $attrs['backgroundColor'] . ';}';
	}

	// Border Color/Width/Radius all live under blockAdvanceOptions.innerBlock
	// (BoxControl for width, BorderRadiusControl for radius — see
	// inspector/tab-style.js). A border-color/-width with no border-style
	// renders nothing at all (CSS defaults border-style to none), so style
	// is forced to solid whenever either is actually set.
	$border_css = '';

	if ( ! empty( $inner['borderColor'] ) ) {
		$border_css .= 'border-color:' . $inner['borderColor'] . ';';
	}

	if ( ! empty( $inner['borderWidth'] ) && is_array( $inner['borderWidth'] ) ) {
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			if ( ! empty( $inner['borderWidth'][ $side ] ) ) {
				$border_css .= 'border-' . $side . '-width:' . $inner['borderWidth'][ $side ] . ';';
			}
		}
	}

	if ( '' !== $border_css ) {
		$border_css .= 'border-style:solid;';
		$css        .= $root_selector . ' .swiper-slide{' . $border_css . '}';
	}

	// BorderRadiusControl's value is either a single linked string (e.g.
	// '10px') or, once unlinked per-corner, an object keyed by corner name.
	if ( ! empty( $inner['borderRadius'] ) ) {
		$radius     = $inner['borderRadius'];
		$radius_css = '';

		if ( is_string( $radius ) ) {
			$radius_css = 'border-radius:' . $radius . ';';
		} elseif ( is_array( $radius ) ) {
			$corner_props = array(
				'topLeft'     => 'border-top-left-radius',
				'topRight'    => 'border-top-right-radius',
				'bottomLeft'  => 'border-bottom-left-radius',
				'bottomRight' => 'border-bottom-right-radius',
			);
			foreach ( $corner_props as $corner => $prop ) {
				if ( ! empty( $radius[ $corner ] ) ) {
					$radius_css .= $prop . ':' . $radius[ $corner ] . ';';
				}
			}
		}

		if ( '' !== $radius_css ) {
			$css .= $root_selector . ' .swiper-slide{' . $radius_css . '}';
		}
	}

	if ( '' !== $css ) {
		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css );
	}

	return $block_content;
}
add_filter( 'gutenify_render_block_gutenify/wc-product-images-slider', 'gutenify_block_wc_product_images_slider_render_block', 10, 4 );

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context. PRO-origin block hosted in this lite framework —
 * see Helpers::is_pro_block_active() for the PRO-active-or-theme-support gate.
 */
function gutenify_register_block_wc_product_images_slider() {
	if ( ! \gutenify\Helpers::is_pro_block_active() ) {
		return;
	}

	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'gutenify_block_wc_product_images_slider_render',
		)
	);
}
add_action( 'init', 'gutenify_register_block_wc_product_images_slider' );
