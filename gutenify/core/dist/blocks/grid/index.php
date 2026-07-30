<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Grid{
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'gutenify_render_block_gutenify/grid', array( __CLASS__, 'render_block' ), 10, 4 );
	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}

	public static function render_block( $block_content, $block, $instance, $block_id ) {
		$root_selector = '.' . $block_id ;
		$css='';

	$css = $root_selector . '{ display: grid;';

	if (!empty($block['attrs']['blockAdvanceOptions']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['rowGap'] ) . ';';
	} else {
		$css .= 'row-gap: 40px;';

	}
	if (!empty($block['attrs']['blockAdvanceOptions']['gap']['columnGap'])) {
		$css .= 'column-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['columnGap'] ) . ';';
	} else {
		$css .= 'column-gap: 40px;';

	}
	if (!empty($block['attrs']['blockAdvanceOptions']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['columns'] ) . ',1fr);';
	} else {
		$css .= 'grid-template-columns: repeat(3,1fr);';

	}
	$css .= '}';

	//tablet styling
	$css .= '@media screen and (max-width: 780px){'. $root_selector . '{';

	if (!empty($block['attrs']['blockAdvanceOptions']['tablet']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['columns'] ) . ',1fr);';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['columns'] ) . ',1fr);';
	} else {
		$css .= 'grid-template-columns: repeat(3,1fr);';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['tablet']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['gap']['rowGap'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['rowGap'] ) . ';';
	} else {
		$css .= 'row-gap: 40px;';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['tablet']['gap']['columnGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['gap']['columnGap'] ) . ';';
	} else if (!empty($block['attrs']['blockAdvanceOptions']['gap']['columnGap'])) {
		$css .= 'column-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['columnGap'] ) . ';';
	} else {
		$css .= 'column-gap: 40px;';
	}
	$css .= '}}';

	//mobile styling
	$css .= '@media screen and (max-width: 360px){ '. $root_selector. '{';

	if (!empty($block['attrs']['blockAdvanceOptions']['mobile']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['mobile']['columns'] ) . ',1fr);';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['tablet']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['columns'] ) . ',1fr);';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['columns'])) {
		$css .= 'grid-template-columns: repeat(' . esc_attr( $block['attrs']['blockAdvanceOptions']['columns'] ) . ',1fr);';
	} else {
		$css .= 'grid-template-columns: repeat(3,1fr);';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['mobile']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['mobile']['gap']['rowGap'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['tablet']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['gap']['rowGap'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['gap']['rowGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['rowGap'] ) . ';';
	} else {
		$css .= 'row-gap: 40px;';
	}
	if (!empty($block['attrs']['blockAdvanceOptions']['mobile']['gap']['columnGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['mobile']['gap']['columnGap'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['tablet']['gap']['columnGap'])) {
		$css .= 'row-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['tablet']['gap']['columnGap'] ) . ';';
	} elseif (!empty($block['attrs']['blockAdvanceOptions']['gap']['columnGap'])) {
		$css .= 'column-gap: ' . esc_attr( $block['attrs']['blockAdvanceOptions']['gap']['columnGap'] ) . ';';
	} else {
		$css .= 'column-gap: 40px;';
	}

	$css .= '}}';


	$handle = 'gutenify_'. str_replace( '/', '_', $block['blockName'] ) . '_' . $block_id;
	wp_add_inline_style( $handle, $css);
	return $block_content;
}
}

Grid::init();

