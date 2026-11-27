<?php
/**
 * Verify the imported demo's catalog and core store settings through WP-CLI.
 */
if ( ! class_exists( 'WooCommerce' ) || 'wp-woo' !== get_stylesheet() ) {
	WP_CLI::error( 'WooCommerce y Bitforma deben estar activos.' );
}

foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $page ) {
	if ( 'publish' !== get_post_status( wc_get_page_id( $page ) ) ) {
		WP_CLI::error( "Falta la página de WooCommerce: $page." );
	}
}

$counts = array( 'productos' => 0, 'variables' => 0, 'variaciones' => 0, 'ofertas' => 0, 'agotados' => 0 );
foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) ) as $product ) {
	++$counts['productos'];
	$counts['ofertas'] += (int) $product->is_on_sale();
	$counts['agotados'] += (int) ! $product->is_in_stock();
	if ( $product->is_type( 'variable' ) ) {
		++$counts['variables'];
		$counts['variaciones'] += count( $product->get_children() );
	}

	$image = $product->get_image_id();
	if ( ! $product->get_sku() || ! $product->get_name() || ! $product->get_short_description()
		|| ! $product->get_description() || '' === $product->get_price() || ! $product->get_category_ids()
		|| ! $image || ! is_readable( (string) get_attached_file( $image ) )
		|| ! get_post_meta( $image, '_wp_attachment_image_alt', true ) ) {
		WP_CLI::error( "Ficha incompleta para el producto {$product->get_id()}." );
	}
}

$counts['categorías'] = count( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) ) );
if ( $counts['productos'] < 20 || $counts['categorías'] < 4 || ! $counts['variaciones'] || ! $counts['ofertas'] || ! $counts['agotados'] ) {
	WP_CLI::error( 'El catálogo no cumple los mínimos esperados de la demostración.' );
}

if ( 'yes' !== ( get_option( 'woocommerce_cod_settings' )['enabled'] ?? '' ) || 'EUR' !== get_woocommerce_currency() ) {
	WP_CLI::error( 'La tienda debe usar euros y tener activo el contra reembolso de prueba.' );
}

WP_CLI::log( wp_json_encode( $counts, JSON_UNESCAPED_UNICODE ) );
WP_CLI::success( 'Configuración y catálogo comprobados.' );
