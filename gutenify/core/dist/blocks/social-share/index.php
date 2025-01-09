<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Social_Share
	{
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );

	}

	public static function register_block() {
		register_block_type( __DIR__ );
	}
}

Social_Share::init();

