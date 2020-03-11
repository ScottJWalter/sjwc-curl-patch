<?php
/**
 * Plugin Name: SJWC Curl Patch
 * Plugin URI:  https://scottjwalter.consulting
 * Description: Fork of Safly's curl patch that uses CloudFlare instead of Tencent
 * Version:     0.1.0
 * Author:      Scott J Walter Consulting
 * Author URI:  https://scottjwalter.consulting
 * Text Domain: sjwc-curlpatch
 * Domain Path: /languages
 *
 * @package SJWC_CurlPatch
 */
defined( 'ABSPATH' ) || die();


// Useful global constants.
define( 'SJWC_CURL_PATCH_VERSION'	, '0.1.0' );
define( 'SJWC_CURL_PATCH_URL'		, plugin_dir_url( __FILE__ ) );
define( 'SJWC_CURL_PATCH_PATH'		, plugin_dir_path( __FILE__ ) );
define( 'SJWC_CURL_PATCH_INC'		, SJWC_CURL_PATCH_PATH . 'includes/' );
define( 'SJWC_CURL_CACHE'			, WP_CONTENT_DIR . '/cache/sjwc' );
define( 'SJWC_CURL_PATCH_CACHE'		, SJWC_CURL_CACHE . '/curl-patch/' );
define( 'SJWC_CURL_PATCH_GLOBAL'    , 'sjwc_custom_curl_resolve' );
define( 'SJWC_CURL_PATCH_ACTION'    , 'sjwc_curl_patch' );


// Include files.
require_once SJWC_CURL_PATCH_INC . 'functions/core.php';

// Activation/Deactivation.
register_activation_hook( __FILE__, '\SJWC\CurlPatch\Core\activate' );
register_deactivation_hook( __FILE__, '\SJWC\CurlPatch\Core\deactivate' );

// Bootstrap.
SJWC\CurlPatch\Core\setup();

// Require Composer autoloader if it exists.
if ( file_exists( SJWC_CURL_PATCH_PATH . '/vendor/autoload.php' ) ) {
	require_once SJWC_CURL_PATCH_PATH . 'vendor/autoload.php';
}
