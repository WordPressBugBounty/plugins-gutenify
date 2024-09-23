<?php
namespace gutenify;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Responsive_Display_Control extends Main_Class_Wrapper{
	public static $handle;
	public function __construct() {
		parent::__construct();
		self::$handle = static::$main_slug . '-responsive-display-control';
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}
	public static function register_assets() {
		$assets = Helpers::asset_file_values(sprintf( '%sdist/extend/responsive-display-control/index.asset.php', static::$base_dir ));
		$handle = static::$main_slug . '-responsive-display-control';
		wp_register_script( self::$handle, static::$base_url . 'dist/extend/responsive-display-control/index.js', $assets['dependencies'], $assets['version'], true );

		wp_register_style( self::$handle . '-admin', static::$base_url . 'dist/extend/responsive-display-control/index.css', array(), $assets['version'] );
		wp_register_style( self::$handle, static::$base_url . 'dist/extend/responsive-display-control/style-index.css', array(), $assets['version'] );
	}

	public static function enqueue_block_editor_assets() {
		$assets = Helpers::asset_file_values(sprintf( '%sdist/extend/responsive-display-control/index.asset.php', static::$base_dir ));
		wp_enqueue_script( self::$handle );
		wp_enqueue_style( self::$handle . '-admin' );
	}

	public static function enqueue_scripts() {
		$assets = Helpers::asset_file_values(sprintf( '%sdist/extend/responsive-display-control/index.asset.php', static::$base_dir ));
		wp_enqueue_style( self::$handle );
	}
}

new Responsive_Display_Control();
