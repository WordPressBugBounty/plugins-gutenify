<?php
/**
 * Product Category List render template.
 *
 * @package Gutenify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

$selected_ids    = isset( $attributes['categoryIds'] ) ? array_values( array_filter( array_map( 'absint', (array) $attributes['categoryIds'] ) ) ) : array();
$include_children = ! isset( $attributes['includeChildren'] ) || ! empty( $attributes['includeChildren'] );
$term_ids        = $selected_ids;

if ( $include_children ) {
	foreach ( $selected_ids as $selected_id ) {
		$child_ids = get_term_children( $selected_id, 'product_cat' );
		if ( ! is_wp_error( $child_ids ) ) {
			$term_ids = array_merge( $term_ids, $child_ids );
		}
	}
}

$term_ids = array_values( array_unique( array_map( 'absint', $term_ids ) ) );
$terms    = array();
foreach ( $term_ids as $term_id ) {
	$term = get_term( $term_id, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$terms[] = $term;
	}
}

if ( empty( $terms ) ) {
	if ( is_admin() || wp_is_json_request() ) {
		echo '<p class="gutenify--category-list__message">' . esc_html__( 'Select one or more product categories.', 'gutenify' ) . '</p>';
	}
	return;
}

$layout          = isset( $attributes['layout'] ) && 'list' === $attributes['layout'] ? 'list' : 'grid';
$desktop_columns = isset( $attributes['desktopColumns'] ) ? max( 1, min( 6, absint( $attributes['desktopColumns'] ) ) ) : 4;
$tablet_columns  = isset( $attributes['tabletColumns'] ) ? max( 1, min( 4, absint( $attributes['tabletColumns'] ) ) ) : 2;
$mobile_columns  = isset( $attributes['mobileColumns'] ) ? max( 1, min( 2, absint( $attributes['mobileColumns'] ) ) ) : 1;
$show_image      = ! isset( $attributes['showImage'] ) || ! empty( $attributes['showImage'] );
$show_name       = ! isset( $attributes['showName'] ) || ! empty( $attributes['showName'] );
$show_description = ! empty( $attributes['showDescription'] );
$show_count      = ! empty( $attributes['showCount'] );
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'gutenify--category-list is-layout-' . $layout,
		'style' => '--category-list-columns-desktop:' . $desktop_columns . ';--category-list-columns-tablet:' . $tablet_columns . ';--category-list-columns-mobile:' . $mobile_columns . ';',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<ul class="gutenify--category-list__items">
		<?php foreach ( $terms as $term ) : ?>
			<?php
			$term_link = get_term_link( $term );
			$image_id  = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}
			?>
			<li class="gutenify--category-list__item">
				<a class="gutenify--category-list__link" href="<?php echo esc_url( $term_link ); ?>">
					<?php if ( $show_image ) : ?>
						<span class="gutenify--category-list__image">
							<?php if ( $image_id ) : ?>
								<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php elseif ( function_exists( 'wc_placeholder_img' ) ) : ?>
								<?php echo wc_placeholder_img( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
						</span>
					<?php endif; ?>
					<span class="gutenify--category-list__content">
						<?php if ( $show_name ) : ?>
							<strong class="gutenify--category-list__name"><?php echo esc_html( $term->name ); ?></strong>
						<?php endif; ?>
						<?php if ( $show_count ) : ?>
							<span class="gutenify--category-list__count"><?php echo esc_html( sprintf( _n( '%s product', '%s products', $term->count, 'gutenify' ), number_format_i18n( $term->count ) ) ); ?></span>
						<?php endif; ?>
						<?php if ( $show_description && $term->description ) : ?>
							<span class="gutenify--category-list__description"><?php echo wp_kses_post( wp_trim_words( $term->description, 24 ) ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
