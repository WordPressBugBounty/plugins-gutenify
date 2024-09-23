<?php

namespace gutenify;
/**
 *  GlobalCode functions
 *
 * @package Gutenify
 */
if (!defined('ABSPATH')) {
    exit;
}
/**
 * GlobalCode Class
 */
class GlobalCode
{

   private static $settings;
     
    /**
     * Constructor
     */
    function __construct()
    {
        self::$settings = gutenify_settings();
        add_action('wp_head', array(__CLASS__, 'global_header_code'));
        add_action('wp_body_open', array(__CLASS__, 'global_body_open_code'));
        add_action('wp_footer', array(__CLASS__, 'global_footer_code'));
    }
    /**
     * Header code display in frontend.
     *
     * @return string|null
     */
    public static function global_header_code()
    {
        if(!empty(self::$settings['global_header_code'])){
           echo self::$settings['global_header_code'];
        }
    }
    /**
     * Body Open code display in frontend.
     *
     * @return string|null
     */
    public static function global_body_open_code()
    {
        if(!empty(self::$settings['global_body_open_code'])){
            echo self::$settings['global_body_open_code'];
         }
    }
    /**
     * Footer code display in frontend.
     *
     * @return string|null
     */
    public static function global_footer_code()
    {
        if(!empty(self::$settings['global_footer_code'])){
            echo self::$settings['global_footer_code'];
         }
    }
}
new GlobalCode();
