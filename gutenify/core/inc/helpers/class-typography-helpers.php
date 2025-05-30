<?php
/**
 * Typography Helpers class.
 *
 * Provides utility functions to enqueue Fonts based on theme.json
 * typography settings and to modify theme.json font family data accordingly.
 *
 * @package gutenify
 * @subpackage Typography
 * @since 1.0.0
 */

namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class to manage typography-related functionalities
 * such as enqueueing Google Fonts and modifying theme.json data.
 */
class Typography_Helpers {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// Enqueue fonts for blocks.
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_fonts' ) );

		// Filter the theme JSON data for user modifications.
		add_filter( 'wp_theme_json_data_user', array( __CLASS__, 'update_theme_json' ), 1 );
	}

	/**
	 * Generate Google Fonts URL parts from font configuration.
	 *
	 * @param array $font_config Font configuration array.
	 * @return string Google Fonts URL query part.
	 */
	public static function generate_google_fonts_url_parts( $font_config ) {
		if ( empty( $font_config['fontFace'] ) || empty( $font_config['name'] ) ) {
			return '';
		}

		// Replace spaces in font name with '+' for URL encoding.
		$font_family = str_replace( ' ', '+', $font_config['name'] );
		$styles      = array();

		foreach ( $font_config['fontFace'] as $face ) {
			$ital     = ( strtolower( $face['fontStyle'] ) === 'italic' ) ? 1 : 0;
			$weight   = $face['fontWeight'];
			$styles[] = "{$ital},{$weight}";
		}

		// Remove duplicate styles and sort them.
		$styles = array_unique( $styles );
		sort( $styles );

		// Build style query string for Google Fonts URL.
		$style_query = implode( ';', $styles );

		return "family={$font_family}:ital,wght@{$style_query}";
	}

	/**
	 * Enqueue Google Fonts styles based on theme.json fontFamilies.
	 */
	public static function enqueue_fonts() {
		$constants       = Helpers::plugin_constants();
		$version         = $constants['plugin_main_version'];
		$demo_url        = $constants['authorDemoWebSite'];
		$theme_json_data = \WP_Theme_JSON_Resolver::get_user_data();
		$data            = $theme_json_data->get_data();
		$font_settings   = array();
		if ( isset( $data['settings']['typography']['fontFamilies'] ) ) {
			foreach ( $data['settings']['typography']['fontFamilies'] as $font ) {
				if ( ! empty( $font['fontFace'] ) ) {
					foreach ( $font['fontFace'] as $font_face ) {
						// Include fonts hosted on demo url or those without a 'src' attribute.
						if ( ! empty( $font_face['src'] ) && false !== strpos( $font_face['src'], $demo_url ) ) {
							$font_settings[ $font['slug'] ] = self::generate_google_fonts_url_parts( $font );
						} elseif ( empty( $font_face['src'] ) ) {
							$font_settings[ $font['slug'] ] = self::generate_google_fonts_url_parts( $font );
						}
					}
				}
			}
			if ( ! empty( $font_settings ) ) {
				// Combine all font queries separated by '&'.
				$final_font_settings = implode( '&', $font_settings );

				// Enqueue Google Fonts stylesheet.
				wp_enqueue_style( 'gutenify-bundle', esc_url( wptt_get_webfont_url( "https://fonts.googleapis.com/css2?{$final_font_settings}&display=swap" ) ), array(), $version );
			}
		}
	}

	/**
	 * Modify theme.json data to clean up font sources.
	 *
	 * @param WP_Theme_JSON $theme_json Theme JSON data object.
	 * @return WP_Theme_JSON Modified theme JSON data.
	 */
	public static function update_theme_json( $theme_json ) {
		// Get current theme.json data as array.
		$data = $theme_json->get_data();

		// Append the custom font to existing fontFamilies.
		if ( isset( $data['settings']['typography']['fontFamilies']['custom'] ) ) {
			foreach ( $data['settings']['typography']['fontFamilies']['custom'] as $font_key => $font ) {
				if ( ! empty( $font['fontFace'] ) ) {
					foreach ( $font['fontFace'] as $font_face_key => $font_face ) {
						if ( ! empty( $font_face['src'] ) && false !== strpos( $font_face['src'], 'demo.gutenify.com' ) ) {
							unset( $data['settings']['typography']['fontFamilies']['custom'][ $font_key ]['fontFace'][ $font_face_key ]['src'] );
						}
					}
				}
			}
		}

		// Update the theme JSON object with modified data.
		$theme_json->update_with( $data );

		return $theme_json;
	}
}

// Initialize the Typography_Helpers class.
Typography_Helpers::init();
