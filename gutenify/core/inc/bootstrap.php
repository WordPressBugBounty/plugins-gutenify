<?php
defined( 'ABSPATH' ) || exit;

require 'class-helpers.php';
require 'helpers.php';
require 'typography-helpers.php';
// require 'get-dynamic-blocks.php';
require 'class-block-inline-styles.php';
require 'class-block-assets.php';
require 'class-templates.php';
require 'class-rest.php';
require 'class-actions.php';
// require 'class-admin-menu.php';
include 'styles.php';
include 'demo-importer.php';
include 'class-rest-demo-importer-v2.php';
include 'extend/custom-css.php';
include 'woocommerce-template-functions.php';
include 'class-assets.php';
include 'site-templates/class-global-code.php';

// Blocks.
$active_blocks = \gutenify\Helpers::active_blocks();
$base_dir = \gutenify\Helpers::core_base_dir();

if ( ! empty( $active_blocks ) ) {
	foreach( $active_blocks as $block ) {
		$file = $base_dir . 'dist/blocks/' . $block. '/index.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

$required_files = array(
	'inc/interfaces/class-main-class-wrapper.php',
	'inc/class-fix-third-party-block-issues.php',
	'inc/class-meta-fields.php',
	'inc/class-blocks.php',

	'dist/non-blocks/components/index.php',
	// 'dist/non-blocks/extend/save-template/index.php',
	// 'dist/non-blocks/extend/responsive-display-control/index.php',
	'dist/non-blocks/extend/toolbar-templates-button/index.php',
	'dist/non-blocks/common-scripts/slider/index.php',
	'dist/non-blocks/admin/pages/getting-started/index.php',
	'dist/non-blocks/admin/pages/demo-importer-v2/index.php',
	'dist/non-blocks/admin/pages/settings/index.php',

	'inc/class-admin-menu.php',
);

foreach( $required_files as $file ) {
	$file = sprintf( '%s%s', $base_dir, $file );
	if( file_exists( $file ) ) {
		require_once $file;
	}
}
