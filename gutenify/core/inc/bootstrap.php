<?php
/**
 * Bootstrap file to initialize and load all required dependencies and core functionality.
 *
 * This file serves as the entry point to include necessary files and set up the environment for the LIGER Lite plugin/theme.
 *
 * @package gutenify
 */

/**
 * Prevent direct access to the file.
 *
 * Ensures this file is being loaded within the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Load the Helpers class file containing utility methods.
 */
require_once 'class-helpers.php';

/**
 * List of PHP files to be required for initializing core functionality.
 *
 * @var string[] Array of relative file paths.
 */
$required_files = array(

	// Core helpers and utilities.
	'inc/helpers.php',
	'inc/typography-helpers.php',
	'inc/styles.php',
	'inc/class-style-helpers.php',
	'inc/class-assets.php',

	// Gutenberg block handling.
	'inc/class-blocks-categories.php',
	'inc/class-block-inline-styles.php',
	'inc/class-block-assets.php',

	// Template and site structure management.
	'inc/class-templates.php',
	'inc/site-templates/class-global-code.php',

	// REST API endpoints.
	'inc/class-rest.php',
	'inc/class-rest-demo-importer-v2.php',

	// Demo importing tools.
	'inc/demo-importer.php',

	// Admin interface and backend logic.
	'inc/class-admin-menu.php',
	'inc/class-actions.php',
	'inc/class-meta-fields.php',
	'inc/class-fix-third-party-block-issues.php',

	// Extensions for dynamic functionality.
	'inc/extend/class-dynamic-block-classname.php',
	'inc/extend/class-dynamic-styles.php',
	'inc/extend/class-slider-blocks.php',
	'inc/extend/class-post-list.php',

	// WooCommerce support.
	'inc/woocommerce-template-functions.php',

	// Interface definitions and wrappers.
	'inc/interfaces/class-main-class-wrapper.php',

	// React-based non-block components and admin pages.
	'dist/non-blocks/components/index.php',


	// Block editor extensions.
	'dist/non-blocks/extend/toolbar-templates-button/index.php',
	'dist/non-blocks/extend/custom-list/index.php',
	'dist/non-blocks/extend/block-custom-css/index.php',
	'dist/non-blocks/extend/sliders/index.php',
	'dist/non-blocks/extend/aos/index.php',

	// Admin page interfaces.
	'dist/non-blocks/admin/pages/getting-started/index.php',
	'dist/non-blocks/admin/pages/demo-importer-v2/index.php',
	'dist/non-blocks/admin/pages/settings/index.php',

	// Optionally excluded files for future use.
	/**
	 * 'dist/non-blocks/extend/save-template/index.php',
	 * 'dist/non-blocks/extend/responsive-display-control/index.php',
	 * 'dist/non-blocks/extend/masonry/index.php',
	 * 'dist/non-blocks/common-scripts/slider/index.php',
	 */


	// ----

	// 'inc/helpers.php',
	// 'inc/typography-helpers.php',
	// 'inc/class-block-inline-styles.php',
	// 'inc/class-block-assets.php',
	// 'inc/class-templates.php',
	// 'inc/class-rest.php',
	// 'inc/class-actions.php',
	// 'inc/styles.php',
	// 'inc/demo-importer.php',
	// 'inc/class-rest-demo-importer-v2.php',
	// 'inc/extend/class-dynamic-block-classname.php',
	// 'inc/extend/class-dynamic-styles.php',
	// 'inc/extend/class-slider-blocks.php',
	// 'inc/extend/class-post-list.php',
	// 'inc/woocommerce-template-functions.php',
	// 'inc/class-assets.php',
	// 'inc/site-templates/class-global-code.php',

	// 'inc/class-style-helpers.php',

	// 'inc/interfaces/class-main-class-wrapper.php',
	// 'inc/class-fix-third-party-block-issues.php',
	// 'inc/class-meta-fields.php',
	// 'inc/class-blocks.php',

	// 'dist/non-blocks/components/index.php',
		// 'dist/non-blocks/extend/save-template/index.php',
		// 'dist/non-blocks/extend/responsive-display-control/index.php',
	// 'dist/non-blocks/extend/toolbar-templates-button/index.php',
	// 'dist/non-blocks/extend/custom-list/index.php',
	// 'dist/non-blocks/extend/block-custom-css/index.php',
	// 'dist/non-blocks/extend/sliders/index.php',
		// 'dist/non-blocks/extend/masonry/index.php',
	// 'dist/non-blocks/extend/aos/index.php',
		// 'dist/non-blocks/common-scripts/slider/index.php',
	// 'dist/non-blocks/admin/pages/getting-started/index.php',
	// 'dist/non-blocks/admin/pages/demo-importer-v2/index.php',
	// 'dist/non-blocks/admin/pages/settings/index.php',

	// 'inc/class-admin-menu.php',
);

// Import the Helpers class to simplify references.
use gutenify\Helpers;

// Get the base directory path where block files are located.
$base_dir = Helpers::core_base_dir();

// Loop through the list of required file paths.
foreach ( $required_files as $file ) {
	// Build the full path to the file by prepending the base directory.
	$full_path = $base_dir . $file;

	// Check if the file exists and is a regular file before including it.
	if ( is_file( $full_path ) ) {
		// Include the file only once to prevent redeclaration errors.
		require_once $full_path;
	}
}

// Retrieve the list of active blocks that need to be loaded.
$active_blocks = Helpers::active_blocks();

if ( ! empty( $active_blocks ) ) {
	// Loop through each active block and include its index.php file if it exists.
	foreach ( $active_blocks as $block ) {
		// Build the full path to the block's index.php file.
		$file = "{$base_dir}dist/blocks/{$block}/index.php";

		// Include the file only if it exists and is a regular file.
		if ( is_file( $file ) ) {
			require_once $file;
		}
	}
}
