<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Blocks {
	public function __construct() {
		add_filter( 'block_categories_all', array( __CLASS__, 'register_block_categories' ) );
	}

	public static function register_block_categories( $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'gutenify',
					'title' => __( 'Gutenify Blocks', 'gutenify' ),
				),
			),
			$categories
		);
	}
}

new Blocks();
