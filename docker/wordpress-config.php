<?php

$codespace = getenv( 'CODESPACE_NAME' );
$forwarding_domain = getenv( 'GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN' );
$codespace_host = $codespace && $forwarding_domain
	? "{$codespace}-8080.{$forwarding_domain}"
	: '';

$local_url = getenv( 'WP_LOCAL_URL' );
$site_url = $codespace_host ? "https://{$codespace_host}" : $local_url;
$local_host = parse_url( $local_url, PHP_URL_HOST );
$local_port = parse_url( $local_url, PHP_URL_PORT );
$local_authority = $local_port ? "$local_host:$local_port" : $local_host;

// The Codespaces proxy can send the local authority in Host and the browser's host separately.
if ( $codespace_host
	&& $codespace_host === ( $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '' )
	&& 'https' === ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' )
) {
	$_SERVER['HTTP_HOST'] = $codespace_host;
	$_SERVER['SERVER_PORT'] = '443';
} elseif ( $local_authority === ( $_SERVER['HTTP_HOST'] ?? '' ) ) {
	$site_url = $local_url;
}

define( 'WP_HOME', $site_url );
define( 'WP_SITEURL', $site_url );
define( 'WP_DEFAULT_THEME', 'twentytwentyfive' );
define( 'WP_ENVIRONMENT_TYPE', 'development' );
define( 'WP_DEVELOPMENT_MODE', 'theme' );
define( 'WP_DEBUG_LOG', '/tmp/bitforma-wordpress.log' );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );

if ( 'https' === parse_url( $site_url, PHP_URL_SCHEME ) ) {
	define( 'FORCE_SSL_ADMIN', true );

	// GitHub termina TLS antes de entregar la petición al puerto HTTP de Apache.
	if ( parse_url( $site_url, PHP_URL_HOST ) === ( $_SERVER['HTTP_HOST'] ?? '' ) ) {
		$_SERVER['HTTPS'] = 'on';
	}
}
