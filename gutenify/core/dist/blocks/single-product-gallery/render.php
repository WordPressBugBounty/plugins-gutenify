<?php
/**
 * Single Product Gallery render template.
 *
 * @package Gutenify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

// Only trust context/the current post as the product to show when it
// actually IS a product — otherwise a page/post that merely happens to
// contain this block (context always supplies *some* postId once
// do_blocks() runs on real page/post content, per usesContext) would
// silently defeat previewProductId, which exists precisely for that
// non-product-context case (both the editor's ServerSideRender preview
// and, previously incorrectly, the frontend).
$context_product_id = 0;

if ( isset( $block->context['postId'] ) && 'product' === get_post_type( absint( $block->context['postId'] ) ) ) {
	$context_product_id = absint( $block->context['postId'] );
}

if ( ! $context_product_id && ! empty( $attributes['previewProductId'] ) ) {
	$context_product_id = absint( $attributes['previewProductId'] );
}

if ( ! $context_product_id && 'product' === get_post_type() ) {
	$context_product_id = get_the_ID();
}
$gallery_product    = wc_get_product( $context_product_id );

if ( ! $gallery_product ) {
	if ( is_admin() || wp_is_json_request() ) {
		echo '<p class="gutenify--single-product-gallery__message">' . esc_html__( 'The gallery will appear when this block has a WooCommerce product context.', 'gutenify' ) . '</p>';
	}
	return;
}

$allowed_layouts = array( 'stack', 'grid-2', 'grid-3', 'feature-grid', 'feature-columns' );
$layout          = isset( $attributes['layout'] ) && in_array( $attributes['layout'], $allowed_layouts, true ) ? $attributes['layout'] : 'grid-2';
// Same preset values as core's Image block "Aspect ratio" control —
// whitelisted rather than passed through raw since this becomes a CSS
// custom property value below.
$allowed_aspect_ratios = array( 'auto', '1', '4/3', '3/2', '16/9', '3/4', '2/3', '9/16' );
// Default is "Square" (1), not "Original" (auto) — a real product's own
// gallery photos are rarely all the same natural size/orientation, so
// "Original" produced a visibly broken grid out of the box (mismatched
// image heights, large empty gaps next to a landscape photo). A uniform
// square crop is the standard e-commerce gallery default for exactly
// this reason (WooCommerce's own recommended product image size is
// itself square).
$aspect_ratio          = isset( $attributes['aspectRatio'] ) && in_array( $attributes['aspectRatio'], $allowed_aspect_ratios, true ) ? $attributes['aspectRatio'] : '1';
// Same three values as CSS's own `object-fit` keywords, matching core's
// Image block "Scale" control (Cover/Contain/Fill) 1:1 — no translation
// layer needed between the attribute and the CSS custom property below.
$allowed_scales = array( 'cover', 'contain', 'fill' );
$scale          = isset( $attributes['scale'] ) && in_array( $attributes['scale'], $allowed_scales, true ) ? $attributes['scale'] : 'cover';
// `focalPoint` mirrors core's Image block attribute shape ({x, y}, each
// 0-1) — clamped here since it becomes a CSS percentage below.
$focal_point   = isset( $attributes['focalPoint'] ) && is_array( $attributes['focalPoint'] ) ? $attributes['focalPoint'] : array();
$focal_point_x = isset( $focal_point['x'] ) ? min( 1, max( 0, (float) $focal_point['x'] ) ) : 0.5;
$focal_point_y = isset( $focal_point['y'] ) ? min( 1, max( 0, (float) $focal_point['y'] ) ) : 0.5;
$enable_lightbox = ! isset( $attributes['enableLightbox'] ) || ! empty( $attributes['enableLightbox'] );
$close_on_overlay_click = ! isset( $attributes['closeLightboxOnOverlayClick'] ) || ! empty( $attributes['closeLightboxOnOverlayClick'] );
$show_dots       = ! isset( $attributes['showDots'] ) || ! empty( $attributes['showDots'] );
$show_sale_badge = ! isset( $attributes['showSaleBadge'] ) || ! empty( $attributes['showSaleBadge'] );
$allowed_badge_alignments = array( 'left', 'center', 'right' );
$sale_badge_align = isset( $attributes['saleBadgeAlign'] ) && in_array( $attributes['saleBadgeAlign'], $allowed_badge_alignments, true ) ? $attributes['saleBadgeAlign'] : 'right';
$image_ids       = array_values(
	array_filter(
		array_unique(
			array_merge(
				array( absint( $gallery_product->get_image_id() ) ),
				array_map( 'absint', $gallery_product->get_gallery_image_ids() )
			)
		)
	)
);

if ( empty( $image_ids ) ) {
	if ( is_admin() || wp_is_json_request() ) {
		echo '<p class="gutenify--single-product-gallery__message">' . esc_html__( 'This product does not have gallery images yet.', 'gutenify' ) . '</p>';
	}
	return;
}

$sale_badge = $content;
if ( $show_sale_badge && '' === trim( $sale_badge ) && $gallery_product->is_on_sale() && class_exists( 'WP_Block' ) ) {
	$sale_badge_block = new WP_Block(
		array(
			'blockName' => 'woocommerce/product-sale-badge',
			'attrs'     => array( 'align' => $sale_badge_align ),
		),
		array( 'postId' => $gallery_product->get_id() )
	);
	$sale_badge = $sale_badge_block->render();
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'                 => 'gutenify--single-product-gallery wc-block-components-product-image is-layout-' . $layout,
		'style'                 => sprintf(
			'--single-product-gallery-aspect-ratio:%1$s;--single-product-gallery-scale:%2$s;--single-product-gallery-focal-x:%3$s%%;--single-product-gallery-focal-y:%4$s%%;',
			esc_attr( $aspect_ratio ),
			esc_attr( $scale ),
			esc_attr( $focal_point_x * 100 ),
			esc_attr( $focal_point_y * 100 )
		),
		'data-ff-product-gallery' => '',
		'data-ff-lightbox'       => $enable_lightbox ? 'true' : 'false',
		'data-ff-lightbox-overlay-close' => $close_on_overlay_click ? 'true' : 'false',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $show_sale_badge ) : ?>
		<?php echo $sale_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
	<div class="gutenify--single-product-gallery__viewport">
		<div class="gutenify--single-product-gallery__track" style="--single-product-gallery-secondary-count:<?php echo esc_attr( max( 1, count( $image_ids ) - 1 ) ); ?>">
			<?php foreach ( $image_ids as $index => $image_id ) : ?>
				<?php
				$full_image_src = wp_get_attachment_image_src( $image_id, 'full' );
				$full_image     = $full_image_src ? array(
					'src'    => $full_image_src[0],
					'width'  => $full_image_src[1],
					'height' => $full_image_src[2],
					'alt'    => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
				) : null;
				?>
				<figure class="gutenify--single-product-gallery__item">
					<?php if ( $enable_lightbox ) : ?>
						<button
							type="button"
							class="gutenify--single-product-gallery__image-button"
							data-ff-gallery-item="<?php echo esc_attr( $index ); ?>"
							data-ff-image-src="<?php echo esc_url( $full_image ? $full_image['src'] : '' ); ?>"
							data-ff-image-alt="<?php echo esc_attr( $full_image ? $full_image['alt'] : '' ); ?>"
							data-ff-image-width="<?php echo esc_attr( $full_image ? $full_image['width'] : 0 ); ?>"
							data-ff-image-height="<?php echo esc_attr( $full_image ? $full_image['height'] : 0 ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Open product image %1$d of %2$d', 'gutenify' ), $index + 1, count( $image_ids ) ) ); ?>"
						>
							<?php echo wp_get_attachment_image( $image_id, 'woocommerce_single', false, array( 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					<?php else : ?>
						<?php echo wp_get_attachment_image( $image_id, 'woocommerce_single', false, array( 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="gutenify--single-product-gallery__mobile-ui">
		<div class="gutenify--single-product-gallery__counter" role="status" aria-live="polite">
			<span class="screen-reader-text"><?php esc_html_e( 'Image', 'gutenify' ); ?></span>
			<span data-ff-gallery-current>1</span>
			<span aria-hidden="true">/</span>
			<span class="screen-reader-text"><?php esc_html_e( 'of', 'gutenify' ); ?></span>
			<span><?php echo esc_html( count( $image_ids ) ); ?></span>
		</div>

		<?php if ( $show_dots && count( $image_ids ) > 1 ) : ?>
			<div class="gutenify--single-product-gallery__dots" role="group" aria-label="<?php esc_attr_e( 'Choose product image', 'gutenify' ); ?>">
				<?php foreach ( $image_ids as $index => $image_id ) : // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable ?>
					<button
						type="button"
						class="gutenify--single-product-gallery__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-ff-gallery-dot="<?php echo esc_attr( $index ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Show image %d', 'gutenify' ), $index + 1 ) ); ?>"
						aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
