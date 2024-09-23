<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Main_Class_Wrapper {
	static $plugin_info;
	static $main_slug;
	static $base_dir;
	static $base_url;
	public function __construct() {
		self::$plugin_info = Helpers::plugin_constants();
		self::$main_slug = self::$plugin_info['plugin_main_slug'];
		self::$base_dir =  Helpers::core_base_dir();
		self::$base_url =  Helpers::core_base_url();
	}
}
