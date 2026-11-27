<?php
/**
 * Plugin Name: WP Woo Blocks
 * Description: Bloques Gutenberg del proyecto.
 * Version: 0.1.0
 * Text Domain: wp-woo-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', static function () {
	register_block_type( __DIR__ . '/blocks/nota' );
} );
