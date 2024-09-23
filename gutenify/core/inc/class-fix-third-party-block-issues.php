<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Fix_Third_party_Block_Issues {
	public function __construct() {
		add_filter( 'wpforms_gutenberg_form_selector_attributes', array( __CLASS__, 'wpforms_gutenberg_form_selector_attributes' ) );
	}

	public static function wpforms_gutenberg_form_selector_attributes( $attrs ){
		if ( ! isset( $attrs['customCss'] ) ) {
			$attrs['customCss'] = array(
				'type' => 'string',
				'default' => ''
			);
		}

		if ( ! isset( $attrs['blockClientId'] ) ) {
			$attrs['blockClientId'] = array(
				'type' => 'string',
				'default' => ''
			);
		}

		if ( ! isset( $attrs[  'gutenifyStyles' ] ) ) {
			$attrs[  'gutenifyStyles'] = array(
				'type' => 'string',
				'default' => ''
			);
		}
		return $attrs;
	}
}

new Fix_Third_party_Block_Issues();
