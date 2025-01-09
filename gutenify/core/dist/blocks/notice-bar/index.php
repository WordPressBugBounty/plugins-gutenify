<?php

namespace gutenify;

defined( 'ABSPATH' ) || exit;

class Notice_Bar{
	public static function init() {
		add_action('init', array(__CLASS__, 'register_block'));
		add_action('init',array( __CLASS__, 'register_patterns'));

	}

	public static function register_block() {
		register_block_type(__DIR__);
	}
	public static function register_patterns() {
		register_block_pattern(
			'gutenify/notice-bar-1',
			array(
				'title'       => __( 'Two buttons', 'wpdocs-my-plugin' ),
				'slug'       => 'gutenify/notice-bar-1',
				'blockTypes' => array( 'gutenify/notice-bar' ),
				'description' => _x( 'Two horizontal buttons, the left button is filled in, and the right button is outlined.', 'Block pattern description', 'wpdocs-my-plugin' ),
				'content'     => '<!-- wp:gutenify/notice-bar {"statusType":"success","border":{"width":"1px","color":"#12B76A"},"style":{"color":{"text":"","background":"#ECFDF3"}},"blockClientId":"3ebf7a50-69c3-11ef-840c-774056278650","metadata":{"patternName":"gutenify/notice-bar-1","name":"Two buttons"}} -->
	<div data-statustype="success" class="wp-block-gutenify-notice-bar gutenify-notice-bar has-background gutenify-section-3ebf7a50-69c3-11ef-840c-774056278650" style="background-color:#ECFDF3;position:relative;border-color:#12B76A;border-width:1px;border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-left-radius:4px;border-bottom-right-radius:4px"><!-- wp:group {"blockClientId":"3faf7fa0-69c3-11ef-840c-774056278650","style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"20px","right":"20px"}},"color":[],"border":[]},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group gutenify-section-3faf7fa0-69c3-11ef-840c-774056278650" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:group {"blockClientId":"3faee360-69c3-11ef-840c-774056278650","layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group gutenify-section-3faee360-69c3-11ef-840c-774056278650"><!-- wp:gutenify/icon {"blockClientId":"3fae6e30-69c3-11ef-840c-774056278650","gutenifyStyles":".gutenify-section-3fae6e30-69c3-11ef-840c-774056278650 .gutenify-icon-wrapper i {\n            font-size: 40px;\n        }.gutenify-section-3fae6e30-69c3-11ef-840c-774056278650 .gutenify-icon-wrapper img {\n            width: 40px;\n            max-width: 40px;\n        }.gutenify-section-3fae6e30-69c3-11ef-840c-774056278650 .gutenify-icon-wrapper .gutenify-image-icon-content img {\n        border-radius: undefinedpx;\n    }"} -->
	<div class="wp-block-gutenify-icon flex-col-item is-content-justification-center gutenify-icon-version-2 gutenify-section-3fae6e30-69c3-11ef-840c-774056278650"><div class="gutenify-icon-wrapper"><i class="fas fa-star"></i></div></div>
	<!-- /wp:gutenify/icon -->

	<!-- wp:paragraph {"blockClientId":"3fae9540-69c3-11ef-840c-774056278650"} -->
	<p class="gutenify-section-3fae9540-69c3-11ef-840c-774056278650">Thanks for using Gutenify!</p>
	<!-- /wp:paragraph --></div>
	<!-- /wp:group -->

	<!-- wp:gutenify/buttons {"blockClientId":"3faf3180-69c3-11ef-840c-774056278650"} -->
	<div class="wp-block-gutenify-buttons wp-block-buttons gutenify-buttons-version-2 gutenify-section-3faf3180-69c3-11ef-840c-774056278650"><!-- wp:gutenify/button {"blockClientId":"3faf0a70-69c3-11ef-840c-774056278650","text":"Plans","className":"wp-block-button gutenify-button-version-2"} -->
	<div class="wp-block-gutenify-button wp-block-button gutenify-button-version-2 gutenify-section-3faf0a70-69c3-11ef-840c-774056278650"><a class="gutenify-button-link wp-block-button__link"><span>Plans</span></a></div>
	<!-- /wp:gutenify/button --></div>
	<!-- /wp:gutenify/buttons --></div>
	<!-- /wp:group --><div style="position:absolute;top:0px;right:0px"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18px" height="18px" style="padding-top:2pxpx;padding-bottom:2pxpx;padding-right:2pxpx;padding-left:2pxpx;fill:" class="closeBtn" aria-hidden="true"><path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path></svg></div></div>
	<!-- /wp:gutenify/notice-bar -->',
			)
			);

			register_block_pattern(
			'gutenify/notice-bar-2',
			array(
				'title'       => __( 'Two buttons 2', 'wpdocs-my-plugin' ),
				'slug'       => 'gutenify/notice-bar-2',
				'blockTypes' => array( 'gutenify/notice-bar' ),
				'description' => _x( 'Two horizontal buttons, the left button is filled in, and the right button is outlined.', 'Block pattern description', 'wpdocs-my-plugin' ),
				'content'     => '<!-- wp:gutenify/notice-bar {"statusType":"info","border":{"width":"1px","color":"#2E90FA"},"style":{"color":{"text":"","background":"#EFF8FF"}},"blockClientId":"ca82de60-69eb-11ef-8bf3-e5c265a6c7c3","metadata":{"patternName":"gutenify/notice-bar-2","name":"Two buttons 2"}} -->
	<div data-statustype="info" class="wp-block-gutenify-notice-bar gutenify-notice-bar has-background gutenify-section-ca82de60-69eb-11ef-8bf3-e5c265a6c7c3" style="background-color:#EFF8FF;position:relative;border-color:#2E90FA;border-width:1px;border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-left-radius:4px;border-bottom-right-radius:4px"><!-- wp:group {"blockClientId":"cc354950-69eb-11ef-8bf3-e5c265a6c7c3","style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"20px","right":"20px"}},"color":[],"border":[]},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group gutenify-section-cc354950-69eb-11ef-8bf3-e5c265a6c7c3" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:group {"blockClientId":"cc345ef0-69eb-11ef-8bf3-e5c265a6c7c3","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
	<div class="wp-block-group gutenify-section-cc345ef0-69eb-11ef-8bf3-e5c265a6c7c3"><!-- wp:gutenify/icon {"blockClientId":"cc33e9c0-69eb-11ef-8bf3-e5c265a6c7c3","size":24,"gutenifyStyles":".gutenify-section-cc33e9c0-69eb-11ef-8bf3-e5c265a6c7c3 .gutenify-icon-wrapper i {\n            font-size: 24px;\n        }.gutenify-section-cc33e9c0-69eb-11ef-8bf3-e5c265a6c7c3 .gutenify-icon-wrapper img {\n            width: 24px;\n            max-width: 24px;\n        }.gutenify-section-cc33e9c0-69eb-11ef-8bf3-e5c265a6c7c3 .gutenify-icon-wrapper .gutenify-image-icon-content img {\n        border-radius: undefinedpx;\n    }"} -->
	<div class="wp-block-gutenify-icon flex-col-item is-content-justification-center gutenify-icon-version-2 gutenify-section-cc33e9c0-69eb-11ef-8bf3-e5c265a6c7c3"><div class="gutenify-icon-wrapper"><i class="fas fa-star"></i></div></div>
	<!-- /wp:gutenify/icon -->

	<!-- wp:paragraph {"blockClientId":"cc3437e0-69eb-11ef-8bf3-e5c265a6c7c3"} -->
	<p class="gutenify-section-cc3437e0-69eb-11ef-8bf3-e5c265a6c7c3">Thanks for using Gutenify!</p>
	<!-- /wp:paragraph --></div>
	<!-- /wp:group -->

	<!-- wp:gutenify/buttons {"blockClientId":"cc352240-69eb-11ef-8bf3-e5c265a6c7c3"} -->
	<div class="wp-block-gutenify-buttons wp-block-buttons gutenify-buttons-version-2 gutenify-section-cc352240-69eb-11ef-8bf3-e5c265a6c7c3"><!-- wp:gutenify/button {"blockClientId":"cc348600-69eb-11ef-8bf3-e5c265a6c7c3","text":"Plans","className":"wp-block-button gutenify-button-version-2"} -->
	<div class="wp-block-gutenify-button wp-block-button gutenify-button-version-2 gutenify-section-cc348600-69eb-11ef-8bf3-e5c265a6c7c3"><a class="gutenify-button-link wp-block-button__link"><span>Plans</span></a></div>
	<!-- /wp:gutenify/button --></div>
	<!-- /wp:gutenify/buttons --></div>
	<!-- /wp:group --><div style="position:absolute;top:0px;right:0px"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18px" height="18px" style="padding-top:2pxpx;padding-bottom:2pxpx;padding-right:2pxpx;padding-left:2pxpx;fill:" class="closeBtn" aria-hidden="true"><path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path></svg></div></div>
	<!-- /wp:gutenify/notice-bar -->',
			)
			);
	}
}

Notice_Bar::init();


