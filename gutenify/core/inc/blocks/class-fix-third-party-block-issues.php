<?php
/**
 * Fix integration issues with third-party blocks (e.g., WPForms).
 *
 * Adds missing attributes required by Gutenify for better block compatibility.
 *
 * @package gutenify
 * @subpackage Compatibility
 * @since 1.0.0
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Class Fix_Third_Party_Block_Issues
 *
 * Ensures compatibility with WPForms Gutenberg block by adding required attributes.
 */
class Fix_Third_Party_Block_Issues {

	/**
	 * Attributes to inject into WPForms Gutenberg block.
	 *
	 * @var array
	 */
	private static $attributes = array(
		// 'customCss'          => array(
		// 	'type' => 'string',
		// ),
		// 'blockClientId'      => array(
		// 	'type' => 'string',
		// ),
		'animationsSettings' => array(
			'type'    => 'object',
			'default' => array(
				'duration' => 1000,
			),
		),
	);

	/**
	 * Constructor: hooks into WPForms block attribute filter.
	 */
	public function __construct() {
		add_filter(
			'wpforms_gutenberg_form_selector_attributes',
			array( __CLASS__, 'add_custom_block_attributes' )
		);
	}

	/**
	 * Adds missing attributes to the WPForms block if not already set.
	 *
	 * @param array $attrs Existing block attributes.
	 * @return array Modified attributes.
	 */
	public static function add_custom_block_attributes( $attrs ) {
		foreach ( self::$attributes as $key => $val ) {
			if ( ! isset( $attrs[ $key ] ) ) {
				$attrs[ $key ] = array(
					'type'    => ! empty( $val['type'] ) ? $val['type'] : 'string',
					'default' => ! empty( $val['default'] ) ? $val['default'] : '',
				);
			}
		}

		return $attrs;
	}
}

// Initialize the class.
new Fix_Third_Party_Block_Issues();
