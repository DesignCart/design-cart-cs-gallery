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
 * Shortcode i zasoby frontu.
 */
class DCCSG_Frontend {

	/**
	 * @var DCCSG_Frontend|null
	 */
	private static $instance = null;

	/**
	 * @var bool
	 */
	private $needs_assets = false;

	/**
	 * @var bool
	 */
	private $enqueued = false;

	/**
	 * @var array<int,bool>
	 */
	private $gallery_ids = array();

	/**
	 * @return DCCSG_Frontend
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'design_cart_cs_gallery', array( $this, 'shortcode' ) );
		add_shortcode( 'dccsg_gallery', array( $this, 'shortcode' ) );
		add_filter( 'the_posts', array( $this, 'detect_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
		add_action( 'wp_footer', array( $this, 'maybe_print_assets' ), 5 );
	}

	/**
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'dccsg-front',
			DCCSG_URL . 'public/css/frontend.css',
			array(),
			file_exists( DCCSG_PATH . 'public/css/frontend.css' ) ? (string) filemtime( DCCSG_PATH . 'public/css/frontend.css' ) : DCCSG_VERSION
		);
		wp_register_script(
			'dccsg-front',
			DCCSG_URL . 'public/js/frontend.js',
			array(),
			file_exists( DCCSG_PATH . 'public/js/frontend.js' ) ? (string) filemtime( DCCSG_PATH . 'public/js/frontend.js' ) : DCCSG_VERSION,
			true
		);
	}

	/**
	 * @param array<string,mixed> $atts .
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'design_cart_cs_gallery'
		);

		$gallery_id = absint( $atts['id'] );
		if ( ! $gallery_id || ! DCCSG_Settings::exists( $gallery_id ) ) {
			$gallery_id = DCCSG_Settings::first_id();
		}
		if ( ! $gallery_id ) {
			return '';
		}

		$settings = DCCSG_Settings::get( $gallery_id );
		$products = DCCSG_Products::resolve( $settings );
		if ( empty( $products ) ) {
			return '';
		}

		$this->needs_assets             = true;
		$this->gallery_ids[ $gallery_id ] = true;

		$columns = array( array(), array(), array() );
		$map     = array( 1, 0, 2 );
		foreach ( $products as $i => $product ) {
			$products[ $i ]['pos']       = $i + 1;
			$product['pos']              = $i + 1;
			$columns[ $map[ $i % 3 ] ][] = $product;
		}

		ob_start();
		include DCCSG_PATH . 'public/views/gallery.php';
		return (string) ob_get_clean();
	}

	/**
	 * @param array<int,WP_Post> $posts .
	 * @return array<int,WP_Post>
	 */
	public function detect_shortcode( $posts ) {
		if ( $this->needs_assets || empty( $posts ) || is_admin() ) {
			return $posts;
		}
		foreach ( $posts as $post ) {
			if ( ! isset( $post->post_content ) ) {
				continue;
			}
			if ( has_shortcode( $post->post_content, 'design_cart_cs_gallery' ) || has_shortcode( $post->post_content, 'dccsg_gallery' ) ) {
				$this->needs_assets = true;
				$this->collect_ids_from_content( $post->post_content );
			}
		}
		return $posts;
	}

	/**
	 * @param string $content .
	 * @return void
	 */
	private function collect_ids_from_content( $content ) {
		$regex = get_shortcode_regex( array( 'design_cart_cs_gallery', 'dccsg_gallery' ) );
		if ( ! preg_match_all( '/' . $regex . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			return;
		}
		foreach ( $matches as $match ) {
			$atts = shortcode_parse_atts( $match[3] );
			$id   = ( is_array( $atts ) && ! empty( $atts['id'] ) ) ? absint( $atts['id'] ) : DCCSG_Settings::first_id();
			if ( $id ) {
				$this->gallery_ids[ $id ] = true;
			}
		}
	}

	/**
	 * @return void
	 */
	public function maybe_enqueue() {
		if ( $this->needs_assets ) {
			$this->enqueue();
		}
	}

	/**
	 * @return void
	 */
	public function maybe_print_assets() {
		if ( ! $this->needs_assets ) {
			return;
		}
		$this->enqueue();
	}

	/**
	 * @return void
	 */
	private function enqueue() {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;

		$ids = array_keys( $this->gallery_ids );
		if ( ! $ids ) {
			$first = DCCSG_Settings::first_id();
			if ( $first ) {
				$ids = array( $first );
			}
		}

		$css = '';
		foreach ( $ids as $id ) {
			$css .= $this->css_vars( DCCSG_Settings::get( $id ), (int) $id );
		}
		wp_enqueue_style( 'dccsg-front' );
		if ( $css ) {
			wp_add_inline_style( 'dccsg-front', $css );
		}
		wp_enqueue_script( 'dccsg-front' );
		wp_localize_script(
			'dccsg-front',
			'dccsgFront',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'dccsg_front' ),
				'i18n'  => array(
					'added'         => DCCSG_I18n::t( 'added' ),
					'error'         => DCCSG_I18n::t( 'add_error' ),
					'selectOptions' => DCCSG_I18n::t( 'select_options' ),
					'outOfStock'    => DCCSG_I18n::t( 'out_of_stock' ),
				),
			)
		);
	}

	/**
	 * @param array<string,mixed> $settings   .
	 * @param int                 $gallery_id .
	 * @return string
	 */
	private function css_vars( $settings, $gallery_id ) {
		$id    = absint( $gallery_id );
		$sel   = '.dccsg[data-dccsg-id="' . $id . '"],.dccsg-overlay[data-dccsg-id="' . $id . '"]';
		$lines = array( $sel . '{' );
		$width = isset( $settings['container_width'] ) ? (float) $settings['container_width'] : 100;
		$unit  = ( isset( $settings['container_width_unit'] ) && 'px' === $settings['container_width_unit'] ) ? 'px' : '%';
		if ( $width < 1 ) {
			$width = 1;
		}
		$css_width = ( 'px' === $unit ) ? $width . 'px' : $width . '%';

		$lines[] = '--dccsg-bg:' . $this->css_color( isset( $settings['bg_color'] ) ? $settings['bg_color'] : '#b7b19f' ) . ';';
		$lines[] = '--dccsg-bg-blur:' . $this->css_color( isset( $settings['bg_blur_color'] ) ? $settings['bg_blur_color'] : '#efe6d2' ) . ';';
		$lines[] = '--dccsg-close-bg:' . $this->css_color( isset( $settings['close_btn_bg'] ) ? $settings['close_btn_bg'] : '#1c2f34' ) . ';';
		$lines[] = '--dccsg-close-color:' . $this->css_color( isset( $settings['close_btn_color'] ) ? $settings['close_btn_color'] : '#ffffff' ) . ';';
		$lines[] = '--dccsg-container-width:' . $css_width . ';';

		$allowed_typo = DCCSG_Settings::typo_keys();
		foreach ( (array) $settings['typo'] as $key => $typo ) {
			if ( ! isset( $allowed_typo[ $key ] ) || ! is_array( $typo ) ) {
				continue;
			}
			$p = '--dccsg-' . str_replace( '_', '-', $key );
			$family = $this->css_family( isset( $typo['family'] ) ? $typo['family'] : 'inherit' );
			$lines[] = $p . '-family:' . $family . ';';
			$lines[] = $p . '-size:' . (float) $typo['size'] . ( isset( $typo['size_unit'] ) ? $typo['size_unit'] : 'px' ) . ';';
			$lines[] = $p . '-weight:' . ( isset( $typo['weight'] ) ? $typo['weight'] : '400' ) . ';';
			$lines[] = $p . '-transform:' . ( ! empty( $typo['uppercase'] ) ? 'uppercase' : 'none' ) . ';';
			$lines[] = $p . '-color:' . $this->css_color( $typo['color'] ) . ';';
			$lines[] = $p . '-align:' . ( isset( $typo['align'] ) ? $typo['align'] : 'left' ) . ';';
			if ( isset( $typo['bg'] ) ) {
				$lines[] = $p . '-bg:' . $this->css_color( $typo['bg'] ) . ';';
			}
			if ( isset( $typo['hover_color'] ) ) {
				$lines[] = $p . '-hover-color:' . $this->css_color( $typo['hover_color'] ) . ';';
			}
			if ( isset( $typo['hover_bg'] ) ) {
				$lines[] = $p . '-hover-bg:' . $this->css_color( $typo['hover_bg'] ) . ';';
			}
		}

		$lines[] = '}';
		return implode( '', $lines );
	}

	/**
	 * @param string $family .
	 * @return string
	 */
	private function css_family( $family ) {
		if ( 'inherit' === $family || false !== strpos( $family, ',' ) ) {
			return $family;
		}
		return '"' . $family . '", serif';
	}

	/**
	 * @param string $color .
	 * @return string
	 */
	private function css_color( $color ) {
		$hex = sanitize_hex_color( $color );
		return $hex ? $hex : '#111111';
	}
}
