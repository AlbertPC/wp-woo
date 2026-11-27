<?php
/**
 * Styles shared by the storefront and the Site Editor.
 */
add_action( 'after_setup_theme', static function () {
	add_editor_style( 'style.css' );
} );

add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'bitforma', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	$assets_dir = get_stylesheet_directory() . '/assets/';
	$assets_url = get_stylesheet_directory_uri() . '/assets/';
	wp_enqueue_style( 'bitforma-chat', $assets_url . 'chat.css', array(), filemtime( $assets_dir . 'chat.css' ) );
	wp_enqueue_script( 'bitforma-chat', $assets_url . 'chat.js', array(), filemtime( $assets_dir . 'chat.js' ), true );

	if ( is_singular( 'product' ) && function_exists( 'wc_get_product' ) ) {
		$script = '/assets/product-details.js';
		wp_enqueue_script( 'bitforma-product-details', get_stylesheet_directory_uri() . $script, array( 'wc-single-product' ), filemtime( get_stylesheet_directory() . $script ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script( 'bitforma-product-details', 'bitformaProductDetails', array(
			'readMore' => __( 'Leer más', 'wp-woo' ),
			'readLess' => __( 'Leer menos', 'wp-woo' ),
		) );
	}
} );

add_action( 'wp_footer', static function () {
	$chat_url = 'https://n8n.tujuegoideal.com/webhook/41bb35c1-f1a7-4b1e-9d7d-3b107b01ee93/chat';
	$greeting = 'Buenos días, si necesita algo no dude en preguntarme.';
	?>
	<div class="bf-chat-widget">
		<div class="bf-chat-voice" hidden>
			<p class="bf-chat-voice__bubble" id="bf-chat-greeting" role="status" lang="es"></p>
			<button class="bf-chat-voice__button" type="button" aria-controls="bf-chat-greeting" data-stop-label="<?php esc_attr_e( 'Detener', 'wp-woo' ); ?>">
				<span class="bf-chat-voice__orb" aria-hidden="true">
					<canvas width="256" height="256"></canvas>
				</span>
				<span class="bf-chat-voice__label"><?php esc_html_e( 'Hablar', 'wp-woo' ); ?></span>
			</button>
			<audio preload="none" data-transcript="<?php echo esc_attr( $greeting ); ?>" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/saludo-es.wav' ); ?>"></audio>
		</div>
		<button class="bf-chat-launcher wp-element-button" type="button" popovertarget="bf-chat">
			<?php esc_html_e( 'Chat', 'wp-woo' ); ?>
		</button>
		<section class="bf-chat-panel" id="bf-chat" popover aria-labelledby="bf-chat-title">
			<header class="bf-chat-panel__header">
				<h2 id="bf-chat-title"><?php esc_html_e( 'Chat de ayuda', 'wp-woo' ); ?></h2>
				<button type="button" popovertarget="bf-chat" popovertargetaction="hide" aria-label="<?php esc_attr_e( 'Cerrar chat', 'wp-woo' ); ?>" autofocus>&times;</button>
			</header>
			<iframe title="<?php esc_attr_e( 'Conversación con el asistente', 'wp-woo' ); ?>" data-src="<?php echo esc_url( $chat_url ); ?>" sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>
		</section>
	</div>
	<?php
} );

add_action( 'wp_footer', static function () {
	if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product || ! $product->is_type( array( 'simple', 'variable' ) ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}

	$assets_dir = get_stylesheet_directory() . '/assets/';
	$assets_url = get_stylesheet_directory_uri() . '/assets/';
	$dependencies = $product->is_type( 'variable' ) ? array( 'wc-add-to-cart-variation' ) : array();
	wp_enqueue_script( 'bitforma-product-bar', $assets_url . 'product-bar.js', $dependencies, filemtime( $assets_dir . 'product-bar.js' ), true );
	wp_enqueue_style( 'bitforma-product-bar', $assets_url . 'product-bar.css', array(), filemtime( $assets_dir . 'product-bar.css' ) );
	?>
	<section class="bf-product-bar" aria-label="<?php esc_attr_e( 'Comprar producto', 'wp-woo' ); ?>" hidden>
		<div class="bf-product-bar__inner bf-wrap">
			<div class="bf-product-bar__product">
				<?php echo $product->get_image( 'thumbnail', array( 'alt' => '', 'width' => 48, 'height' => 48 ) ); ?>
				<span class="bf-product-bar__name"><?php echo esc_html( $product->get_name() ); ?></span>
			</div>
			<div class="bf-product-bar__options" hidden></div>
			<div class="bf-product-bar__purchase">
				<div class="bf-product-bar__price" aria-live="polite" aria-atomic="true"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<button type="button" class="bf-product-bar__button wp-element-button" disabled><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
			</div>
			<p class="bf-product-bar__status" role="status" hidden></p>
		</div>
	</section>
	<?php
} );

/**
 * Return the populated categories shared by the navigation and the home page.
 */
function bitforma_featured_categories(): array {
	$preferred = array( 'pilas', 'conectividad-audifonos', 'mantenimiento-audifonos', 'auriculares-audifonos' );
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'slug'       => $preferred,
		'orderby'    => 'slug__in',
	) );
	if ( ! is_wp_error( $categories ) && count( $categories ) === count( $preferred ) ) {
		return $categories;
	}

	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 4,
		'exclude'    => array( get_option( 'default_product_cat' ) ),
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	return is_wp_error( $categories ) ? array() : $categories;
}

add_filter( 'render_block_core/image', static function ( string $html, array $block ): string {
	if ( 'bf-hero-image' !== ( $block['attrs']['className'] ?? '' ) ) {
		return $html;
	}
	$image = new WP_HTML_Tag_Processor( $html );
	if ( $image->next_tag( 'img' ) && str_ends_with( (string) $image->get_attribute( 'src' ), '/assets/hero-1120.webp' ) ) {
		$assets = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/';
		$image->set_attribute( 'srcset', $assets . 'hero-560.webp 560w, ' . $assets . 'hero-1120.webp 1120w' );
		$image->set_attribute( 'sizes', '(max-width: 680px) 92vw, (max-width: 1416px) 46vw, 660px' );
		$image->set_attribute( 'fetchpriority', 'high' );
	}
	return $image->get_updated_html();
}, 10, 2 );
