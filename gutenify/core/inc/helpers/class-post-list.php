<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Post_List {
	public static function get_posts( $attributes, $props ) {
		$args = array(
			'posts_per_page'   => ! empty( $attributes['query']['numberOfItems'] ) ? $attributes['query']['numberOfItems'] : 10,
			'post_status'      => 'publish',
			'order'            => ! empty( $attributes['query']['order'] ) ? $attributes['query']['order'] : 'desc',
			'orderby'          => ! empty( $attributes['query']['orderBy'] ) ? $attributes['query']['orderBy'] : 'date',
			'suppress_filters' => false,
			'category__in'     => array(),
			'post__not_in'     => array( get_the_ID() ),
		);

		if ( isset( $attributes['query']['selectedCategories'] ) ) {

			$args['category__in'] = array_column( $attributes['query']['selectedCategories'], 'id' );

		}

		if ( ! empty( $attributes['query']['tax']['category'] ) ) {

			$args['category__in'] = array_merge( $attributes['query']['tax']['category'], $args['category__in'] );

		}

		if ( ! empty( $attributes['query']['tax']['tag'] ) ) {
			$args['tag__in'] = $attributes['query']['tax']['tag'];
		}

		if ( ! empty( $attributes['query']['tax']['post_tag'] ) ) {
			$args['tag__in'] = $attributes['query']['tax']['post_tag'];
		}

		if ( ! empty( $attributes['query']['authorIds'] ) ) {
			$args['author__in'] = $attributes['query']['authorIds'];
		}

		if ( ! empty( $attributes['query']['postTypes'] ) && in_array( 'attachment', $attributes['query']['postTypes'] ) ) {
			$args['post_status'] = 'inherit';

		}

		$recent_posts    = get_posts( $args );
		$formatted_posts = gutenify_get_post_info( $recent_posts );
		return $formatted_posts;
	}
}
