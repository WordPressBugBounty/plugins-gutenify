<?php
namespace gutenify;

defined('ABSPATH') || exit;

class Testimonials
{
	public static function init()
	{
		add_action('init', array(__CLASS__, 'register_block'));
		add_filter('gutenify_render_block_gutenify/testimonials', array(__CLASS__, 'render_block'), 10, 4);
	}

	public static function register_block()
	{
		register_block_type(__DIR__);
	}

	public static function render_block($block_content, $block, $instance, $block_id)
	{
		$root_selector = '.' . $block_id;
		$css = '';
		$css = $root_selector . '{';


		//color
		if (!empty($block['attrs']['blockAdvanceOptions']['textColor'])) {
			$css .= 'color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['textColor'] ) . ';';
		}

		//background
		if (!empty($block['attrs']['backgroundGradient'])) {
			$css .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
		}elseif (!empty($block['attrs']['backgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['backgroundColor'] ) . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundGradient'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundGradient'] ) . ';';
		}elseif (!empty($block['attrs']['blockAdvanceOptions']['backgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundColor'] ) . ';';
		}

		//border color
		if (!empty($block['attrs']['blockAdvanceOptions']['borderColor'])) {
			$css .= 'border-color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderColor'] ) . ';';
		}



	//border width
	if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderWidth'] )  ) {
		$css .= 'border-style:solid;';
		$css .= \gutenify\Style_Helpers::box_control( $block['attrs']['blockAdvanceOptions']['borderWidth'], 'border-', '-width');
	}

	if ( ! empty( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) ) {
		$css .= \gutenify\Style_Helpers::border_radius_control( $block['attrs']['blockAdvanceOptions']['borderRadius'] );
	}
		$css .= '}';
		if(!empty($block['attrs']['blockAdvanceOptions']['textColor'])){
			$css .= $root_selector . ' :where(h1,h2,h3,h4,h5,h6){
			color: inherit;
			}
			';
	   }


		//hoverstyle
		$css .= $root_selector . ':hover{';
		//hover color
		if (!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])) {
			$css .= 'color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverTextColor'] ) . ';';
		}

		//hover background
		if (!empty($block['attrs']['hoverBackgroundGradient'])) {
			$css .= 'background: ' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
		} elseif (!empty($block['attrs']['hoverBackgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'])) {
			$css .= 'background: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'] ) . ';';
		} elseif (!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'])) {
			$css .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'] ) . ';';
		} else {
			$css .= '';
		}

		//hover border
		if (!empty($block['attrs']['blockAdvanceOptions']['hoverBorderColor'])) {
			$css .= 'border-color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBorderColor'] ) . ';';
		}
		$css .= '}';






		$handle = 'gutenify_' . str_replace('/', '_', $block['blockName']) . '_' . $block_id;
		wp_add_inline_style($handle, $css);
		return $block_content;
	}
}

Testimonials::init();

