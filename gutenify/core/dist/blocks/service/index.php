<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Service{
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/service', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {

		$root_selector = '.' . $block_id ;
		$css='';

		$css_chunk = '';

		//text
		if(!empty($block['attrs']['blockAdvanceOptions']['textColor'])){
			$css_chunk .= 'color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['textColor'] ) . ';';
		}


		//background
		if(!empty($block['attrs']['backgroundGradient'])){
			$css_chunk .= 'background: ' . esc_attr( $block['attrs']['backgroundGradient'] ) . ';';
		}elseif(!empty($block['attrs']['backgroundColor'])){
			$css_chunk .= 'background:' . esc_attr( $block['attrs']['backgroundColor'] ). ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['backgroundGradient'])){
			$css_chunk .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundGradient'] ) . ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['backgroundColor'])){
			$css_chunk .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['backgroundColor'] ) . ';';
		}

		//border color
		if(!empty($block['attrs']['blockAdvanceOptions']['borderColor'])){
			$css_chunk .= 'border-color: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderColor'] ) . ';';
		}

		//border width
		if(!empty($block['attrs']['blockAdvanceOptions']['borderWidth'])){
			if(is_numeric($block['attrs']['blockAdvanceOptions']['borderWidth'])){
					$css_chunk .= 'border-width:' .esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth'] ) . 'px; border-style: solid;';
			}
			if(is_string($block['attrs']['blockAdvanceOptions']['borderWidth'])){
				$css_chunk .= 'border-width:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth'] ) . 'px; border-style: solid;';
			}
			if(is_array($block['attrs']['blockAdvanceOptions']['borderWidth'])){
				if(!empty($block['attrs']['blockAdvanceOptions']['borderWidth']['top'])){
					$css_chunk .='border-top-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth']['top'] ). '; border-top-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderWidth']['bottom'])){
					$css_chunk .='border-bottom-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth']['bottom'] ). '; border-bottom-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderWidth']['left'])){
					$css_chunk .='border-left-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth']['left'] ). '; border-left-style: solid;';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderWidth']['right'])){
					$css_chunk .='border-right-width:'. esc_attr( $block['attrs']['blockAdvanceOptions']['borderWidth']['right'] ). '; border-right-style: solid;';
				}
			}
		}

		//border radius
		if(!empty($block['attrs']['blockAdvanceOptions']['borderRadius'])){
			if(is_string($block['attrs']['blockAdvanceOptions']['borderRadius'])){
				$css_chunk .= ' border-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) . ';';
			}
			if(is_numeric($block['attrs']['blockAdvanceOptions']['borderRadius'])){
				$css_chunk .= 'border-radius:'. esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius'] ) . 'px;';
			}
			if(is_array($block['attrs']['blockAdvanceOptions']['borderRadius'])){
				if(!empty($block['attrs']['blockAdvanceOptions']['borderRadius']['topleft'])){
					$css_chunk .= 'border-top-left-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius']['topLeft'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderRadius']['bottomLeft'])){
					$css_chunk .= 'border-bottom-left-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius']['bottomLeft'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderRadius']['topRight'])){
					$css_chunk .= 'border-top-right-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius']['topRight'] ) .';';
				}
				if(!empty($block['attrs']['blockAdvanceOptions']['borderRadius']['bottomRight'])){
					$css_chunk .= 'border-bottom-right-radius:' . esc_attr( $block['attrs']['blockAdvanceOptions']['borderRadius']['bottomRight'] ) .';';
				}
			}
		}

		//hover
		if(!empty($css_chunk)){
			$css .= $root_selector . ' {' . $css_chunk . '}';
		}
		if(!empty($block['attrs']['blockAdvanceOptions']['textColor'])){
			$css .= $root_selector . ' :where(h1,h2,h3,h4,h5,h6){
			color: inherit;
			}
			';
	   }

		$css_chunk = '';

		//color
		if(!empty($block['attrs']['blockAdvanceOptions']['hoverTextColor'])){
			$css_chunk .= 'color:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverTextColor'] ) . ';';
		}

		//background
		if(!empty($block['attrs']['hoverBackgroundGradient'])){
			$css_chunk .= 'background:' . esc_attr( $block['attrs']['hoverBackgroundGradient'] ) . ';';
		}elseif(!empty($block['attrs']['hoverBackgroundColor'])){
			$css_chunk .='background:' . esc_attr( $block['attrs']['hoverBackgroundColor'] ) . ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'])){
			$css_chunk .= 'background:' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundGradient'] ). ';';
		}elseif(!empty($block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'])){
			$css_chunk .='background: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBackgroundColor'] ). ';';
		}

		//borderColor
		if(!empty($block['attrs']['blockAdvanceOptions']['hoverBorderColor'])){
			$css_chunk .= 'border-color:'. esc_attr( $block['attrs']['blockAdvanceOptions']['hoverBorderColor'] ) . ';';
		}

		if(!empty($css_chunk)){
			$css .= $root_selector . ':hover {' . $css_chunk . '}';
		}
		$handle = 'gutenify_'. str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
		wp_add_inline_style( $handle, $css);
		return $block_content;
	}
}

Service::init();
