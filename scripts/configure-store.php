<?php
/**
 * Initialise the demo's settings once. Run through WP-CLI.
 */
if ( get_option( 'bitforma_store_configured' ) ) {
	WP_CLI::success( 'La configuración inicial ya está aplicada.' );
	return;
}

$options = array(
	'blogdescription'                     => 'Tienda de electrónica de demostración',
	'blog_public'                         => '0',
	'timezone_string'                     => 'Europe/Madrid',
	'woocommerce_default_country'         => 'ES:M',
	'woocommerce_currency'                => 'EUR',
	'woocommerce_currency_pos'            => 'right_space',
	'woocommerce_price_decimal_sep'       => ',',
	'woocommerce_price_thousand_sep'       => '.',
	'woocommerce_store_address'           => 'Calle de la Muestra, 18',
	'woocommerce_store_city'              => 'Madrid',
	'woocommerce_store_postcode'          => '28013',
	'woocommerce_allowed_countries'       => 'specific',
	'woocommerce_specific_allowed_countries' => array( 'ES' ),
	'woocommerce_ship_to_countries'       => 'specific',
	'woocommerce_specific_ship_to_countries' => array( 'ES' ),
	'woocommerce_default_customer_address' => 'base',
	'woocommerce_prices_include_tax'      => 'no',
	'woocommerce_tax_display_shop'        => 'incl',
	'woocommerce_tax_display_cart'        => 'incl',
	'woocommerce_calc_taxes'              => 'yes',
	'woocommerce_tax_based_on'            => 'shipping',
	'woocommerce_enable_guest_checkout'   => 'yes',
	'woocommerce_hide_out_of_stock_items' => 'no',
	'woocommerce_catalog_columns'       => 3,
	'woocommerce_catalog_rows'          => 4,
	'woocommerce_coming_soon'             => 'no',
	'woocommerce_permalinks'              => array(
		'product_base' => '/producto/',
		'category_base' => 'categoria-producto',
		'tag_base' => 'etiqueta-producto',
	),
);

foreach ( $options as $option => $value ) {
	update_option( $option, $value );
}

$pages = array(
	'shop'      => array( 'Tienda', 'tienda' ),
	'cart'      => array( 'Carrito', 'carrito' ),
	'checkout'  => array( 'Finalizar compra', 'finalizar-compra' ),
	'myaccount' => array( 'Mi cuenta', 'mi-cuenta' ),
);
foreach ( $pages as $key => list( $title, $slug ) ) {
	$id = wc_get_page_id( $key );
	if ( $id <= 0 ) {
		WP_CLI::error( "Falta la página de WooCommerce: {$key}." );
	}
	$result = wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_name' => $slug ), true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
}

$about = get_page_by_path( 'sobre-la-demo' );
if ( ! $about ) {
	$about_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Sobre la demo',
		'post_name'    => 'sobre-la-demo',
		'post_content' => "<!-- wp:paragraph -->\n<p>Bitforma es una tienda ficticia para evaluar WordPress y WooCommerce. Utiliza datos de comprador ficticios en las pruebas.</p>\n<!-- /wp:paragraph -->\n<!-- wp:paragraph -->\n<p>Los pedidos, el envío y el IVA del 21 % se usan para demostrar el funcionamiento de la tienda. El pago contra reembolso registra el pedido sin realizar ningún cobro ni envío.</p>\n<!-- /wp:paragraph -->",
	), true );
	if ( is_wp_error( $about_id ) ) {
		WP_CLI::error( $about_id->get_error_message() );
	}
}

$zone = null;
foreach ( WC_Shipping_Zones::get_zones() as $existing ) {
	if ( 'España · demostración' === $existing['zone_name'] ) {
		$zone = WC_Shipping_Zones::get_zone( $existing['zone_id'] );
		break;
	}
}
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'España · demostración' );
	$zone->set_zone_order( 0 );
	$zone->add_location( 'ES', 'country' );
	$zone->save();
}

$flat_rate_id = 0;
foreach ( $zone->get_shipping_methods() as $method ) {
	if ( 'flat_rate' === $method->id ) {
		$flat_rate_id = $method->get_instance_id();
		break;
	}
}
$flat_rate_id = $flat_rate_id ?: $zone->add_shipping_method( 'flat_rate' );
if ( ! $flat_rate_id ) {
	WP_CLI::error( 'No se pudo crear la tarifa de envío de demostración.' );
}
update_option( "woocommerce_flat_rate_{$flat_rate_id}_settings", array(
	'title'      => 'Envío estándar de demostración',
	'tax_status' => 'taxable',
	'cost'       => '4.09',
) );

foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
	if ( 'cod' !== $gateway->id ) {
		$gateway->update_option( 'enabled', 'no' );
	}
}
update_option( 'woocommerce_cod_settings', array(
	'enabled'            => 'yes',
	'title'              => 'Contra reembolso · prueba',
	'description'        => 'Confirma el pedido de demostración sin realizar ningún pago.',
	'instructions'       => 'Pedido simulado. Sin pago ni envío real.',
	'enable_for_methods' => array( "flat_rate:{$flat_rate_id}" ),
	'enable_for_virtual' => 'no',
) );

$tax_name = 'IVA demo';
$rates = WC_Tax::get_rates_for_tax_class( '' );
if ( ! in_array( $tax_name, array_column( $rates, 'tax_rate_name' ), true ) ) {
	$tax_id = WC_Tax::_insert_tax_rate( array(
		'tax_rate_country'  => 'ES',
		'tax_rate'          => '21.0000',
		'tax_rate_name'     => $tax_name,
		'tax_rate_priority' => 1,
		'tax_rate_shipping' => 1,
		'tax_rate_class'    => '',
	) );
	if ( ! $tax_id ) {
		WP_CLI::error( 'No se pudo crear el IVA de demostración.' );
	}
}

update_option( 'bitforma_store_configured', 1 );
WP_CLI::success( 'Configuración de España, páginas, IVA y compra de demostración aplicada.' );
