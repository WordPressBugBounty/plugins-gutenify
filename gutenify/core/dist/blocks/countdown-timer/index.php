<?php
/**
 * Plugin Name: Gutenify Countdown Block
 * Plugin URI: https://gutenify.com
 * Description: Gutenify Countdown - stores settings in post meta
 * Version: 1.0.0
 * Author: Gutenify
 *
 * @package gutenify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post meta for countdown timer settings.
 */
function gutenify_countdown_timer_register_meta() {
	$meta_fields = array(
		'_gutenify_countdown_timer_type'           => array(
			'type'    => 'string',
			'default' => 'fixed',
		),
		'_gutenify_countdown_timer_evergreen_hours' => array(
			'type'    => 'integer',
			'default' => 24,
		),
		'_gutenify_countdown_timer_evergreen_minutes' => array(
			'type'    => 'integer',
			'default' => 0,
		),
		'_gutenify_countdown_timer_date'           => array(
			'type'    => 'string',
			'default' => '',
		),
		'_gutenify_countdown_timer_timezone'       => array(
			'type'    => 'string',
			'default' => 'US/Eastern',
		),
		'_gutenify_countdown_timer_end_action'     => array(
			'type'    => 'string',
			'default' => 'none',
		),
		'_gutenify_countdown_timer_hide_selector'  => array(
			'type'    => 'string',
			'default' => '',
		),
		'_gutenify_countdown_timer_redirect_url'   => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	foreach ( $meta_fields as $meta_key => $args ) {
		register_post_meta(
			'',
			$meta_key,
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => $args['type'],
				'default'       => $args['default'],
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'gutenify_countdown_timer_register_meta' );

/**
 * Register site-wide settings for countdown timer.
 */
function gutenify_countdown_timer_register_settings() {
	register_setting(
		'options',
		'_gutenify_countdown_timer_site_data',
		array(
			'type'         => 'object',
			'show_in_rest' => array(
				'schema' => array(
					'type'       => 'object',
					'properties' => array(
						'countdownTimerType' => array( 'type' => 'string' ),
						'evergreenHours'    => array( 'type' => 'integer' ),
						'evergreenMinutes'  => array( 'type' => 'integer' ),
						'date'              => array( 'type' => 'string' ),
						'timezone'          => array( 'type' => 'string' ),
						'endAction'         => array( 'type' => 'string' ),
						'hideSelector'      => array( 'type' => 'string' ),
						'redirectUrl'       => array( 'type' => 'string' ),
						'evergreenRestart'  => array( 'type' => 'boolean' ),
					),
				),
			),
			'default'      => array(
				'countdownTimerType' => 'fixed',
				'evergreenHours'    => 24,
				'evergreenMinutes'  => 0,
				'evergreenRestart'  => false,
				'date'              => '',
				'timezone'          => 'US/Eastern',
				'endAction'         => 'none',
				'hideSelector'      => '',
				'redirectUrl'       => '',
			),
		)
	);
}
add_action( 'init', 'gutenify_countdown_timer_register_settings' );

/**
 * Registers the block.
 */
function gutenify_countdown_timer_register_block() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'gutenify_countdown_timer_render',
		)
	);
}
add_action( 'init', 'gutenify_countdown_timer_register_block' );

/**
 * Converts a font size value to a fluid clamp() value.
 * Min = 75% of value, viewport range 320px–1920px.
 */
function gutenify_get_fluid_font_size( $font_size ) {
	if ( empty( $font_size ) ) {
		return $font_size;
	}

	if ( ! preg_match( '/^([\d.]+)(.+)$/', $font_size, $matches ) ) {
		return $font_size;
	}

	$value = (float) $matches[1];
	$unit  = $matches[2];

	$value_px = $value;
	if ( 'rem' === $unit || 'em' === $unit ) {
		$value_px = $value * 16;
	}

	$min_px       = $value_px * 0.75;
	$min_viewport = 320;
	$max_viewport = 1920;
	$slope        = ( $value_px - $min_px ) / ( $max_viewport - $min_viewport );
	$intercept    = $min_px - $slope * $min_viewport;
	$slope_vw     = round( $slope * 100, 4 );
	$intercept_rem = round( $intercept / 16, 4 );

	$min = 'px' === $unit
		? $min_px . 'px'
		: round( $value * 0.75, 4 ) . $unit;

	return "clamp({$min}, {$intercept_rem}rem + {$slope_vw}vw, {$font_size})";
}

/**
 * Render callback for the block - outputs HTML with post meta as data attributes.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block HTML.
 */
function gutenify_countdown_timer_render( $attributes, $content, $block ) {
	$post_id = $block->context['postId'] ?? get_the_ID();

	if ( ! $post_id ) {
		return $content;
	}

	$storage_level = $attributes['storageLevel'] ?? 'postMeta';

	$type = $attributes['countdownTimerType'] ?? 'fixed';

	if ( 'blockLevel' === $storage_level ) {
		$evg_hours      = (int) ( $attributes['evergreenHours'] ?? 24 );
		$evg_minutes    = (int) ( $attributes['evergreenMinutes'] ?? 0 );
		$date           = $attributes['date'] ?? '';
		$timezone       = wp_timezone_string();
		$end_action     = $attributes['endAction'] ?? 'none';
		$hide_selector  = $attributes['hideSelector'] ?? '';
		$redirect_url   = $attributes['redirectUrl'] ?? '';
	} elseif ( 'siteLevel' === $storage_level ) {
		$site_data      = get_option( '_gutenify_countdown_timer_site_data', array() );
		$evg_hours      = (int) ( $site_data['evergreenHours'] ?? 24 );
		$evg_minutes    = (int) ( $site_data['evergreenMinutes'] ?? 0 );
		$date           = $site_data['date'] ?? '';
		$timezone       = wp_timezone_string();
		$end_action     = $site_data['endAction'] ?? 'none';
		$hide_selector  = $site_data['hideSelector'] ?? '';
		$redirect_url   = $site_data['redirectUrl'] ?? '';
	} else {
		$evg_hours      = get_post_meta( $post_id, '_gutenify_countdown_timer_evergreen_hours', true );
		$evg_hours      = $evg_hours !== '' ? (int) $evg_hours : 24;
		$evg_minutes    = get_post_meta( $post_id, '_gutenify_countdown_timer_evergreen_minutes', true );
		$evg_minutes    = $evg_minutes !== '' ? (int) $evg_minutes : 0;
		$date           = get_post_meta( $post_id, '_gutenify_countdown_timer_date', true );
		$timezone       = wp_timezone_string();
		$end_action     = get_post_meta( $post_id, '_gutenify_countdown_timer_end_action', true );
		$hide_selector  = get_post_meta( $post_id, '_gutenify_countdown_timer_hide_selector', true );
		$redirect_url   = get_post_meta( $post_id, '_gutenify_countdown_timer_redirect_url', true );
		$evg_restart    = get_post_meta( $post_id, '_gutenify_countdown_timer_evergreen_restart', true );
	}

	$evg_restart = 'siteLevel' === $storage_level ? ( $site_data['evergreenRestart'] ?? false ) : ( 'blockLevel' === $storage_level ? ( $attributes['evergreenRestart'] ?? false ) : $evg_restart );
	$evg_restart = filter_var( $evg_restart, FILTER_VALIDATE_BOOLEAN );

	if ( empty( $hide_selector ) && 'hide' === $end_action ) {
		$hide_selector = '.gutenify-countdown-timer-hide';
	}

	if ( 'fixed' === $type && empty( $date ) ) {
		return $content;
	}

	// Default to US/Eastern if no timezone is set.
	if ( empty( $timezone ) ) {
		$timezone = 'US/Eastern';
	}

	$timestamp = 0;

	if ( 'evergreen' === $type ) {
		// Calculate the full duration relative to now so the initial server render
		// shows the maximum time before the frontend JS kicks in.
		$timestamp = current_time( 'timestamp', true ) + ( $evg_hours * 3600 ) + ( $evg_minutes * 60 );
	} else {
		try {
			// Create the DateTime object without timezone first to parse the stored date.
			$dt = new DateTime( $date );

			// Re-interpret the date in the selected timezone
			// (strip any offset from the stored date and apply the selected timezone).
			$tz = new DateTimeZone( $timezone );
			$dt = new DateTime( $dt->format( 'Y-m-d H:i:s' ), $tz );

			$timestamp = $dt->getTimestamp();
		} catch ( Exception $e ) {
			return $content;
		}
	}

	// Compute initial countdown values server-side to avoid flash of empty content.
	$remaining = $timestamp - current_time( 'timestamp', true );
	if ( $remaining > 0 ) {
		$init_days    = str_pad( floor( $remaining / 86400 ), 2, '0', STR_PAD_LEFT );
		$init_hours   = str_pad( floor( ( $remaining % 86400 ) / 3600 ), 2, '0', STR_PAD_LEFT );
		$init_minutes = str_pad( floor( ( $remaining % 3600 ) / 60 ), 2, '0', STR_PAD_LEFT );
		$init_seconds = str_pad( $remaining % 60, 2, '0', STR_PAD_LEFT );
	} else {
		$init_days    = '00';
		$init_hours   = '00';
		$init_minutes = '00';
		$init_seconds = '00';
	}

	$labels = array(
		'days'    => $attributes['daysLabel'] ?? 'DAYS',
		'hours'   => $attributes['hoursLabel'] ?? 'HOURS',
		'minutes' => $attributes['minutesLabel'] ?? 'MINUTES',
		'seconds' => $attributes['secondsLabel'] ?? 'SECONDS',
	);

	$label_style = '';

	if ( ! empty( $attributes['labelColor'] ) ) {
		$label_style .= 'color:' . esc_attr( $attributes['labelColor'] ) . ';';
	}
	if ( ! empty( $attributes['labelFontSize'] ) ) {
		$label_style .= 'font-size:' . esc_attr( gutenify_get_fluid_font_size( $attributes['labelFontSize'] ) ) . ';';
	}
	if ( ! empty( $attributes['labelLineHeight'] ) ) {
		$label_style .= 'line-height:' . esc_attr( $attributes['labelLineHeight'] ) . ';';
	}

	$label_style_attr = $label_style ? ' style="' . $label_style . '"' : '';

	$wrapper_attributes = get_block_wrapper_attributes( array(
		'data-type'          => esc_attr( $type ),
		'data-post-id'       => esc_attr( $post_id ),
		'data-evergreen-hours'   => esc_attr( $evg_hours ),
		'data-evergreen-minutes' => esc_attr( $evg_minutes ),
		'data-timestamp'     => esc_attr( $timestamp ),
		'data-end-action'    => esc_attr( $end_action ),
		'data-hide-selector' => esc_attr( $hide_selector ),
		'data-redirect-url'  => esc_attr( $redirect_url ),
		'data-evergreen-restart' => esc_attr( $evg_restart ? 'true' : 'false' ),
	) );

	$separator  = $attributes['separator'] ?? '';
	$item_gap   = $attributes['itemGap'] ?? '';
	$ul_style   = $item_gap ? ' style="gap:' . esc_attr( $item_gap ) . ';"' : '';

	$item_border       = $attributes['itemBorder'] ?? array();
	$item_border_style = '';
	if ( ! empty( $item_border['color'] ) ) {
		$item_border_style .= 'border-color:' . esc_attr( $item_border['color'] ) . ';';
	}
	if ( ! empty( $item_border['width'] ) ) {
		$item_border_style .= 'border-width:' . esc_attr( $item_border['width'] ) . ';';
	}
	if ( ! empty( $item_border['style'] ) ) {
		$item_border_style .= 'border-style:' . esc_attr( $item_border['style'] ) . ';';
	}
	if ( ! empty( $attributes['itemBorderRadius'] ) ) {
		$item_border_style .= 'border-radius:' . esc_attr( $attributes['itemBorderRadius'] ) . ';';
	}
	if ( ! empty( $attributes['itemPadding'] ) ) {
		$item_border_style .= 'padding:' . esc_attr( $attributes['itemPadding'] ) . ';';
	}
	$item_style_attr = $item_border_style ? ' style="' . $item_border_style . '"' : '';

	ob_start();
	?>
	<div <?php echo wp_kses_post( $wrapper_attributes ); ?>>
		<ul<?php echo $ul_style; ?>>
			<li<?php echo $item_style_attr; ?>>
				<span class="days"><?php echo esc_html( $init_days ); ?></span>
				<small<?php echo $label_style_attr; ?>><?php echo esc_html( $labels['days'] ); ?></small>
			</li>
			<?php if ( $separator ) : ?>
				<li class="separator"><?php echo esc_html( $separator ); ?></li>
			<?php endif; ?>
			<li<?php echo $item_style_attr; ?>>
				<span class="hours"><?php echo esc_html( $init_hours ); ?></span>
				<small<?php echo $label_style_attr; ?>><?php echo esc_html( $labels['hours'] ); ?></small>
			</li>
			<?php if ( $separator ) : ?>
				<li class="separator"><?php echo esc_html( $separator ); ?></li>
			<?php endif; ?>
			<li<?php echo $item_style_attr; ?>>
				<span class="minutes"><?php echo esc_html( $init_minutes ); ?></span>
				<small<?php echo $label_style_attr; ?>><?php echo esc_html( $labels['minutes'] ); ?></small>
			</li>
			<?php if ( $separator ) : ?>
				<li class="separator"><?php echo esc_html( $separator ); ?></li>
			<?php endif; ?>
			<li<?php echo $item_style_attr; ?>>
				<span class="seconds"><?php echo esc_html( $init_seconds ); ?></span>
				<small<?php echo $label_style_attr; ?>><?php echo esc_html( $labels['seconds'] ); ?></small>
			</li>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
