<?php
namespace gutenify;

defined( 'ABSPATH' ) || exit;

/**
 * Extends core/button with an icon and hover effects (motion, background).
 * Hover colors and tablet/mobile styling come from core's block states (WP 7.1+).
 *
 * The effects live in one static stylesheet (styles/frontend.scss) shared with
 * the editor. This class only adds the classes and CSS variables it reads, and
 * mirrors fx.js, so keep get_fx() in sync with it. The allowed values come from
 * presets.json, which both sides read.
 */
class Core_Button {
	/**
	 * Icon hashes already written to the stylesheet during this request.
	 *
	 * @var string[]
	 */
	private static $emitted_icons = array();

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_style' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_editor_style' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'register_editor_script' ) );
		add_filter( 'gutenify_render_block_core/button', array( __CLASS__, 'render' ), 10, 4 );

		// The legacy Gutenify buttons are replaced by core's once core has block states.
		add_filter( 'register_block_type_args', array( __CLASS__, 'hide_legacy_blocks' ), 10, 2 );
		$constants = Helpers::plugin_constants();
		add_filter( $constants['plugin_main_slug'] . '--editor--localized-vars', array( __CLASS__, 'add_editor_flags' ) );
	}

	/**
	 * Whether core (WP 7.1+) or the Gutenberg plugin has per-block states, which
	 * is what covers the legacy block's hover and tablet/mobile options.
	 *
	 * @return bool
	 */
	public static function core_has_block_states() {
		return function_exists( 'wp_normalize_state_preset_vars' ) || function_exists( 'gutenberg_normalize_state_preset_vars' );
	}

	/**
	 * Hides the legacy blocks from the inserter, but keeps them registered so
	 * saved content still renders and edits.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_name Block name.
	 * @return array
	 */
	public static function hide_legacy_blocks( $args, $block_name ) {
		if ( in_array( $block_name, array( 'gutenify/button', 'gutenify/buttons' ), true ) && self::core_has_block_states() ) {
			$args['supports']             = isset( $args['supports'] ) && is_array( $args['supports'] ) ? $args['supports'] : array();
			$args['supports']['inserter'] = false;
		}
		return $args;
	}

	/**
	 * Tells the editor script about the above, so it hides the same blocks.
	 *
	 * @param array $vars Editor variables.
	 * @return array
	 */
	public static function add_editor_flags( $vars ) {
		$vars['core_block_states'] = self::core_has_block_states();
		return $vars;
	}

	private static function handle() {
		$constants = Helpers::plugin_constants();
		return $constants['plugin_main_slug'] . '-extend-core-button';
	}

	public static function register_style() {
		$base_dir = Helpers::core_base_dir();
		$base_url = Helpers::core_base_url();
		$assets   = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/core-button/frontend.asset.php', $base_dir ) );

		wp_register_style( self::handle(), $base_url . 'dist/non-blocks/extend/core-button/frontend.css', array(), $assets['version'] );
	}

	/**
	 * The editor canvas loads the same stylesheet as the frontend. On the
	 * frontend it is only enqueued when a button uses an effect or icon.
	 */
	public static function enqueue_editor_style() {
		if ( is_admin() ) {
			wp_enqueue_style( self::handle() );
		}
	}

	public static function register_editor_script() {
		$base_dir = Helpers::core_base_dir();
		$base_url = Helpers::core_base_url();
		$assets   = Helpers::asset_file_values( sprintf( '%sdist/non-blocks/extend/core-button/index.asset.php', $base_dir ) );
		$handle   = self::handle() . '-editor';

		wp_register_script( $handle, $base_url . 'dist/non-blocks/extend/core-button/index.js', $assets['dependencies'], $assets['version'], true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, $base_url . 'dist/non-blocks/extend/core-button/index.css', array(), $assets['version'] );
		wp_enqueue_style( $handle );
	}

	private static function presets() {
		static $presets = null;
		if ( null === $presets ) {
			$presets = wp_json_file_decode( __DIR__ . '/presets.json', array( 'associative' => true ) );
			if ( ! is_array( $presets ) ) {
				// Fail closed: no effects rather than a fatal error.
				$presets = array(
					'defaults'   => array(
						'duration'     => 250,
						'tintStrength' => 20,
						'easing'       => 'ease',
					),
					'limits'     => array(
						'duration'     => array( 50, 1500 ),
						'tintStrength' => array( 5, 40 ),
						'iconSvgMax'   => 20000,
					),
					'motion'     => array(),
					'background' => array(),
					'nudge'      => array(),
					'easing'     => array( 'ease' => 'ease' ),
				);
			}
		}
		return $presets;
	}

	/**
	 * Allows only a plain length such as `1.4em` or `12px`. Anything else is
	 * dropped, so a value can never carry extra CSS (url(), unbalanced
	 * brackets that swallow the next variable, ...).
	 *
	 * @param mixed $value Raw attribute value.
	 * @return string The value, or an empty string.
	 */
	private static function css_length( $value ) {
		$value = is_string( $value ) || is_numeric( $value ) ? trim( (string) $value ) : '';
		return preg_match( '/^-?(?:\d+\.?\d*|\.\d+)(?:px|em|rem|%|vw|vh|vmin|vmax|ch|ex)?$/iD', $value ) ? $value : '';
	}

	/**
	 * Allows only a plain color: hex, rgb()/hsl() with numbers, a theme preset
	 * variable, or a color keyword. Anything else is dropped.
	 *
	 * @param mixed $value Raw attribute value.
	 * @return string The value, or an empty string.
	 */
	private static function css_color( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		return preg_match( '/^(?:#[0-9a-f]{3,8}|(?:rgb|hsl)a?\([\d\s.,%\/deg]+\)|var\(--wp--preset--[a-z0-9-]+--[a-z0-9-]+\)|[a-z]{3,20})$/iD', $value ) ? $value : '';
	}

	/**
	 * Turns core's `var:preset|color|slug` form into a CSS variable reference.
	 *
	 * @param mixed $value Style value.
	 * @return mixed
	 */
	private static function preset_to_var( $value ) {
		if ( is_string( $value ) && 0 === strpos( $value, 'var:preset|' ) ) {
			return 'var(--wp--' . str_replace( '|', '--', substr( $value, 4 ) ) . ')';
		}
		return $value;
	}

	private static function clamp( $value, $limits, $fallback ) {
		$number = is_numeric( $value ) ? (int) $value : 0;
		return $number > 0 ? min( $limits[1], max( $limits[0], $number ) ) : $fallback;
	}

	private static function key_in( $value, $allowed ) {
		return is_string( $value ) && in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Turns the block attributes into wrapper classes and CSS variables.
	 *
	 * @param array $attrs Block attributes.
	 * @return array{classes: string[], vars: array<string,string>, svg: string}
	 */
	public static function get_fx( $attrs ) {
		$presets  = self::presets();
		$icon     = isset( $attrs['gtfyIcon'] ) && is_array( $attrs['gtfyIcon'] ) ? $attrs['gtfyIcon'] : array();
		$hover    = isset( $attrs['gtfyHoverEffect'] ) && is_array( $attrs['gtfyHoverEffect'] ) ? $attrs['gtfyHoverEffect'] : array();
		$classes  = array();
		$vars     = array();
		$svg      = '';
		$raw      = isset( $icon['svg'] ) && is_string( $icon['svg'] ) ? trim( $icon['svg'] ) : '';
		$has_icon = '' !== $raw && 0 === stripos( $raw, '<svg' ) && strlen( $raw ) <= $presets['limits']['iconSvgMax'];

		if ( $has_icon ) {
			$svg       = $raw;
			$classes[] = 'gtfy-icon';
			$classes[] = ( isset( $icon['position'] ) && 'after' === $icon['position'] ) ? 'gtfy-icon-after' : 'gtfy-icon-before';
			if ( ! empty( $icon['hideText'] ) ) {
				$classes[] = 'gtfy-icon-only';
			}
			$size      = self::css_length( $icon['size'] ?? '' );
			$gap       = self::css_length( $icon['gap'] ?? '' );
			if ( '' !== $size ) {
				$vars['--gtfy-icon-size'] = $size;
			}
			if ( '' !== $gap ) {
				$vars['--gtfy-icon-gap'] = $gap;
			}
		}

		$motion     = self::key_in( $hover['motion'] ?? null, $presets['motion'] );
		$animate    = ! empty( $hover['animate'] );
		// An icon-only button has nothing else to show, so revealing the icon on hover makes no sense.
		$reveal     = $has_icon && empty( $icon['hideText'] ) && ! empty( $hover['iconReveal'] );
		$nudge      = $has_icon ? self::key_in( $hover['iconNudge'] ?? null, $presets['nudge'] ) : '';
		$background = self::key_in( $hover['background'] ?? null, $presets['background'] );

		if ( 0 === strpos( $background, 'sweep' ) ) {
			// A gradient hover is emitted by core as a `background` shorthand with !important, which resets the size and base color, so it can't be swept.
			$colors = isset( $attrs['style'][':hover']['color'] ) && is_array( $attrs['style'][':hover']['color'] ) ? $attrs['style'][':hover']['color'] : array();
			$fill   = self::css_color( self::preset_to_var( $colors['background'] ?? '' ) );
			if ( '' !== $fill && empty( $colors['gradient'] ) ) {
				$vars['--gtfy-fill'] = $fill;
				$classes[]           = 'gtfy-fx-sweep';
			} else {
				$background = '';
			}
		}

		if ( 'darken' === $background || 'lighten' === $background ) {
			$strength            = self::clamp( $hover['tintStrength'] ?? 0, $presets['limits']['tintStrength'], $presets['defaults']['tintStrength'] );
			$vars['--gtfy-tint'] = rtrim( rtrim( number_format( $strength / 100, 2, '.', '' ), '0' ), '.' );
		}

		if ( '' !== $motion ) {
			$classes[] = 'gtfy-motion-' . $motion;
		}
		if ( '' !== $background ) {
			$classes[] = 'gtfy-bg-' . $background;
		}
		if ( '' !== $nudge ) {
			$classes[] = 'gtfy-nudge';
			$classes[] = 'gtfy-nudge-' . $nudge;
		}
		if ( $reveal ) {
			$classes[] = 'gtfy-icon-reveal';
		}
		if ( ! empty( $hover['activePress'] ) ) {
			$classes[] = 'gtfy-active-press';
		}

		if ( '' !== $motion || '' !== $background || $animate || '' !== $nudge || $reveal ) {
			$classes[] = 'gtfy-fx';
			if ( '' !== $motion || '' !== $background || $animate ) {
				$classes[] = 'gtfy-fx-transition';
			}
			if ( '' !== $nudge || $reveal ) {
				$classes[] = 'gtfy-fx-icon';
			}
			if ( isset( $hover['animateOut'] ) && false === $hover['animateOut'] ) {
				$classes[] = 'gtfy-fx-instant';
			}
			$easing                  = isset( $hover['easing'] ) && is_string( $hover['easing'] ) && isset( $presets['easing'][ $hover['easing'] ] ) ? $hover['easing'] : $presets['defaults']['easing'];
			$vars['--gtfy-duration'] = self::clamp( $hover['duration'] ?? 0, $presets['limits']['duration'], $presets['defaults']['duration'] ) . 'ms';
			$vars['--gtfy-easing']   = $presets['easing'][ $easing ];
		}

		return array(
			'classes' => $classes,
			'vars'    => $vars,
			'svg'     => $svg,
		);
	}

	public static function render( $content, $block, $instance, $block_id ) {
		if ( empty( $block['blockName'] ) || 'core/button' !== $block['blockName'] ) {
			return $content;
		}

		$fx = self::get_fx( $block['attrs'] ?? array() );
		if ( empty( $fx['classes'] ) ) {
			return $content;
		}

		$processor = new \WP_HTML_Tag_Processor( $content );
		if ( ! $processor->next_tag() ) {
			return $content;
		}

		foreach ( $fx['classes'] as $class_name ) {
			$processor->add_class( $class_name );
		}

		$declarations = '';
		foreach ( $fx['vars'] as $name => $value ) {
			$declarations .= $name . ':' . $value . ';';
		}
		if ( '' !== $declarations ) {
			$existing = $processor->get_attribute( 'style' );
			$existing = is_string( $existing ) && '' !== trim( $existing ) ? rtrim( trim( $existing ), ';' ) . ';' : '';
			$processor->set_attribute( 'style', $existing . $declarations );
		}

		if ( '' !== $fx['svg'] ) {
			// One rule per distinct icon, however many buttons use it.
			$hash = substr( md5( $fx['svg'] ), 0, 8 );
			$processor->add_class( 'gtfy-icon-' . $hash );
			if ( ! in_array( $hash, self::$emitted_icons, true ) ) {
				self::$emitted_icons[] = $hash;
				wp_add_inline_style( self::handle(), '.gtfy-icon-' . $hash . '{--gtfy-icon:url("data:image/svg+xml,' . rawurlencode( $fx['svg'] ) . '")}' );
			}
		}

		wp_enqueue_style( self::handle() );

		return $processor->get_updated_html();
	}
}

Core_Button::init();
