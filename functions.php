<?php
/**
 * Twenty Seventeen Child functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen_Child
 * @since 1.0
 */

define("THEME_CHILD_DIR", get_stylesheet_directory());
define("THEME_CHILD_DIR_URI", get_stylesheet_directory_uri());
   
/*** 'tsc' is 'twentyseventeen-child' ***/

/**
 * Sets up theme defaults and registers support for various WP features.
 */
function tsc_setup() {
    load_child_theme_textdomain( 'tsc', THEME_CHILD_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'tsc_setup' );

// Styles and scripts in frontend
function tsc_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'tsc_enqueue_styles' );

// Styles and scripts in backend
function tsc_admin_scripts() {
    if( is_admin() && class_exists( 'WooCommerce' ) ){
        wp_enqueue_script( 'tsc_admin_js', THEME_CHILD_DIR_URI . '/assets/js/admin.js', array('jquery') );
    }

}
add_action( 'admin_enqueue_scripts', 'tsc_admin_scripts' );

//Required files
require_once ( THEME_CHILD_DIR . '/inc/helpers.php');

require_once ( THEME_CHILD_DIR . '/inc/hooks.php');
