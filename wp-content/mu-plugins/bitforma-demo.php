<?php
/**
 * Plugin Name: Bitforma Demo
 * Description: Intercepta los correos de WordPress y WooCommerce en la tienda de demostración.
 */

add_filter( 'pre_wp_mail', '__return_true' );
