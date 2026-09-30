<?php
/**
 * Term Thumbnail render template.
 *
 * @package Gutenify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gutenify_term_thumbnail_get_border_attributes' ) ) {
	/**
	 * Builds border classnames/styles for this block from its own `style`
	 * attribute, mirroring core's own (Gutenberg-plugin)
	 * `gutenberg_get_block_core_post_featured_image_border_attributes()`.
	 *
	 * block.json's `__experimentalBorder` support declares
	 * `__experimentalSkipSerialization: true`, so WordPress never
	 * auto-applies the border to `get_block_wrapper_attributes()`'s outer
	 * wrapper `<div>` — without that flag the rounded corners/width/color
	 * only ever showed on the invisible wrapper, never on the `<img>` (or
	 * the dim-overlay `<span>`) sitting inside it. This re-applies border
	 * support manually to the actual image/overlay elements below, using
	 * the stable core `wp_style_engine_get_styles()` (not the
	 * Gutenberg-plugin-only `gutenberg_style_engine_get_styles()`, which
	 * only exists here because the Gutenberg plugin happens to be checked
	 * out for reference on this dev site).
	 *
	 * Declared as a plain guarded function (not a class method/namespaced
	 * function) because this file is a render template, not a class, and
	 * `render.php` can be `include`d more than once per request — a Terms
	 * Query Loop renders this block once per term, so a second `include`
	 * of this same file within the same request is the normal case here,
	 * not an edge case.
	 *
	 * @param array $attributes Block attributes.
	 * @return array{class?: string, style?: string} Border classnames/styles, if any were set.
	 */
	function gutenify_term_thumbnail_get_border_attributes( $attributes ) {
		$border_styles = array();
		$sides         = array( 'top', 'right', 'bottom', 'left' );

		if ( isset( $attributes['style']['border']['radius'] ) ) {
			$border_styles['radius'] = $attributes['style']['border']['radius'];
		}

		if ( isset( $attributes['style']['border']['style'] ) ) {
			$border_styles['style'] = $attributes['style']['border']['style'];
		}

		if ( isset( $attributes['style']['border']['width'] ) ) {
			$border_styles['width'] = $attributes['style']['border']['width'];
		}

		$preset_color           = ! empty( $attributes['borderColor'] ) ? 'var:preset|color|' . $attributes['borderColor'] : null;
		$custom_color           = $attributes['style']['border']['color'] ?? null;
		$border_styles['color'] = $preset_color ? $preset_color : $custom_color;

		// Individual per-side overrides (top/right/bottom/left), same shape
		// core's own border block-support stores them in.
		foreach ( $sides as $side ) {
			$side_border            = $attributes['style']['border'][ $side ] ?? null;
			$border_styles[ $side ] = array(
				'color' => $side_border['color'] ?? null,
				'style' => $side_border['style'] ?? null,
				'width' => $side_border['width'] ?? null,
			);
		}

		$styles = wp_style_engine_get_styles( array( 'border' => $border_styles ) );

		$border_attributes = array();
		if ( ! empty( $styles['classnames'] ) ) {
			$border_attributes['class'] = $styles['classnames'];
		}
		if ( ! empty( $styles['css'] ) ) {
			$border_attributes['style'] = $styles['css'];
		}
		return $border_attributes;
	}
}

// Both the classic-editor context (is_admin()) and ServerSideRender's own
// block-renderer REST call (wp_is_json_request()) render this template for
// editor-canvas preview purposes, never as real frontend output. Every
// editor-only branch below (missing-term messages, and the link-suppression
// in Task 1) keys off this single flag rather than repeating the same
// condition three times.
$is_editor_context = is_admin() || wp_is_json_request();

// core/term-template injects `termId`/`taxonomy` into block context for
// every child block rendered inside its loop (same contract core's own
// core/term-name and core/term-count consume).
$term_id  = isset( $block->context['termId'] ) ? absint( $block->context['termId'] ) : 0;
$taxonomy = isset( $block->context['taxonomy'] ) ? sanitize_key( $block->context['taxonomy'] ) : '';

// The editor's ServerSideRender preview calls the block-renderer REST
// endpoint, which does not forward block context — only attributes. When
// this block sits inside a real Term Template in the editor canvas,
// Gutenberg's own BlockContextProvider still supplies `context` to this
// file's caller (edit.js), which mirrors it into these preview attributes
// so the SSR request can reconstruct the same term. Mirrors
// single-product-gallery's previewProductId pattern.
if ( ! $term_id && ! empty( $attributes['previewTermId'] ) ) {
	$term_id  = absint( $attributes['previewTermId'] );
	$taxonomy = ! empty( $attributes['previewTaxonomy'] ) ? sanitize_key( $attributes['previewTaxonomy'] ) : $taxonomy;
}

if ( ! $term_id ) {
	if ( $is_editor_context ) {
		echo '<p class="gutenify--term-thumbnail__message">' . esc_html__( 'This block must be placed inside a Terms Query Loop (Term Template) so it can receive a term.', 'gutenify' ) . '</p>';
	}
	return;
}

$term = get_term( $term_id, $taxonomy );

if ( ! $term || is_wp_error( $term ) ) {
	if ( $is_editor_context ) {
		echo '<p class="gutenify--term-thumbnail__message">' . esc_html__( 'This term could not be found.', 'gutenify' ) . '</p>';
	}
	return;
}

// get_term() already resolves the real taxonomy from the term object even
// when the `$taxonomy` arg above was empty/wrong — use its value from here
// on for the WooCommerce fallback check and the term link below.
$taxonomy = $term->taxonomy;

$allowed_sizes = array_values(
	array_unique(
		array_merge( array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), get_intermediate_image_sizes() )
	)
);
$image_size = isset( $attributes['imageSize'] ) && in_array( $attributes['imageSize'], $allowed_sizes, true ) ? $attributes['imageSize'] : 'thumbnail';

// Same three values as CSS's own `object-fit` keywords, matching core's
// Image block "Scale" control (Cover/Contain/Fill) 1:1.
$allowed_scales = array( 'cover', 'contain', 'fill' );
$scale          = isset( $attributes['scale'] ) && in_array( $attributes['scale'], $allowed_scales, true ) ? $attributes['scale'] : 'cover';

// Same preset values as core's Image block "Aspect ratio" control —
// whitelisted rather than passed through raw since this becomes a CSS
// custom property value below.
//
// Default is "Square" (1), not "Original" (auto) — this block is meant
// for a Terms Query Loop grid, where each term's own thumbnail almost
// certainly has a different natural size/orientation (different source
// photos per category). "Original" produced a visibly broken grid out
// of the box; a uniform square crop is the standard choice for exactly
// this reason.
$allowed_aspect_ratios = array( 'auto', '1', '4/3', '3/2', '16/9', '3/4', '2/3', '9/16' );
$aspect_ratio          = isset( $attributes['aspectRatio'] ) ? (string) $attributes['aspectRatio'] : '1';
if ( ! in_array( $aspect_ratio, $allowed_aspect_ratios, true ) ) {
	$aspect_ratio = '1';
}

// `focalPoint` mirrors core's Image block attribute shape ({x, y}, each
// 0-1) — clamped here since it becomes a CSS percentage below.
$focal_point   = isset( $attributes['focalPoint'] ) && is_array( $attributes['focalPoint'] ) ? $attributes['focalPoint'] : array();
$focal_point_x = isset( $focal_point['x'] ) ? min( 1, max( 0, (float) $focal_point['x'] ) ) : 0.5;
$focal_point_y = isset( $focal_point['y'] ) ? min( 1, max( 0, (float) $focal_point['y'] ) ) : 0.5;

$link_to_term = ! isset( $attributes['linkToTerm'] ) || ! empty( $attributes['linkToTerm'] );

// Border support is skip-serialized in block.json (see the helper above),
// so it never lands on get_block_wrapper_attributes()'s wrapper <div>
// below — apply it directly to the <img>/placeholder <img> (and, further
// down, the dim-overlay <span>) instead, same as core's own Post Featured
// Image block does for its <img>/overlay pair.
$border_attributes = gutenify_term_thumbnail_get_border_attributes( $attributes );
$border_class      = ! empty( $border_attributes['class'] ) ? (string) $border_attributes['class'] : '';
$border_style      = ! empty( $border_attributes['style'] ) ? (string) $border_attributes['style'] : '';

// Same WooCommerce term-thumbnail meta convention category-list already
// reads.
$image_id   = absint( get_term_meta( $term_id, 'thumbnail_id', true ) );
$image_html = '';

if ( $image_id ) {
	$attachment_title = get_the_title( $image_id );
	$image_attr       = array(
		'loading' => 'lazy',
		'alt'     => $attachment_title ? $attachment_title : $term->name,
	);
	// wp_get_attachment_image() esc_attr()'s every $attr value itself
	// before echoing, so class/style don't need re-escaping here — only
	// merged in when actually set, so the default attachment/size classes
	// are left untouched when there's no border to apply.
	if ( $border_class ) {
		$image_attr['class'] = $border_class;
	}
	if ( $border_style ) {
		$image_attr['style'] = $border_style;
	}
	$image_html = wp_get_attachment_image( $image_id, $image_size, false, $image_attr );
} elseif ( function_exists( 'wc_placeholder_img' ) && 'product_cat' === $taxonomy ) {
	// Generic, not WooCommerce-gated: this fallback only fires for the one
	// case category-list already handles the same way — a product_cat term
	// with no thumbnail_id meta, while WooCommerce is active. Any other
	// taxonomy with no thumbnail simply renders nothing below, matching
	// core's own Post Featured Image block when a post has no featured
	// image.
	//
	// wc_placeholder_img()'s own default $attr (`wp_parse_args( $attr,
	// $default_attr )`) lets a passed-in `class` key win outright rather
	// than merging strings, so its default
	// `woocommerce-placeholder wp-post-image` class has to be concatenated
	// in here explicitly or it's silently lost the moment a border class
	// exists. wc_placeholder_img() also esc_attr()'s every attribute
	// itself before echoing, same as wp_get_attachment_image() above.
	$placeholder_attr = array(
		'class' => trim( 'woocommerce-placeholder wp-post-image' . ( $border_class ? ' ' . $border_class : '' ) ),
	);
	if ( $border_style ) {
		$placeholder_attr['style'] = $border_style;
	}
	$image_html = wc_placeholder_img( $image_size, $placeholder_attr );
}

if ( '' === $image_html ) {
	if ( $is_editor_context ) {
		echo '<p class="gutenify--term-thumbnail__message">' . esc_html__( 'This term does not have a thumbnail image.', 'gutenify' ) . '</p>';
	}
	return;
}

$has_aspect_ratio = 'auto' !== $aspect_ratio;
$style            = '--term-thumbnail-scale:' . esc_attr( $scale ) . ';';
if ( $has_aspect_ratio ) {
	$style .= sprintf(
		'--term-thumbnail-aspect-ratio:%1$s;--term-thumbnail-focal-x:%2$s%%;--term-thumbnail-focal-y:%3$s%%;',
		esc_attr( $aspect_ratio ),
		esc_attr( $focal_point_x * 100 ),
		esc_attr( $focal_point_y * 100 )
	);
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'gutenify--term-thumbnail' . ( $has_aspect_ratio ? ' has-aspect-ratio' : '' ),
		'style' => $style,
	)
);

// A link is never rendered for an editor-context render (classic-editor
// is_admin(), or ServerSideRender's block-renderer REST call) regardless of
// the `linkToTerm` attribute's value — clicking a link inside the editor
// canvas is bad UX (nested-link/click-target problems), and core's own
// Post Featured Image block never renders its link in the editor either,
// only on the real frontend. get_term_link() is also skipped entirely in
// that case since its result would never be used.
$term_link = ( ! $is_editor_context && $link_to_term ) ? get_term_link( $term ) : '';
$has_link  = ! $is_editor_context && $link_to_term && $term_link && ! is_wp_error( $term_link );

$link_target = isset( $attributes['linkTarget'] ) && '_blank' === $attributes['linkTarget'] ? '_blank' : '_self';
$link_rel    = isset( $attributes['rel'] ) ? (string) $attributes['rel'] : '';

// Overlay: dim/color-or-gradient layer on top of the image, matching core's
// own Post Featured Image "Overlay" panel (dimRatio/overlayColor/
// customOverlayColor/gradient/customGradient). Snapped to the nearest 10 to
// match the discrete has-background-dim-{n} opacity classes shipped in
// style.scss (also guards against an out-of-step value ever reaching here
// via a raw REST/attributes payload, since RangeControl's step is only a
// display hint).
$dim_ratio = isset( $attributes['dimRatio'] ) ? absint( $attributes['dimRatio'] ) : 0;
$dim_ratio = (int) ( round( $dim_ratio / 10 ) * 10 );
$dim_ratio = max( 0, min( 100, $dim_ratio ) );

$overlay_color        = isset( $attributes['overlayColor'] ) ? sanitize_html_class( $attributes['overlayColor'] ) : '';
$custom_overlay_color = isset( $attributes['customOverlayColor'] ) ? (string) $attributes['customOverlayColor'] : '';
$gradient_slug        = isset( $attributes['gradient'] ) ? sanitize_html_class( $attributes['gradient'] ) : '';
$custom_gradient      = isset( $attributes['customGradient'] ) ? (string) $attributes['customGradient'] : '';

$overlay_html = '';

if ( $dim_ratio > 0 ) {
	$overlay_classes = array( 'gutenify--term-thumbnail__overlay', 'has-background-dim', 'has-background-dim-' . $dim_ratio );
	$overlay_styles  = array();

	if ( $overlay_color ) {
		$overlay_classes[] = 'has-' . $overlay_color . '-background-color';
	}

	if ( $gradient_slug || $custom_gradient ) {
		$overlay_classes[] = 'has-background-gradient';
	}

	if ( $gradient_slug ) {
		$overlay_classes[] = 'has-' . $gradient_slug . '-gradient-background';
	}

	// Give the overlay the same border radius/width/color as the image
	// beneath it — otherwise a square overlay pokes out past the image's
	// rounded corners, same fix core's own Post Featured Image block
	// applies via its overlay markup builder.
	if ( $border_class ) {
		$overlay_classes[] = $border_class;
	}
	if ( $border_style ) {
		$overlay_styles[] = $border_style;
	}

	// Mirrors core's own overlay markup builder: build the raw declarations
	// first, then run the combined string through safecss_filter_attr()
	// (rejects anything that isn't a safe CSS value, e.g. a javascript: URI
	// smuggled into a gradient) before esc_attr()'ing the whole style
	// attribute — the same two-layer defense already used above for
	// aspect-ratio/height/width/object-fit.
	if ( $custom_gradient ) {
		$overlay_styles[] = 'background-image:' . $custom_gradient . ';';
	} elseif ( $custom_overlay_color ) {
		$overlay_styles[] = 'background-color:' . $custom_overlay_color . ';';
	}

	$overlay_html = sprintf(
		'<span class="%1$s" style="%2$s" aria-hidden="true"></span>',
		esc_attr( implode( ' ', $overlay_classes ) ),
		esc_attr( safecss_filter_attr( implode( '', $overlay_styles ) ) )
	);
}

$image_html .= $overlay_html;
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $has_link ) : ?>
		<a class="gutenify--term-thumbnail__link" href="<?php echo esc_url( $term_link ); ?>"<?php echo '_blank' === $link_target ? ' target="_blank" rel="' . esc_attr( $link_rel ) . '"' : ''; ?>>
			<span class="gutenify--term-thumbnail__image"><?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	<?php else : ?>
		<span class="gutenify--term-thumbnail__image"><?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php endif; ?>
</div>
