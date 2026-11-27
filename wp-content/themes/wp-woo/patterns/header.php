<?php
/**
 * Title: Cabecera Bitforma
 * Slug: wp-woo/header
 */
?>
<!-- wp:group {"className":"bf-top","layout":{"type":"default"}} -->
<div class="wp-block-group bf-top">
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">TIENDA DEMO <span aria-hidden="true">·</span> Compra de prueba sin cargos reales</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"bf-header bf-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bf-header bf-wrap">
<!-- wp:site-title {"level":0,"className":"bf-logo"} /-->
<!-- wp:navigation {"overlayMenu":"mobile","ariaLabel":"Navegación principal","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"Tienda","url":"<?php echo esc_url( home_url( '/tienda/' ) ); ?>","kind":"custom"} /-->
<?php foreach ( bitforma_featured_categories() as $category ) :
	$category_link = get_term_link( $category );
	if ( is_wp_error( $category_link ) ) {
		continue;
	}
	$attributes = array( 'label' => $category->name, 'url' => $category_link, 'kind' => 'taxonomy', 'type' => 'product_cat', 'id' => $category->term_id );
	echo get_comment_delimited_block_content( 'core/navigation-link', $attributes, '' );
endforeach; ?>
<!-- /wp:navigation -->
<!-- wp:group {"className":"bf-tools","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group bf-tools">
<!-- wp:search {"label":"Buscar productos","showLabel":false,"placeholder":"Buscar productos","buttonText":"Buscar","buttonPosition":"button-only","buttonUseIcon":true,"query":{"post_type":"product"}} /-->
<!-- wp:woocommerce/mini-cart /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
