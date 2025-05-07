<?php

namespace gutenify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Countup_V2{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));

	}

	public static function register_block() {
		register_block_type(__DIR__);
	}
}

Countup_V2::init();
