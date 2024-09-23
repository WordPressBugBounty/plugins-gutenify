<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Toolbar_Templates_Button extends Main_Class_Wrapper{
	public static $handle;
	public function __construct() {
		parent::__construct();
		self::$handle = static::$main_slug . '-toolbar-templates-button';
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'register_assets' ) );
	}
	public static function register_assets() {
		$assets = Helpers::asset_file_values(sprintf( '%sdist/non-blocks/extend/toolbar-templates-button/index.asset.php', static::$base_dir ));
		wp_register_script( self::$handle, static::$base_url . 'dist/non-blocks/extend/toolbar-templates-button/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( self::$handle );
	}
}

new Toolbar_Templates_Button();
