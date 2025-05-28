<?php
/**
 * Extend_Attributes
 *
 * Extends block metadata to include custom attributes for the plugin.
 * Adds 'customCss', 'blockClientId', and plugin-specific styles attributes to all blocks.
 *
 * @package gutenify
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Class Extend_Attributes
 *
 * Adds additional attributes to block metadata for enhanced customization.
 */
class Extend_Attributes {

	/**
	 * Register the extend_attributes method on the 'block_type_metadata' action.
	 */
	public function __construct() {
		add_action( 'block_type_metadata', array( $this, 'extend_attributes' ), 1 );
	}

	/**
	 * Extend block attributes if not already set.
	 *
	 * Adds:
	 *  - 'customCss':    For custom CSS input.
	 *  - 'blockClientId': For storing the block's unique client ID.
	 *  - '<plugin>Styles': For plugin-specific styles.
	 *
	 * @param array $metadata Block metadata array.
	 * @return array Modified block metadata.
	 */
	public function extend_attributes( $metadata ) {
		$constants                   = Helpers::plugin_constants();
		$plugin_main_camel_case_name = $constants['plugin_main_camel_case_name'];

		// Add customCss attribute if not present.
		if ( ! isset( $metadata['attributes']['customCss'] ) ) {
			$metadata['attributes']['customCss'] = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		// Add blockClientId attribute if not present.
		if ( ! isset( $metadata['attributes']['blockClientId'] ) ) {
			$metadata['attributes']['blockClientId'] = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		// Add plugin-specific styles attribute if not present.
		if ( ! isset( $metadata['attributes'][ $plugin_main_camel_case_name . 'Styles' ] ) ) {
			$metadata['attributes'][ $plugin_main_camel_case_name . 'Styles' ] = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		// Add animation settings attribute if not present.
		if ( ! isset( $metadata['attributes']['animationsSettings'] ) ) {
			$metadata['attributes']['animationsSettings'] = array(
				'type'    => 'object',
				'default' => array(
					'duration' => 1000,
				),
			);
		}

		return $metadata;
	}
}

// Initialize the attribute extender.
new Extend_Attributes();
