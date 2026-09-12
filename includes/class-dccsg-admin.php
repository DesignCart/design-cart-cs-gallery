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
 * Panel admina DC Interface.
 */
class DCCSG_Admin {

	/**
	 * @var DCCSG_Admin|null
	 */
	private static $instance = null;

	/**
	 * @return DCCSG_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
	}

	/**
	 * @param string $classes .
	 * @return string
	 */
	public function body_class( $classes ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'dccsg-settings' === $page ) {
			$classes .= ' dccsg-admin';
		}
		return $classes;
	}

	/**
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			DCCSG_I18n::t( 'plugin_title' ),
			DCCSG_I18n::t( 'plugin_menu' ),
			'manage_woocommerce',
			'dccsg-settings',
			array( $this, 'render' ),
			'dashicons-images-alt2',
			58
		);
	}

	/**
	 * @return void
	 */
	public function handle_actions() {
		if ( ! is_admin() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! empty( $_POST['dccsg_save'] ) ) {
			check_admin_referer( 'dccsg_save' );
			$id = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;
			if ( ! $id || ! DCCSG_Settings::exists( $id ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=dccsg-settings' ) );
				exit;
			}
			DCCSG_Settings::save( $id, $this->sanitize( wp_unslash( $_POST ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_safe_redirect( admin_url( 'admin.php?page=dccsg-settings&gallery=' . $id . '&updated=1' ) );
			exit;
		}

		if ( ! empty( $_POST['dccsg_create'] ) ) {
			check_admin_referer( 'dccsg_instances' );
			$id = DCCSG_Settings::create();
			wp_safe_redirect( admin_url( 'admin.php?page=dccsg-settings&gallery=' . $id ) );
			exit;
		}

		if ( ! empty( $_POST['dccsg_duplicate'] ) ) {
			check_admin_referer( 'dccsg_instances' );
			$src = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;
			$id  = $src ? DCCSG_Settings::duplicate( $src ) : 0;
			$to  = $id ? admin_url( 'admin.php?page=dccsg-settings&gallery=' . $id ) : admin_url( 'admin.php?page=dccsg-settings' );
			wp_safe_redirect( $to );
			exit;
		}

		if ( ! empty( $_POST['dccsg_delete'] ) ) {
			check_admin_referer( 'dccsg_instances' );
			$id = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;
			if ( $id ) {
				DCCSG_Settings::delete( $id );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=dccsg-settings&deleted=1' ) );
			exit;
		}
	}

	/**
	 * @param array<string,mixed> $post .
	 * @return array<string,mixed>
	 */
	private function sanitize( $post ) {
		$defaults = DCCSG_Settings::defaults();
		$source   = isset( $post['source'] ) ? sanitize_key( $post['source'] ) : 'selected';
		if ( ! in_array( $source, array( 'selected', 'category', 'sale' ), true ) ) {
			$source = 'selected';
		}

		$out = array(
			'title'         => isset( $post['title'] ) ? sanitize_text_field( $post['title'] ) : $defaults['title'],
			'source'        => $source,
			'category_id'   => isset( $post['category_id'] ) ? absint( $post['category_id'] ) : 0,
			'limit'         => isset( $post['limit'] ) ? max( 1, absint( $post['limit'] ) ) : 24,
			'bg_color'        => $this->hex( isset( $post['bg_color'] ) ? $post['bg_color'] : $defaults['bg_color'] ),
			'bg_blur_color'   => $this->hex( isset( $post['bg_blur_color'] ) ? $post['bg_blur_color'] : $defaults['bg_blur_color'] ),
			'close_btn_bg'           => $this->hex( isset( $post['close_btn_bg'] ) ? $post['close_btn_bg'] : $defaults['close_btn_bg'] ),
			'close_btn_color'        => $this->hex( isset( $post['close_btn_color'] ) ? $post['close_btn_color'] : $defaults['close_btn_color'] ),
			'container_width'        => $this->sanitize_container_width( isset( $post['container_width'] ) ? $post['container_width'] : $defaults['container_width'], isset( $post['container_width_unit'] ) ? $post['container_width_unit'] : $defaults['container_width_unit'] ),
			'container_width_unit'   => $this->sanitize_container_unit( isset( $post['container_width_unit'] ) ? $post['container_width_unit'] : $defaults['container_width_unit'] ),
			'scroll_speed'           => $this->sanitize_scroll_speed( isset( $post['scroll_speed'] ) ? $post['scroll_speed'] : $defaults['scroll_speed'] ),
			'show_options'  => empty( $post['show_options'] ) ? 0 : 1,
			'show_qty'      => empty( $post['show_qty'] ) ? 0 : 1,
			'cart_btn_text' => isset( $post['cart_btn_text'] ) ? sanitize_text_field( $post['cart_btn_text'] ) : $defaults['cart_btn_text'],
			'products'      => $this->sanitize_products( isset( $post['products'] ) ? $post['products'] : array() ),
			'typo'          => array(),
		);

		$posted_typo = isset( $post['typo'] ) && is_array( $post['typo'] ) ? $post['typo'] : array();
		foreach ( DCCSG_Settings::typo_keys() as $key => $_label ) {
			$base = $defaults['typo'][ $key ];
			$row  = isset( $posted_typo[ $key ] ) && is_array( $posted_typo[ $key ] ) ? $posted_typo[ $key ] : array();
			$kind = DCCSG_Settings::typo_kind( $key );
			$out['typo'][ $key ] = $this->sanitize_typo( $row, $base, $kind );
		}

		return $out;
	}

	/**
	 * @param mixed $rows .
	 * @return array<int,array<string,mixed>>
	 */
	private function sanitize_products( $rows ) {
		if ( ! is_array( $rows ) ) {
			return array();
		}
		$out = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['id'] ) ) {
				continue;
			}
			$out[] = array(
				'id'    => absint( $row['id'] ),
				'line1' => isset( $row['line1'] ) ? sanitize_text_field( $row['line1'] ) : '',
				'line2' => isset( $row['line2'] ) ? sanitize_text_field( $row['line2'] ) : '',
			);
		}
		return $out;
	}

	/**
	 * @param array<string,mixed> $row  .
	 * @param array<string,mixed> $base .
	 * @param string              $kind .
	 * @return array<string,mixed>
	 */
	private function sanitize_typo( $row, $base, $kind ) {
		$units  = array( 'px', 'rem', 'vw', 'vh', '%' );
		$aligns = array( 'left', 'center', 'right' );
		$fonts  = array_keys( DCCSG_Settings::fonts() );

		$family = isset( $row['family'] ) ? $row['family'] : $base['family'];
		if ( ! in_array( $family, $fonts, true ) ) {
			$family = $base['family'];
		}

		$unit = isset( $row['size_unit'] ) ? $row['size_unit'] : $base['size_unit'];
		if ( ! in_array( $unit, $units, true ) ) {
			$unit = 'px';
		}

		$align = isset( $row['align'] ) ? $row['align'] : $base['align'];
		if ( ! in_array( $align, $aligns, true ) ) {
			$align = 'left';
		}

		$weight = isset( $row['weight'] ) ? preg_replace( '/[^0-9]/', '', (string) $row['weight'] ) : $base['weight'];
		if ( ! in_array( $weight, array( '300', '400', '500', '600', '700', '800' ), true ) ) {
			$weight = '400';
		}

		$out = array(
			'family'    => $family,
			'size'      => isset( $row['size'] ) ? (float) $row['size'] : (float) $base['size'],
			'size_unit' => $unit,
			'weight'    => $weight,
			'uppercase' => empty( $row['uppercase'] ) ? 0 : 1,
			'color'     => $this->hex( isset( $row['color'] ) ? $row['color'] : $base['color'] ),
			'align'     => $align,
		);

		if ( 'button' === $kind ) {
			$out['bg']          = $this->hex( isset( $row['bg'] ) ? $row['bg'] : ( $base['bg'] ?? '#1fa28c' ) );
			$out['hover_color'] = $this->hex( isset( $row['hover_color'] ) ? $row['hover_color'] : ( $base['hover_color'] ?? '#ffffff' ) );
			$out['hover_bg']    = $this->hex( isset( $row['hover_bg'] ) ? $row['hover_bg'] : ( $base['hover_bg'] ?? '#178675' ) );
		}
		if ( 'link' === $kind ) {
			$out['hover_color'] = $this->hex( isset( $row['hover_color'] ) ? $row['hover_color'] : ( $base['hover_color'] ?? '#1fa28c' ) );
		}

		return $out;
	}

	/**
	 * @param string $value .
	 * @return string
	 */
	private function hex( $value ) {
		$color = sanitize_hex_color( $value );
		return $color ? $color : '#111111';
	}

	/**
	 * @param mixed $unit .
	 * @return string
	 */
	private function sanitize_container_unit( $unit ) {
		return 'px' === $unit ? 'px' : '%';
	}

	/**
	 * @param mixed $value .
	 * @return int
	 */
	private function sanitize_scroll_speed( $value ) {
		$value = (int) round( (float) $value );
		if ( $value < 10 ) {
			return 10;
		}
		if ( $value > 100 ) {
			return 100;
		}
		return $value;
	}

	/**
	 * @param mixed  $value .
	 * @param mixed  $unit  .
	 * @return int
	 */
	private function sanitize_container_width( $value, $unit ) {
		$unit  = $this->sanitize_container_unit( $unit );
		$value = (int) round( (float) $value );
		$min   = 1;
		$max   = 'px' === $unit ? 4000 : 100;
		if ( $value < $min ) {
			return $min;
		}
		if ( $value > $max ) {
			return $max;
		}
		return $value;
	}

	/**
	 * @param string $hook .
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( false === strpos( $hook, 'dccsg-settings' ) ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style(
			'dccsg-font-awesome',
			DCCSG_URL . 'admin/vendor/font-awesome/css/font-awesome.min.css',
			array(),
			'4.7.0'
		);

		$iface = DCCSG_PATH . 'admin/css/dc-interface/dc-interface.css';
		wp_enqueue_style(
			'dccsg-interface',
			DCCSG_URL . 'admin/css/dc-interface/dc-interface.css',
			array( 'dccsg-font-awesome' ),
			file_exists( $iface ) ? (string) filemtime( $iface ) : DCCSG_VERSION
		);

		wp_enqueue_style(
			'dccsg-admin',
			DCCSG_URL . 'admin/css/admin.css',
			array( 'dccsg-interface' ),
			file_exists( DCCSG_PATH . 'admin/css/admin.css' ) ? (string) filemtime( DCCSG_PATH . 'admin/css/admin.css' ) : DCCSG_VERSION
		);

		wp_enqueue_script(
			'dccsg-colorpicker',
			DCCSG_URL . 'admin/js/dc-interface/dc-colorpicker.js',
			array(),
			file_exists( DCCSG_PATH . 'admin/js/dc-interface/dc-colorpicker.js' ) ? (string) filemtime( DCCSG_PATH . 'admin/js/dc-interface/dc-colorpicker.js' ) : DCCSG_VERSION,
			true
		);
		wp_enqueue_script(
			'dccsg-dimension',
			DCCSG_URL . 'admin/js/dc-interface/dc-dimension.js',
			array(),
			file_exists( DCCSG_PATH . 'admin/js/dc-interface/dc-dimension.js' ) ? (string) filemtime( DCCSG_PATH . 'admin/js/dc-interface/dc-dimension.js' ) : DCCSG_VERSION,
			true
		);
		wp_enqueue_script(
			'dccsg-interface',
			DCCSG_URL . 'admin/js/dc-interface/dc-interface.js',
			array( 'dccsg-colorpicker', 'dccsg-dimension' ),
			file_exists( DCCSG_PATH . 'admin/js/dc-interface/dc-interface.js' ) ? (string) filemtime( DCCSG_PATH . 'admin/js/dc-interface/dc-interface.js' ) : DCCSG_VERSION,
			true
		);
		wp_enqueue_script(
			'dccsg-admin',
			DCCSG_URL . 'admin/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'dccsg-interface' ),
			file_exists( DCCSG_PATH . 'admin/js/admin.js' ) ? (string) filemtime( DCCSG_PATH . 'admin/js/admin.js' ) : DCCSG_VERSION,
			true
		);

		wp_localize_script(
			'dccsg-admin',
			'dccsgAdmin',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'dccsg_admin' ),
				'i18n'  => array(
					'search'     => DCCSG_I18n::t( 'search_products' ),
					'add'        => DCCSG_I18n::t( 'add_product' ),
					'remove'     => DCCSG_I18n::t( 'remove' ),
					'line1'      => DCCSG_I18n::t( 'line1' ),
					'line2'      => DCCSG_I18n::t( 'line2' ),
					'noProducts' => DCCSG_I18n::t( 'no_products' ),
					'copied'     => DCCSG_I18n::t( 'copied' ),
					'confirmDel' => DCCSG_I18n::t( 'confirm_delete' ),
				),
			)
		);
	}

	/**
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$gallery_id = isset( $_GET['gallery'] ) ? absint( wp_unslash( $_GET['gallery'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $gallery_id && DCCSG_Settings::exists( $gallery_id ) ) {
			$settings = DCCSG_Settings::get( $gallery_id );
			include DCCSG_PATH . 'admin/views/settings.php';
			return;
		}

		$galleries = DCCSG_Settings::all();
		include DCCSG_PATH . 'admin/views/list.php';
	}
}
