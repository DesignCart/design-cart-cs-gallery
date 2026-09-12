<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 */

defined( 'ABSPATH' ) || exit;

/**
 * Zapytania o produkty WooCommerce.
 */
class DCCSG_Products {

	/**
	 * @param array<string,mixed>|null $settings .
	 * @return array<int,array<string,mixed>>
	 */
	public static function resolve( $settings = null ) {
		$settings = is_array( $settings ) ? $settings : DCCSG_Settings::get();
		$meta     = self::meta_map( isset( $settings['products'] ) ? $settings['products'] : array() );
		$limit    = max( 1, (int) $settings['limit'] );
		$source   = isset( $settings['source'] ) ? $settings['source'] : 'selected';

		$listed = array();
		foreach ( (array) $settings['products'] as $row ) {
			if ( ! empty( $row['id'] ) ) {
				$listed[] = (int) $row['id'];
			}
		}
		$listed = array_values( array_unique( array_filter( $listed ) ) );

		if ( 'category' === $source ) {
			$ids = self::query_ids(
				array(
					'limit'    => $limit,
					'category' => array( (int) $settings['category_id'] ),
				)
			);
		} elseif ( 'sale' === $source ) {
			$sale_ids = wc_get_product_ids_on_sale();
			$ids      = $sale_ids ? self::query_ids(
				array(
					'limit'   => $limit,
					'include' => $sale_ids,
				)
			) : array();
		} else {
			$ids = array_slice( $listed, 0, $limit );
		}

		if ( ! $ids && $listed ) {
			$ids = array_slice( $listed, 0, $limit );
		}

		$out = array();
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$extra = isset( $meta[ $id ] ) ? $meta[ $id ] : array( 'line1' => '', 'line2' => '' );
			$out[] = self::normalize( $product, $extra, ! empty( $settings['show_options'] ) );
		}
		return $out;
	}

	/**
	 * @param array<int,array<string,mixed>> $rows .
	 * @return array<int,array<string,string>>
	 */
	public static function meta_map( $rows ) {
		$map = array();
		foreach ( (array) $rows as $row ) {
			if ( empty( $row['id'] ) ) {
				continue;
			}
			$map[ (int) $row['id'] ] = array(
				'line1' => isset( $row['line1'] ) ? (string) $row['line1'] : '',
				'line2' => isset( $row['line2'] ) ? (string) $row['line2'] : '',
			);
		}
		return $map;
	}

	/**
	 * @param array<string,mixed> $args .
	 * @return array<int,int>
	 */
	public static function query_ids( $args ) {
		$defaults = array(
			'status'  => 'publish',
			'limit'   => 24,
			'return'  => 'ids',
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		);
		if ( isset( $args['category'] ) ) {
			$cat_ids = array_map( 'intval', (array) $args['category'] );
			if ( empty( $cat_ids ) || array( 0 ) === $cat_ids ) {
				return array();
			}
			$slugs = array();
			foreach ( $cat_ids as $cat_id ) {
				$term = get_term( $cat_id, 'product_cat' );
				if ( $term && ! is_wp_error( $term ) ) {
					$slugs[] = $term->slug;
				}
			}
			if ( empty( $slugs ) ) {
				return array();
			}
			$args['category'] = $slugs;
		}
		$ids = wc_get_products( wp_parse_args( $args, $defaults ) );
		return is_array( $ids ) ? array_map( 'intval', $ids ) : array();
	}

	/**
	 * @param WC_Product           $product     .
	 * @param array<string,string> $extra       .
	 * @param bool                 $with_options .
	 * @return array<string,mixed>
	 */
	public static function normalize( $product, $extra, $with_options = true ) {
		$image_id = $product->get_image_id();
		$thumb    = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
		$full     = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
		if ( ! $thumb ) {
			$thumb = wc_placeholder_img_src( 'large' );
		}
		if ( ! $full ) {
			$full = $thumb;
		}

		$item = array(
			'id'             => $product->get_id(),
			'name'           => $product->get_name(),
			'permalink'      => $product->get_permalink(),
			'short_desc'     => wp_kses_post( $product->get_short_description() ),
			'price_html'     => $product->get_price_html(),
			'image'          => $thumb,
			'image_full'     => $full,
			'type'           => $product->get_type(),
			'purchasable'    => $product->is_purchasable() && $product->is_in_stock(),
			'in_stock'       => $product->is_in_stock(),
			'min_qty'        => max( 1, (int) $product->get_min_purchase_quantity() ),
			'max_qty'        => (int) $product->get_max_purchase_quantity(),
			'line1'          => $extra['line1'],
			'line2'          => $extra['line2'],
			'is_variable'    => $product->is_type( 'variable' ),
			'attributes'     => array(),
		);

		if ( $with_options && $product->is_type( 'variable' ) && $product instanceof WC_Product_Variable ) {
			$item['attributes'] = self::variation_attributes( $product );
		}

		return $item;
	}

	/**
	 * Atrybuty używane do wariacji — bez pełnej tablicy wariantów.
	 *
	 * @param WC_Product_Variable $product .
	 * @return array<int,array<string,mixed>>
	 */
	public static function variation_attributes( $product ) {
		$out = array();
		foreach ( $product->get_variation_attributes() as $attribute_name => $options ) {
			$taxonomy = $attribute_name;
			$label    = wc_attribute_label( $attribute_name, $product );
			$values   = array();
			foreach ( (array) $options as $option ) {
				$values[] = array(
					'slug'  => $option,
					'label' => self::attribute_option_label( $taxonomy, $option ),
				);
			}
			$out[] = array(
				'name'    => 'attribute_' . sanitize_title( $attribute_name ),
				'label'   => $label,
				'options' => $values,
			);
		}
		return $out;
	}

	/**
	 * @param string $taxonomy .
	 * @param string $option   .
	 * @return string
	 */
	private static function attribute_option_label( $taxonomy, $option ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			$term = get_term_by( 'slug', $option, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term->name;
			}
		}
		return (string) $option;
	}

	/**
	 * @param string $term .
	 * @return array<int,array<string,mixed>>
	 */
	public static function search( $term ) {
		$ids = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => 20,
				's'      => $term,
				'return' => 'ids',
			)
		);
		$rows = array();
		foreach ( (array) $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$thumb = get_the_post_thumbnail_url( $id, 'thumbnail' );
			$rows[] = array(
				'id'    => $id,
				'name'  => $product->get_name(),
				'thumb' => $thumb ? $thumb : '',
			);
		}
		return $rows;
	}

	/**
	 * @param string $source      .
	 * @param int    $category_id .
	 * @param int    $limit       .
	 * @param array<int,array<string,mixed>> $existing .
	 * @return array<int,array<string,mixed>>
	 */
	public static function listing_for_source( $source, $category_id, $limit, $existing ) {
		$meta  = self::meta_map( $existing );
		$limit = max( 1, (int) $limit );
		$ids   = array();

		if ( 'category' === $source ) {
			$ids = self::query_ids(
				array(
					'limit'    => $limit,
					'category' => array( (int) $category_id ),
				)
			);
		} elseif ( 'sale' === $source ) {
			$sale_ids = wc_get_product_ids_on_sale();
			$ids      = $sale_ids ? self::query_ids(
				array(
					'limit'   => $limit,
					'include' => $sale_ids,
				)
			) : array();
		} else {
			foreach ( (array) $existing as $row ) {
				if ( ! empty( $row['id'] ) ) {
					$ids[] = (int) $row['id'];
				}
			}
		}

		$out = array();
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$thumb = get_the_post_thumbnail_url( $id, 'thumbnail' );
			$out[] = array(
				'id'    => $id,
				'name'  => $product->get_name(),
				'thumb' => $thumb ? $thumb : '',
				'line1' => isset( $meta[ $id ]['line1'] ) ? $meta[ $id ]['line1'] : '',
				'line2' => isset( $meta[ $id ]['line2'] ) ? $meta[ $id ]['line2'] : '',
			);
		}
		return $out;
	}
}
