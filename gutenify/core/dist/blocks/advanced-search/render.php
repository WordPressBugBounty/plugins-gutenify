<?php
/** Advanced Product Search render template. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'woocommerce' ) ) { return; }
$placeholder = isset( $attributes['placeholder'] ) ? sanitize_text_field( $attributes['placeholder'] ) : __( 'Search products…', 'gutenify' );
$minimum     = isset( $attributes['minimumCharacters'] ) ? max( 1, min( 5, absint( $attributes['minimumCharacters'] ) ) ) : 2;
$limit       = isset( $attributes['resultLimit'] ) ? max( 1, min( 20, absint( $attributes['resultLimit'] ) ) ) : 6;
$delay       = isset( $attributes['debounceDelay'] ) ? max( 150, min( 1000, absint( $attributes['debounceDelay'] ) ) ) : 300;
$show_categories = ! isset( $attributes['showCategories'] ) || ! empty( $attributes['showCategories'] );
$show_view_all   = ! isset( $attributes['showViewAll'] ) || ! empty( $attributes['showViewAll'] );
$input_id   = wp_unique_id( 'ff-product-search-' );
$search_url = get_post_type_archive_link( 'product' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'gutenify--advanced-search', 'data-ff-advanced-search' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-rest-url="<?php echo esc_url( rest_url( 'gutenify/v1/products/search' ) ); ?>" data-search-url="<?php echo esc_url( $search_url ); ?>" data-minimum="<?php echo esc_attr( $minimum ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-delay="<?php echo esc_attr( $delay ); ?>" data-show-categories="<?php echo $show_categories ? 'true' : 'false'; ?>" data-show-view-all="<?php echo $show_view_all ? 'true' : 'false'; ?>" data-label-categories="<?php esc_attr_e( 'Categories', 'gutenify' ); ?>" data-label-view-all="<?php esc_attr_e( 'View all results', 'gutenify' ); ?>" data-label-empty="<?php esc_attr_e( 'No products found.', 'gutenify' ); ?>" data-label-searching="<?php esc_attr_e( 'Searching…', 'gutenify' ); ?>" data-label-error="<?php esc_attr_e( 'Search could not be completed.', 'gutenify' ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Search products', 'gutenify' ); ?></label>
	<div class="gutenify--advanced-search__field">
		<input id="<?php echo esc_attr( $input_id ); ?>" type="search" class="gutenify--advanced-search__input" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="<?php echo esc_attr( $input_id ); ?>-results">
		<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="2"></path></svg>
	</div>
	<div id="<?php echo esc_attr( $input_id ); ?>-results" class="gutenify--advanced-search__results" role="listbox" hidden></div>
	<div class="gutenify--advanced-search__status screen-reader-text" role="status" aria-live="polite"></div>
</div>
