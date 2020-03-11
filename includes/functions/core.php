<?php
/**
 * Core plugin functionality.
 *
 * @package SJWC\CurlPatch
 */

namespace SJWC\CurlPatch\Core;

use \WP_Error as WP_Error;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	$today = date('Y-m-d');
	$custom_curl_resolve_cache = SJWC_CURL_PATCH_CACHE . $today . '.txt';

	if ( file_exists( $custom_curl_resolve_cache ) ) {
		$custom_curl_resolve = unserialize( file_get_contents( $custom_curl_resolve_cache ) );
	} else {
		$custom_curl_resolve = array(
			'api.wordpress.org:80:'        . get_host_by_name( 'api.wordpress.org' ), 
			'api.wordpress.org:443:'       . get_host_by_name( 'api.wordpress.org' ), 
			'downloads.wordpress.org:80:'  . get_host_by_name( 'downloads.wordpress.org' ),
			'downloads.wordpress.org:443:' . get_host_by_name( 'downloads.wordpress.org' )
		);
		file_put_contents( $custom_curl_resolve_cache, serialize( $custom_curl_resolve ) );
	}

	$if_custom_curl_resolve = TRUE;
	foreach ( $custom_curl_resolve as $value ) {
		$value = explode( ':', $value );

		if ( ! filter_var( $value['2'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$if_custom_curl_resolve = FALSE;
			break;
		}
	}

	$GLOBALS[SJWC_CURL_PATCH_GLOBAL] = $custom_curl_resolve;
	if ( $if_custom_curl_resolve ) {
		add_action( 'http_api_curl', $n( 'custom_curl_resolve' ), 10, 3 );
	}


	do_action( SJWC_CURL_PATCH_ACTION . '.loaded' );
}


function custom_curl_resolve($handle, $r, $url) {
	curl_setopt( $handle, CURLOPT_RESOLVE, $GLOBALS[SJWC_CURL_PATCH_GLOBAL] );

	if (strstr( $url, 'wordpress' )) {
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, FALSE );
	}
}


function get_host_by_name( $domain ) {
	// Link to CloudFlare's DNS resolver
	# curl -H 'accept: application/dns-json' 'https://cloudflare-dns.com/dns-query?name=' . $domain . '&type=A'
	$dns = json_decode( wp_remote_retrieve_body( wp_remote_get( 
		'https://cloudflare-dns.com/dns-query?name=' . $domain . '&type=A'
	) ) );

	$ip	= explode( ';', $dns['Answer']['data'] );

	foreach ($ip as $value) {
		if ( filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return $value;
		}
	}

	return gethostbyname( $domain );
}


function create_dir( $path, $permission = 0755 ) {
	if ( ! file_exists( $path ) ) {
		create_dir( dirname( $path ) );
		mkdir( $path, $permission );
	}
}


function remove_dir( $dir ) {
	if ( is_dir( $dir ) ) {
		#$objects = scandir( $dir );
	
		# foreach ( $objects as $object ) {
		foreach ( scandir( $dir ) as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( filetype( $dir . "/" . $object ) == "dir" ) {
					remove_dir( $dir . "/" . $object ); 
				} else {
					unlink( $dir . "/" . $object );
				}
			}
		}

		#reset( $objects );
		rmdir( $dir );
	}
}


/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {
	create_dir( SJWC_CURL_PATCH_CACHE );
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {
	remove_dir( SJWC_CURL_PATCH_CACHE );
}
