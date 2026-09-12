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
 * AJAX: admin search + frontend koszyk / wariacje.
 */
class DCCSG_Ajax {

	/**
	 * @var DCCSG_Ajax|null
	 */
	private static $instance = null;

	/**
	 * @return DCCSG_Ajax
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_dccsg_search_products', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_dccsg_load_source', array( $this, 'load_source' ) );
		add_action( 'wp_ajax_dccsg_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_dccsg_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_dccsg_get_variation', array( $this, 'get_variation' ) );
		add_action( 'wp_ajax_nopriv_dccsg_get_variation', array( $this, 'get_variation' ) );
	}

	/**
	 * @return void
	 */
	public function search_products() {
		check_ajax_referer( 'dccsg_admin', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		wp_send_json_success( DCCSG_Products::search( $term ) );
	}

	/**
	 * @return void
	 */
	public function load_source() {
		check_ajax_referer( 'dccsg_admin', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$source      = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'selected';
		$category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$limit       = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 24;
		$existing    = isset( $_POST['products'] ) ? json_decode( wp_unslash( $_POST['products'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$rows = DCCSG_Products::listing_for_source( $source, $category_id, $limit, $existing );
		ob_start();
		foreach ( $rows as $idx => $row ) {
			DCCSG_Fields::product_row( $row, (int) $idx );
		}
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html, 'count' => count( $rows ) ) );
	}

	/**
	 * @return void
	 */
	public function add_to_cart() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dccsg_front' ) ) {
			wp_send_json_error( array( 'message' => DCCSG_I18n::t( 'add_error' ) ) );
		}

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$variation    = array();

		if ( ! empty( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			foreach ( wp_unslash( $_POST['variation'] ) as $key => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$variation[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => DCCSG_I18n::t( 'add_error' ) ) );
		}

		if ( $product->is_type( 'variable' ) && ! $variation_id ) {
			wp_send_json_error( array( 'message' => DCCSG_I18n::t( 'select_options' ) ) );
		}

		$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
		if ( ! $added ) {
			wp_send_json_error( array( 'message' => DCCSG_I18n::t( 'add_error' ) ) );
		}

		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		$fragments = array(
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
		);

		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- WooCommerce core filter required to refresh cart widgets.
		$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', $fragments );
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals

		$data = array(
			'message'    => DCCSG_I18n::t( 'added' ),
			'cart_hash'  => WC()->cart->get_cart_hash(),
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'fragments'  => $fragments,
		);

		wp_send_json_success( $data );
	}

	/**
	 * @return void
	 */
	public function get_variation() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dccsg_front' ) ) {
			wp_send_json_error();
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'variable' ) || ! $product instanceof WC_Product_Variable ) {
			wp_send_json_error();
		}

		$attributes = array();
		if ( ! empty( $_POST['attributes'] ) && is_array( $_POST['attributes'] ) ) {
			foreach ( wp_unslash( $_POST['attributes'] ) as $key => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$attributes[ sanitize_title( $key ) ] = wc_clean( $value );
			}
		}

		$data_store   = WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $product, $attributes );
		if ( ! $variation_id ) {
			wp_send_json_success(
				array(
					'variation_id' => 0,
					'price_html'   => $product->get_price_html(),
					'image'        => '',
					'in_stock'     => false,
				)
			);
		}

		$variation = wc_get_product( $variation_id );
		$image_id  = $variation ? $variation->get_image_id() : 0;
		$image     = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

		wp_send_json_success(
			array(
				'variation_id' => $variation_id,
				'price_html'   => $variation ? $variation->get_price_html() : '',
				'image'        => $image,
				'in_stock'     => $variation && $variation->is_in_stock() && $variation->is_purchasable(),
			)
		);
	}
}
