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
 * Domyślne i zapisane ustawienia.
 */
class DCCSG_Settings {

	const OPTION   = 'dccsg_settings';
	const GALLERIES = 'dccsg_galleries';
	const NEXT_ID   = 'dccsg_gallery_next_id';

	/**
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		$typo_text = array(
			'family'    => 'inherit',
			'size'      => 13,
			'size_unit' => 'px',
			'weight'    => '400',
			'uppercase' => 1,
			'color'     => '#111111',
			'align'     => 'left',
		);

		return array(
			'title'                => 'Gallery 1',
			'source'               => 'selected',
			'category_id'          => 0,
			'limit'                => 24,
			'bg_color'             => '#648189',
			'bg_blur_color'        => '#7b959c',
			'close_btn_bg'         => '#ffffff',
			'close_btn_color'      => '#648189',
			'container_width'      => 1600,
			'container_width_unit' => 'px',
			'scroll_speed'         => 22,
			'show_options'         => 1,
			'show_qty'             => 1,
			'cart_btn_text'        => 'Add to cart',
			'products'             => array(),
			'typo'                 => array(
				'item_title'      => array_merge( $typo_text, array( 'color' => '#ffffff' ) ),
				'item_meta'       => array_merge( $typo_text, array( 'color' => '#ffffff', 'align' => 'right' ) ),
				'overlay_title'   => array_merge(
					$typo_text,
					array(
						'family'    => 'Playfair Display',
						'size'      => 9,
						'size_unit' => 'vh',
						'weight'    => '300',
						'uppercase' => 0,
					)
				),
				'overlay_desc'    => $typo_text,
				'overlay_meta'    => $typo_text,
				'overlay_price'   => array_merge( $typo_text, array( 'size' => 18, 'weight' => '600', 'uppercase' => 0 ) ),
				'overlay_qty'     => array_merge( $typo_text, array( 'size' => 14, 'uppercase' => 0, 'align' => 'center' ) ),
				'overlay_options' => array_merge( $typo_text, array( 'size' => 12 ) ),
				'overlay_link'    => array_merge(
					$typo_text,
					array(
						'size'        => 14,
						'color'       => '#000000',
						'hover_color' => '#1fa28c',
					)
				),
				'cart_btn'        => array_merge(
					$typo_text,
					array(
						'size'        => 14,
						'weight'      => '600',
						'align'       => 'center',
						'color'       => '#ffffff',
						'bg'          => '#648189',
						'hover_color' => '#ffffff',
						'hover_bg'    => '#648189',
					)
				),
			),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function typo_keys() {
		return array(
			'item_title'      => 'typo_item_title',
			'item_meta'       => 'typo_item_meta',
			'overlay_title'   => 'typo_overlay_title',
			'overlay_desc'    => 'typo_overlay_desc',
			'overlay_meta'    => 'typo_overlay_meta',
			'overlay_price'   => 'typo_overlay_price',
			'overlay_qty'     => 'typo_overlay_qty',
			'overlay_options' => 'typo_overlay_options',
			'overlay_link'    => 'typo_overlay_link',
			'cart_btn'        => 'typo_cart_btn',
		);
	}

	/**
	 * @param string $key .
	 * @return string
	 */
	public static function typo_kind( $key ) {
		if ( 'cart_btn' === $key ) {
			return 'button';
		}
		if ( 'overlay_link' === $key ) {
			return 'link';
		}
		return 'text';
	}

	/**
	 * @return array<string,string>
	 */
	public static function fonts() {
		return array(
			'inherit'                      => DCCSG_I18n::t( 'font_theme' ),
			'system-ui, sans-serif'        => 'System UI',
			'Georgia, serif'               => 'Georgia',
			'"Times New Roman", serif'     => 'Times New Roman',
			'Playfair Display'             => 'Playfair Display',
			'Cormorant Garamond'           => 'Cormorant Garamond',
			'Cinzel'                       => 'Cinzel',
			'Bodoni Moda'                  => 'Bodoni Moda',
			'Libre Baskerville'            => 'Libre Baskerville',
			'DM Serif Display'             => 'DM Serif Display',
			'Abril Fatface'                => 'Abril Fatface',
			'Oswald'                       => 'Oswald',
			'Montserrat'                   => 'Montserrat',
			'Inter'                        => 'Inter',
			'Poppins'                      => 'Poppins',
			'Roboto'                       => 'Roboto',
			'Lora'                         => 'Lora',
			'Raleway'                      => 'Raleway',
			'Nunito'                       => 'Nunito',
			'Bebas Neue'                   => 'Bebas Neue',
			'Archivo'                      => 'Archivo',
			'Great Vibes'                  => 'Great Vibes',
		);
	}

	/**
	 * @return array<int,string>
	 */
	public static function google_fonts() {
		return array(
			'Playfair Display',
			'Cormorant Garamond',
			'Cinzel',
			'Bodoni Moda',
			'Libre Baskerville',
			'DM Serif Display',
			'Abril Fatface',
			'Oswald',
			'Montserrat',
			'Inter',
			'Poppins',
			'Roboto',
			'Lora',
			'Raleway',
			'Nunito',
			'Bebas Neue',
			'Archivo',
			'Great Vibes',
		);
	}

	/**
	 * Stare pojedyncze ustawienia → pierwsza instancja.
	 *
	 * @return void
	 */
	public static function migrate() {
		$galleries = get_option( self::GALLERIES, false );
		if ( false !== $galleries && is_array( $galleries ) ) {
			return;
		}

		$first = self::defaults();
		$old   = get_option( self::OPTION, false );
		if ( is_array( $old ) && ! empty( $old ) ) {
			$first = self::merge( $first, $old );
		}
		if ( empty( $first['title'] ) ) {
			$first['title'] = self::defaults()['title'];
		}

		update_option( self::GALLERIES, array( 1 => $first ), false );
		update_option( self::NEXT_ID, 2, false );
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function all() {
		self::migrate();
		$stored = get_option( self::GALLERIES, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		$out = array();
		foreach ( $stored as $id => $row ) {
			$out[ (int) $id ] = self::merge( self::defaults(), is_array( $row ) ? $row : array() );
		}
		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	/**
	 * @return int
	 */
	public static function first_id() {
		$all = self::all();
		if ( ! $all ) {
			return 0;
		}
		$ids = array_keys( $all );
		return (int) $ids[0];
	}

	/**
	 * @param int $id .
	 * @return bool
	 */
	public static function exists( $id ) {
		$all = self::all();
		return isset( $all[ (int) $id ] );
	}

	/**
	 * @param int $id 0 = pierwsza instancja.
	 * @return array<string,mixed>
	 */
	public static function get( $id = 0 ) {
		$all = self::all();
		$id  = (int) $id;
		if ( $id && isset( $all[ $id ] ) ) {
			return $all[ $id ];
		}
		if ( $all ) {
			return reset( $all );
		}
		return self::defaults();
	}

	/**
	 * @param int                 $id   .
	 * @param array<string,mixed> $data .
	 * @return void
	 */
	public static function save( $id, $data ) {
		$id         = (int) $id;
		$all        = self::all();
		$all[ $id ] = $data;
		update_option( self::GALLERIES, $all, false );
	}

	/**
	 * @param string $title .
	 * @return int
	 */
	public static function create( $title = '' ) {
		$next = (int) get_option( self::NEXT_ID, 1 );
		if ( $next < 1 ) {
			$next = 1;
		}
		while ( self::exists( $next ) ) {
			$next++;
		}

		$row          = self::defaults();
		$row['title'] = $title ? $title : sprintf( 'Gallery %d', $next );
		self::save( $next, $row );
		update_option( self::NEXT_ID, $next + 1, false );
		return $next;
	}

	/**
	 * @param int $id .
	 * @return int
	 */
	public static function duplicate( $id ) {
		$src = self::get( $id );
		if ( ! self::exists( $id ) ) {
			return 0;
		}
		$src['title'] = $src['title'] . ' ' . DCCSG_I18n::t( 'copy_suffix' );
		return self::create_from( $src );
	}

	/**
	 * @param array<string,mixed> $data .
	 * @return int
	 */
	public static function create_from( $data ) {
		$next = (int) get_option( self::NEXT_ID, 1 );
		if ( $next < 1 ) {
			$next = 1;
		}
		while ( self::exists( $next ) ) {
			$next++;
		}
		self::save( $next, self::merge( self::defaults(), $data ) );
		update_option( self::NEXT_ID, $next + 1, false );
		return $next;
	}

	/**
	 * @param int $id .
	 * @return void
	 */
	public static function delete( $id ) {
		$id  = (int) $id;
		$all = self::all();
		unset( $all[ $id ] );
		update_option( self::GALLERIES, $all, false );
	}

	/**
	 * @param array<string,mixed> $base .
	 * @param array<string,mixed> $over .
	 * @return array<string,mixed>
	 */
	public static function merge( $base, $over ) {
		foreach ( $over as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) && self::is_assoc( $base[ $key ] ) ) {
				$base[ $key ] = self::merge( $base[ $key ], $value );
			} else {
				$base[ $key ] = $value;
			}
		}
		return $base;
	}

	/**
	 * @param array<mixed> $arr .
	 * @return bool
	 */
	private static function is_assoc( $arr ) {
		if ( array() === $arr ) {
			return true;
		}
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}
}
