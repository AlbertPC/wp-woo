<?php
/**
 * Title: Portada Bitforma
 * Slug: wp-woo/home
 */
$assets = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/';
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
$categories = bitforma_featured_categories();
?>
<!-- wp:group {"className":"bf-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bf-wrap">
<!-- wp:group {"className":"bf-hero","layout":{"type":"default"}} -->
<div class="wp-block-group bf-hero">
<!-- wp:group {"className":"bf-hero-copy","layout":{"type":"default"}} -->
<div class="wp-block-group bf-hero-copy">
<!-- wp:paragraph {"className":"bf-kicker"} -->
<p class="bf-kicker">Accesorios para audífonos</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Todo para tus audífonos.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Encuentra pilas, recambios, cargadores y accesorios de conectividad para tus dispositivos.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>">Explorar catálogo →</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"bf-hero-image"} -->
<figure class="wp-block-image size-full bf-hero-image"><img src="<?php echo esc_url( $assets . 'hero-1120.webp' ); ?>" alt="Ilustración de audífonos y estuche de carga sobre un fondo lavanda"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
<?php if ( $categories ) : ?>
<!-- wp:group {"tagName":"section","className":"bf-section","layout":{"type":"default"}} -->
<section class="wp-block-group bf-section">
<!-- wp:heading -->
<h2 class="wp-block-heading">Compra por categoría</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"bf-category-grid","layout":{"type":"default"}} -->
<div class="wp-block-group bf-category-grid">
<?php foreach ( $categories as $category ) :
	$thumbnail = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );
	$photo = $thumbnail ? wp_get_attachment_image_url( $thumbnail, 'medium' ) : '';
	$link = get_term_link( $category );
	if ( is_wp_error( $link ) ) {
		continue;
	}
?>
<!-- wp:group {"className":"bf-category","layout":{"type":"default"}} -->
<div class="wp-block-group bf-category">
<?php if ( $photo ) : ?>
<!-- wp:image {"id":<?php echo $thumbnail; ?>,"sizeSlug":"medium","linkDestination":"none"} -->
<figure class="wp-block-image size-medium"><img src="<?php echo esc_url( $photo ); ?>" alt="" class="wp-image-<?php echo $thumbnail; ?>"/></figure>
<!-- /wp:image -->
<?php endif; ?>
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $category->name ); ?></a></h3>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
<?php endif; ?>
<!-- wp:group {"tagName":"section","className":"bf-section","layout":{"type":"default"}} -->
<section class="wp-block-group bf-section">
<!-- wp:paragraph {"className":"bf-kicker"} -->
<p class="bf-kicker">Selección Bitforma</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Productos destacados</h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"isProductCollectionBlock":true,"postType":"product","perPage":4,"order":"desc","orderBy":"date","inherit":false,"featured":true},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"fontSize":"small"} /-->
<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
</section>
<!-- /wp:group -->
<!-- wp:group {"tagName":"section","className":"bf-section","layout":{"type":"default"}} -->
<section class="wp-block-group bf-section">
<!-- wp:paragraph {"className":"bf-kicker"} -->
<p class="bf-kicker">Precios especiales</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">En oferta</h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-collection {"queryId":2,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"isProductCollectionBlock":true,"postType":"product","perPage":4,"order":"desc","orderBy":"date","inherit":false,"woocommerceOnSale":true},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"fontSize":"small"} /-->
<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
</section>
<!-- /wp:group -->
<!-- wp:group {"tagName":"section","className":"bf-benefits","layout":{"type":"default"}} -->
<section class="wp-block-group bf-benefits">
<!-- wp:heading -->
<h2 class="wp-block-heading">Prueba una compra de principio a fin</h2>
<!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Envío simulado</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Elige la tarifa de demostración al introducir una dirección española.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Pago sin cargos</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>El pago contra reembolso registra el pedido sin solicitar datos bancarios.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Pedidos de prueba</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Consulta el pedido con los datos ficticios que introduzcas en la compra.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</section>
<!-- /wp:group -->
<!-- wp:group {"tagName":"section","className":"bf-section","layout":{"type":"default"}} -->
<section class="wp-block-group bf-section">
<!-- wp:paragraph {"className":"bf-kicker"} -->
<p class="bf-kicker">Lo último</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Novedades</h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-collection {"queryId":3,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"isProductCollectionBlock":true,"postType":"product","perPage":4,"order":"desc","orderBy":"date","inherit":false},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"fontSize":"small"} /-->
<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
</section>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
