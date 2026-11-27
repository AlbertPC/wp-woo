<?php
/**
 * Title: Cuadrícula y filtros del catálogo
 * Slug: wp-woo/catalog
 */
$filters = array();
if ( function_exists( 'wc_attribute_taxonomy_id_by_name' ) ) {
	foreach ( array( 'brand' => 'Marca', 'color' => 'Color' ) as $slug => $label ) {
		$id = wc_attribute_taxonomy_id_by_name( $slug );
		if ( $id ) {
			$filters[ $id ] = $label;
		}
	}
}
?>
<!-- wp:group {"className":"bf-catalog-grid","layout":{"type":"default"}} -->
<div class="wp-block-group bf-catalog-grid">
<!-- wp:woocommerce/product-filters {"overlayMode":"mobile"} -->
<!-- wp:woocommerce/product-filter-active -->
<!-- wp:woocommerce/product-filter-removable-chips -->
<div class="wp-block-woocommerce-product-filter-removable-chips wc-block-product-filter-removable-chips"></div>
<!-- /wp:woocommerce/product-filter-removable-chips -->
<!-- wp:woocommerce/product-filter-clear-button /-->
<!-- /wp:woocommerce/product-filter-active -->
<!-- wp:woocommerce/product-filter-price -->
<!-- wp:heading {"level":2,"fontSize":"medium"} -->
<h2 class="wp-block-heading has-medium-font-size">Precio</h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-filter-price-slider /-->
<!-- /wp:woocommerce/product-filter-price -->
<?php foreach ( $filters as $filter_attribute => $label ) : ?>
<!-- wp:woocommerce/product-filter-attribute {"attributeId":<?php echo (int) $filter_attribute; ?>,"showCounts":true} -->
<!-- wp:heading {"level":2,"fontSize":"medium"} -->
<h2 class="wp-block-heading has-medium-font-size"><?php echo esc_html( $label ); ?></h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-filter-checkbox-list /-->
<!-- /wp:woocommerce/product-filter-attribute -->
<?php endforeach; ?>
<!-- wp:woocommerce/product-filter-status {"showCounts":true} -->
<!-- wp:heading {"level":2,"fontSize":"medium"} -->
<h2 class="wp-block-heading has-medium-font-size">Disponibilidad</h2>
<!-- /wp:heading -->
<!-- wp:woocommerce/product-filter-checkbox-list /-->
<!-- /wp:woocommerce/product-filter-status -->
<!-- /wp:woocommerce/product-filters -->
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group">
<!-- wp:woocommerce/product-results-count /-->
<!-- wp:woocommerce/catalog-sorting /-->
</div>
<!-- /wp:group -->
<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"isProductCollectionBlock":true,"postType":"product","perPage":12,"order":"desc","orderBy":"date","inherit":true},"tagName":"div","displayLayout":{"type":"flex","columns":3,"shrinkColumns":true},"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"level":2,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"fontSize":"small"} /-->
<!-- /wp:woocommerce/product-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
<!-- wp:woocommerce/product-collection-no-results -->
<!-- wp:paragraph -->
<p>No se encontraron productos.</p>
<!-- /wp:paragraph -->
<!-- /wp:woocommerce/product-collection-no-results -->
</div>
<!-- /wp:woocommerce/product-collection -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
