<?php
/**
 * Import the supplied catalog snapshot using WooCommerce product CRUD and WordPress media.
 */
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

final class Bitforma_Catalog_Import {
	private const ROOT = 'oscar-evaluacion/';
	private ZipArchive $archive;
	private array $category_rows = array();
	private array $category_ids = array();
	private array $attribute_ids = array();
	private array $product_ids = array();

	public function __construct( string $archive_path ) {
		$this->archive = new ZipArchive();
		if ( true !== $this->archive->open( $archive_path, ZipArchive::RDONLY ) ) {
			throw new RuntimeException( 'No se pudo abrir el ZIP del catálogo.' );
		}
	}

	public function run(): void {
		// The snapshot's API prices exclude VAT.
		update_option( 'woocommerce_prices_include_tax', 'no' );

		foreach ( array( 'audiopacks', 'pilasaudifono' ) as $source ) {
			$this->category_rows = array_column( $this->read( "04-categorias.$source.json" ), null, 'id' );
			$this->category_ids = array();
			$this->attribute_ids = array();

			foreach ( $this->read( "02-productos.$source.json" ) as $row ) {
				$this->import_product( $source, $row );
			}
			$count = 0;
			foreach ( $this->read( "03-variaciones.$source.json" ) as $row ) {
				$this->import_variation( $source, $row );
				++$count;
			}

			foreach ( $this->product_ids[ $source ] as $id ) {
				$product = wc_get_product( $id );
				if ( $product->is_type( 'variable' ) ) {
					WC_Product_Variable::sync( $product );
					WC_Product_Variable::sync_stock_status( $product );
				}
			}

			WP_CLI::log( sprintf( '%s: %d productos, %d variaciones.', $source, count( $this->product_ids[ $source ] ), $count ) );
		}
		wc_delete_product_transients();
	}

	private function read( string $filename ): array {
		$data = $this->archive->getFromName( self::ROOT . $filename );
		if ( false === $data ) {
			throw new RuntimeException( "Falta $filename en el ZIP." );
		}
		return json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
	}

	private function source_post_id( string $key, string $type ): int {
		$ids = get_posts( array(
			'post_type'      => $type,
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'inherit' ),
			'fields'         => 'ids',
			'numberposts'    => 1,
			'meta_key'       => '_bitforma_source_key',
			'meta_value'     => $key,
			'suppress_filters' => false,
		) );
		return (int) ( $ids[0] ?? 0 );
	}

	private function import_product( string $source, array $row ): void {
		$key = $source . '-' . $row['id'];
		$id = $this->source_post_id( $key, 'product' );
		if ( $id ) {
			$this->product_ids[ $source ][ $row['id'] ] = $id;
			return;
		}

		$product = 'variable' === $row['type'] ? new WC_Product_Variable() : new WC_Product_Simple();
		$product->set_name( $row['name'] );
		$product->set_slug( $row['slug'] );
		$product->set_status( $row['status'] );
		$product->set_catalog_visibility( $row['catalog_visibility'] );
		$description = $this->sanitize_description( $row['description'] );
		$summary = $this->sanitize_description( $row['short_description'] );
		$product->set_description( $description );
		$product->set_short_description( trim( wp_strip_all_tags( $summary ) ) ? $summary : esc_html( wp_trim_words( wp_strip_all_tags( $description ), 30 ) ) );
		$product->set_featured( $row['featured'] || ( 'audiopacks' === $source && in_array( $row['id'], array( 10469, 11373, 11703, 31977 ), true ) ) );
		$product->set_menu_order( (int) $row['menu_order'] );
		$product->set_category_ids( array_map( fn( $category ) => $this->category_id( $category['id'] ), $row['categories'] ) );

		$attributes = array();
		foreach ( $row['attributes'] as $record ) {
			$attributes[] = $this->product_attribute( $record );
		}
		$product->set_attributes( $attributes );
		$product->set_default_attributes( $this->variation_attributes( $row['default_attributes'] ) );

		$images = array();
		foreach ( $row['images'] as $image ) {
			$images[] = $this->media( $source, (int) $row['id'], $image, $row['name'] );
		}
		if ( $images ) {
			$product->set_image_id( array_shift( $images ) );
			$product->set_gallery_image_ids( array_values( array_unique( $images ) ) );
		}
		$this->apply_commerce_fields( $product, $row, $key );
		$id = $product->save();
		$this->product_ids[ $source ][ $row['id'] ] = $id;

		if ( ! empty( $row['brands'] ) && taxonomy_exists( 'product_brand' ) ) {
			$names = array_column( $row['brands'], 'name' );
			$this->check( wp_set_object_terms( $id, array_map( 'sanitize_text_field', $names ), 'product_brand' ) );
		}
		foreach ( $product->get_category_ids() as $category_id ) {
			if ( $product->get_image_id() && ! get_term_meta( $category_id, 'thumbnail_id', true ) ) {
				update_term_meta( $category_id, 'thumbnail_id', $product->get_image_id() );
			}
		}
	}

	private function import_variation( string $source, array $row ): void {
		$key = $source . '-' . $row['id'];
		if ( $this->source_post_id( $key, 'product_variation' ) ) {
			return;
		}
		$parent = $this->product_ids[ $source ][ $row['parent_id'] ] ?? 0;
		if ( ! $parent ) {
			throw new RuntimeException( "Falta el producto padre de $key." );
		}
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent );
		$variation->set_status( $row['status'] );
		$variation->set_menu_order( (int) $row['menu_order'] );
		$variation->set_description( $this->sanitize_description( $row['description'] ) );
		$variation->set_attributes( $this->variation_attributes( $row['attributes'] ) );
		if ( ! empty( $row['image']['src'] ) ) {
			$variation->set_image_id( $this->media( $source, (int) $row['parent_id'], $row['image'], $row['name'] ) );
		}
		$this->apply_commerce_fields( $variation, $row, $key );
		$variation->save();
	}

	private function apply_commerce_fields( WC_Product $product, array $row, string $key ): void {
		$sku = (string) $row['sku'];
		$product->set_sku( $sku && ! wc_get_product_id_by_sku( $sku ) ? $sku : 'bf-' . $key );
		$product->update_meta_data( '_bitforma_source_key', $key );
		$product->update_meta_data( '_bitforma_source_sku', $sku );
		$product->set_regular_price( $row['regular_price'] );
		$product->set_sale_price( $row['sale_price'] );
		$product->set_date_on_sale_from( $row['date_on_sale_from_gmt'] ? strtotime( $row['date_on_sale_from_gmt'] . 'Z' ) : null );
		$product->set_date_on_sale_to( $row['date_on_sale_to_gmt'] ? strtotime( $row['date_on_sale_to_gmt'] . 'Z' ) : null );
		$product->set_manage_stock( true === $row['manage_stock'] );
		$product->set_stock_quantity( $row['stock_quantity'] );
		$product->set_stock_status( $row['stock_status'] );
		$product->set_backorders( $row['backorders'] );
		$product->set_tax_class( $row['tax_class'] );
		if ( isset( $row['tax_status'] ) ) {
			$product->set_tax_status( $row['tax_status'] );
		}
	}

	private function category_id( int $origin_id ): int {
		if ( isset( $this->category_ids[ $origin_id ] ) ) {
			return $this->category_ids[ $origin_id ];
		}
		$row = $this->category_rows[ $origin_id ];
		$parent = $row['parent'] ? $this->category_id( (int) $row['parent'] ) : 0;
		$term = term_exists( $row['slug'], 'product_cat', $parent );
		if ( ! $term ) {
			$term = $this->check( wp_insert_term( $row['name'], 'product_cat', array(
				'parent'      => $parent,
				'slug'        => $row['slug'],
				'description' => $this->sanitize_description( $row['description'] ),
			) ) );
		}
		return $this->category_ids[ $origin_id ] = (int) $term['term_id'];
	}

	private function product_attribute( array $row ): WC_Product_Attribute {
		$attribute = new WC_Product_Attribute();
		if ( ! empty( $row['id'] ) ) {
			$slug = wc_sanitize_taxonomy_name( $row['slug'] );
			$slug = preg_replace( '/^pa_/', '', $slug );
			$taxonomy = wc_attribute_taxonomy_name( $slug );
			$id = wc_attribute_taxonomy_id_by_name( $slug );
			if ( ! $id ) {
				$id = $this->check( wc_create_attribute( array( 'name' => $row['name'], 'slug' => $slug, 'type' => 'select' ) ) );
			}
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$this->check( register_taxonomy( $taxonomy, array( 'product' ), array( 'label' => $row['name'], 'public' => false ) ) );
			}
			$this->attribute_ids[ $row['id'] ] = $taxonomy;
			$attribute->set_id( $id );
			$attribute->set_name( $taxonomy );
			$terms = array();
			foreach ( $row['options'] as $option ) {
				$term = get_term_by( 'name', $option, $taxonomy );
				$terms[] = $term ? $term->term_id : (int) $this->check( wp_insert_term( $option, $taxonomy ) )['term_id'];
			}
			$attribute->set_options( $terms );
		} else {
			$attribute->set_name( $row['name'] );
			$attribute->set_options( $row['options'] );
		}
		$attribute->set_position( (int) $row['position'] );
		$attribute->set_visible( $row['visible'] );
		$attribute->set_variation( $row['variation'] );
		return $attribute;
	}

	private function variation_attributes( array $rows ): array {
		$attributes = array();
		foreach ( $rows as $row ) {
			$taxonomy = $this->attribute_ids[ $row['id'] ] ?? ( $row['slug'] ?? '' );
			if ( $row['id'] && taxonomy_exists( $taxonomy ) ) {
				$term = get_term_by( 'name', $row['option'], $taxonomy );
				$attributes[ $taxonomy ] = $term ? $term->slug : sanitize_title( $row['option'] );
			} else {
				$attributes[ sanitize_title( $row['name'] ) ] = $row['option'];
			}
		}
		return $attributes;
	}

	private function media( string $source, int $product_id, array $image, string $fallback_alt ): int {
		$filename = rawurldecode( basename( wp_parse_url( $image['src'], PHP_URL_PATH ) ) );
		$path = self::ROOT . "recursos/$source/$product_id/$filename";
		$key = 'media-' . hash( 'sha256', $path );
		$id = $this->source_post_id( $key, 'attachment' );
		if ( $id ) {
			return $id;
		}
		$filetype = wp_check_filetype( $filename );
		$bytes = $this->archive->getFromName( $path );
		if ( false === $bytes || ! str_starts_with( (string) $filetype['type'], 'image/' ) ) {
			throw new RuntimeException( "La imagen $path falta o tiene un formato incompatible." );
		}
		$temp = wp_tempnam( $filename );
		try {
			if ( ! $temp || false === file_put_contents( $temp, $bytes ) ) {
				throw new RuntimeException( "No se pudo preparar la imagen $filename." );
			}
			$id = $this->check( media_handle_sideload( array( 'name' => $filename, 'tmp_name' => $temp ), 0 ) );
		} finally {
			if ( $temp && file_exists( $temp ) ) {
				unlink( $temp );
			}
		}
		update_post_meta( $id, '_bitforma_source_key', $key );
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $image['alt'] ?: $fallback_alt ) );
		return $id;
	}

	private function sanitize_description( string $html ): string {
		$allowed = wp_kses_allowed_html( 'post' );
		unset( $allowed['a'], $allowed['img'], $allowed['video'], $allowed['audio'], $allowed['source'] );
		return wp_kses( strip_shortcodes( $html ), $allowed );
	}

	private function check( mixed $value ): mixed {
		if ( is_wp_error( $value ) ) {
			throw new RuntimeException( $value->get_error_message() );
		}
		return $value;
	}
}

try {
	( new Bitforma_Catalog_Import( $args[0] ?? '' ) )->run();
	WP_CLI::success( 'Importación local completada.' );
} catch ( Throwable $error ) {
	WP_CLI::error( $error->getMessage() );
}
