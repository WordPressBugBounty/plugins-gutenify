<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Post_Carousel {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );

		add_filter( 'gutenify_render_block_gutenify/post-carousel', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	public static function render_callback( $attr, $content, $props ) {
		$layout =  ! empty( $attr['layout'] ) ? esc_attr( $attr['layout'] ) : 1;
		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => 'gutenify-post-carousel-' . $layout
		) );

		$has_navigation = ! ( isset( $attr['hasNavigation']) && false === $attr['hasNavigation'] );
		$has_pagination = ! ( isset( $attr['hasPagination']) && false === $attr['hasPagination'] );

		$new_content = sprintf( '<div %s>', $wrapper_attributes );
		$new_content .= '<div class="swiper-wrapper">';
		$posts = Post_List::get_posts( $attr, $props );
		$new_content .= self::display_posts( $posts, $attr, $props );
		$new_content .= '</div>';
		if ( $has_navigation ) {
			$new_content .= '<div class="navigation-wrap">
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>';
		}

		if ( $has_pagination ) {
			$new_content .= '<div class="swiper-pagination"></div>';
		}
		$new_content .= '</div>';
		return $new_content;
	}

	public static function display_posts($posts, $attributes, $props)
	{
		$constants = \gutenify\Helpers::plugin_constants();
		$plugin_main_slug = $constants['plugin_main_slug'];
		$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];


		$list_items_markup = '';
		// echo '<pre>';
		// var_dump($attributes['displayFeaturedImage']);
		// echo '</pre>';
		$has_image = !$attributes['displayFeaturedImage'] ? 'has-no-feature-image': '';

		$default_image_url = $constants['core_base_url'] . 'assets/images/placeholder-attachment.png';

		foreach ($posts as $post) {

			$list_class = '';
			$align_self_class = '';

			$list_items_markup .= '<div class="gutenify-post-carousel-item gutenify-post-carousel-item-wrapper swiper-slide ">';
			$list_items_markup .= '<div class="gutenify-post-carousel-item-inner-wrapper ' . $has_image . '">';

			if (!empty($attributes['displayFeaturedImage'])) {
				$thumbnail_url = $default_image_url;
				if (has_post_thumbnail($post['ID'])) {
					$thumbnail_url = get_the_post_thumbnail_url($post['ID']);
				}
				$list_items_markup .= sprintf(
					'<div class="gutenify-post-carousel-thumb">
						<a class="image-hover-zoom" href="%1$s">
							<img src="%2$s" />
						</a>
					</div>',
					esc_url($post['postLink']),
					esc_url($thumbnail_url)
				);
			}

			$list_items_markup .= sprintf(
				'<div class="gutenify-post-carousel-text-content %s">',
				esc_attr($align_self_class)
			);

			$title = $post['title'];

			if (!$title) {

				$title = _x('(no title)', 'placeholder when a post has no title', 'gutenify');

			}

			$list_items_markup .= sprintf(
				'<h3 class="gutenify-post-carousel-title"><a href="%1$s" rel="bookmark">%2$s</a></h3> ',
				$post['postLink'],
				$title
			);
			$meta_data = '';
			if (isset($attributes['displayPostDate']) && $attributes['displayPostDate']) {
				$meta_data .= sprintf(
					'<span class="posted-on"><time datetime="%1$s" class="gutenify-posts-date">%2$s</time></span>',
					esc_html($post['date']),
					esc_html($post['dateReadable'])
				);

			}
			if (!empty($attributes['displayPostAuthor'])) {
				if (!get_the_author_meta('description') && post_type_supports(get_post_type(), 'author')) {
					$meta_data .= '<span class="byline">';
					$meta_data .= sprintf(
						'%s',
						'<a href="' . esc_url(get_author_posts_url($post['author_id'])) . '" rel="author">' . esc_html(get_the_author_meta('display_name', $post['author_id'])) . '</a>'
					);
					$meta_data .= '</span>';
				}
			}

			if (!empty($attributes['displayPostCategories'])) {
				/* translators: used between list items, there is a space after the comma. */
				$categories_list = get_the_category_list(__(', '), '', $post['ID']);
				if ($categories_list) {
					$meta_data .= sprintf(
						'<span class="cat-links">%s </span>',
						$categories_list // phpcs:ignore WordPress.Security.EscapeOutput
					);
				}
			}

			if (!empty($meta_data)) {
				$list_items_markup .= sprintf('<div class="gutenify-post-carousel-meta">%1$s</div>', $meta_data);
			}

			if (isset($attributes['displayPostContent']) && $attributes['displayPostContent']) {

				$post_excerpt = $post['postExcerpt'];
				$trimmed_excerpt = esc_html(wp_trim_words($post_excerpt, $attributes['excerptLength'], ' &hellip; '));
				$list_items_markup .= '<div class="entry-summary">';
				$list_items_markup .= sprintf(
					'<p>%1$s</p>',
					esc_html($trimmed_excerpt)
				);

				if (isset($attributes['displayPostLink']) && $attributes['displayPostLink']) {

					$list_items_markup .= sprintf(
						'<a href="%1$s" class="more-link"><span class="more-button">%2$s<span class="screen-reader-text">%3$s</span></span></a>',
						esc_url($post['postLink']),
						esc_html($attributes['postLink']),
						esc_html($post['title'])
					);

				}

				$list_items_markup .= '</div>';
			}

			$list_items_markup .= '</div></div></div>';

		}

		return $list_items_markup;
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {


		$css_thread = '';
		//color
		$css_chunk = '';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'] . ';';
		}

		$background_color = !empty( $instance->attributes['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) ? esc_attr($instance->attributes['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) :  '';
		$background_color = ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) ? esc_attr($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'] ) : $background_color;

		if ( ! empty( $background_color ) ) {
			$css_chunk .=  'background-color: ' . esc_attr( $background_color ). ' ;';
		}

		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'])) {
			$css_chunk .= 'background: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'] . ';';
		}

		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'])) {
			$css_chunk .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'] . ';';
		}

		//borderWidth
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'] )  ) {
			$css_chunk .= 'border-style:solid;';
			$css_chunk .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'], 'border-', '-width' );
		}

		//borderWidth
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] )  ) {
			$css_chunk .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderRadius'] );
		}

		if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['boxShadow'] ) ) {
			$css_chunk .= \gutenify\Style_Helpers::custom_box_shadow_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['boxShadow'] );
		}

		$inner_block_selector = '.' . $block_id . ' .gutenify-post-carousel-item-inner-wrapper';

		$css_thread .= $inner_block_selector . '{';
		$css_thread .= $css_chunk;
		$css_thread .= '}';

		/**
		 * Content padding
		 */
		if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding'] ) ) {
			$css_thread .= Dynamic_Styles::get_spacing_with_media( '.' . $block_id . '.wp-block-gutenify-post-carousel .gutenify-post-carousel-item .gutenify-post-carousel-item-inner-wrapper .gutenify-post-carousel-text-content', $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding'], 'padding-' );
		}


		/**
		 * Hover
		 */
		$css_chunk = '';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'])) {
			$css_chunk .= 'color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'] . ';';
		}

		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'])) {
			$css_chunk .= 'background-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'] . ';';
		}

		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'])) {
			$css_chunk .= 'background: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'] . ';';
		}

		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'])) {
			$css_chunk .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'] . ';';
		}

		$inner_block_selector = $inner_block_selector . ':hover';

		$css_thread .= $inner_block_selector . '{';
		$css_thread .= $css_chunk;
		$css_thread .= '}';


		/**
		 * Thubmnail
		 */
		$css_chunk = '';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['thumbnailMinHeight'])) {
			$css_chunk .= 'height:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['thumbnailMinHeight'] . ';';
		}
		$thumbnail_selector = '.' . $block_id . ' .gutenify-post-carousel-item-inner-wrapper .gutenify-post-carousel-thumb img';

		$css_thread .= $thumbnail_selector . '{';
		$css_thread .= $css_chunk;
		$css_thread .= '}';

		$css_chunk = '';
		if(!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['postTitleSize'])){
			$css_chunk .= 'font-size:'. $block['attrs']['blockAdvanceOptions']['innerBlock']['postTitleSize'];
		}
		$post_title_selector = '.' . $block_id . ' .gutenify-post-carousel-title';
		$css_thread .= $post_title_selector . '{';
		$css_thread .= $css_chunk;
		$css_thread .= '}';

		$handle = 'gutenify_' . str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css_thread );

		return $block_content;
	}
}

Post_Carousel::init();



/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */



function gutenify_post_carousel_render_block($block_content, $block)
{
	if ((isset($block['blockName']) && 'gutenify/post-carousel' !== $block['blockName']) || is_admin()) {
		return $block_content;
	}

	$block_id = wp_unique_id('gtfy-');
	$block_client_id = !empty($block['attrs']['blockClientId']) ? 'gutenify-section-' . esc_attr($block['attrs']['blockClientId']) : $block_id;

	$block_content = new WP_HTML_Tag_Processor($block_content);
	$block_content->next_tag();
	$block_content->add_class($block_client_id);

	//selector
	// $has_image = $block['attrs']
	// var_dump($block['attrs']);
	$root_selector = '.' . $block_client_id . '.wp-block-gutenify-post-carousel';
	$inner_block_selector = $root_selector . ' .gutenify-post-carousel-item-inner-wrapper';
	$css = '';

	$css .= $inner_block_selector . '{';

	//color
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'])) {
		$css .= 'color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['textColor'] . ';';
	}

	//background Color
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'])) {
		$css .= 'background: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundGradient'] . ';';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'])) {
		$css .= 'background-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['backgroundColor'] . ';';
	} else {
		$css .= 'background-color: #f3f3f3;';
	}

	//border color
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'])) {
		$css .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['borderColor'] . ';';
	}

	//borderWidth
	if ( ! empty( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'] )  ) {
		$css .= 'border-style:solid;';
		$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['innerBlock']['borderWidth'], 'border-', '-width' );
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['arrows']['borderRadius'] ) ) {
		$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['arrows']['borderRadius'] );
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['arrows']['boxShadow'] ) ) {
		$css .= \gutenify\Style_Helpers::custom_box_shadow_control( $block['attrs']['blockAdvanceOptions']['arrows']['boxShadow'] );
	}
	$css .= '}';

	//thumbnail
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['thumbnailMinHeight'])) {
		$css .= '.wp-block-gutenify-post-carousel .gutenify-post-carousel-item-inner-wrapper .gutenify-post-carousel-thumb img {';
		$css .= 'min-height:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['thumbnailMinHeight'] . ';';
		$css .= '}';
	}

	//hover style
	$css .= $inner_block_selector . ':hover {';

	//color
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'])) {
		$css .= ' color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverTextColor'] . ';';
	}

	//background
	if (!empty($block['attrs']['hoverBackgroundGradient'])) {
		$css .= 'background:' . $block['attrs']['hoverBackgroundGradient'] . ';';
	} elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
		$css .= 'background:' . $block['attrs']['hoverBackgroundColor'] . ';';
	}elseif (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'])) {
		$css .= 'background: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundGradient'] . ';';
	}  elseif (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'])) {
		$css .= 'background: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBackgroundColor'] . ';';
	}

	//border color
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'])) {
		$css .= 'border-color: ' . $block['attrs']['blockAdvanceOptions']['innerBlock']['hoverBorderColor'] . ';';
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['arrows']['hoverBoxShadow'] ) ) {
		$css .= \gutenify\Style_Helpers::custom_box_shadow_control( $block['attrs']['blockAdvanceOptions']['arrows']['hoverBoxShadow'] );
	}

	$css .= '}';

	//padding
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding'])) {
		$css .= $inner_block_selector . ' .gutenify-post-carousel-text-content{';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['desktop']['top'])) {
			$css .= 'padding-top:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['desktop']['top'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['bottom'])) {
			$css .= 'padding-bottom:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['desktop']['bottom'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['left'])) {
			$css .= 'padding-left:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['desktop']['left'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['right'])) {
			$css .= 'padding-right:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['desktop']['right'] . 'px;';
		}
		$css .= '}';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding'])) {
		$css .= '@media only screen and (max-width: 992px){' . $inner_block_selector . ' .gutenify-post-carousel-text-content{';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['top'])) {
			$css .= 'padding-top:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['top'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['bottom'])) {
			$css .= 'padding-bottom:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['bottom'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['left'])) {
			$css .= 'padding-left:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['left'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['right'])) {
			$css .= 'padding-right:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['tablet']['right'] . 'px;';
		}
		$css .= '}}';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding'])) {
		$css .= '@media only screen and (max-width: 768px){' . $inner_block_selector . ' .gutenify-post-carousel-text-content{';
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['top'])) {
			$css .= 'padding-top:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['top'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['bottom'])) {
			$css .= 'padding-bottom:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['bottom'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['left'])) {
			$css .= 'padding-left:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['left'] . 'px;';
		}
		if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['right'])) {
			$css .= 'padding-right:' . $block['attrs']['blockAdvanceOptions']['innerBlock']['contentPadding']['mobile']['right'] . 'px;';
		}
		$css .= '}}';
	}

	if (!empty($block['attrs']['blockAdvanceOptions']['innerBlock']['postTitleSize'])) {
		$css .= $inner_block_selector . ' .gutenify-post-carousel-title { font-size: ' . esc_attr($block['attrs']['blockAdvanceOptions']['innerBlock']['postTitleSize']) . '; }';
	}

	$handle = 'gutenify-block-inline-handle';
	wp_add_inline_style('wp-block-library', $css);
	return $block_content->get_updated_html();
}

// add_filter('render_block', 'gutenify_post_carousel_render_block', 200, 2);
