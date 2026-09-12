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
 * Pola formularza DC Interface.
 */
class DCCSG_Fields {

	/**
	 * @param string $name  .
	 * @param int    $value .
	 * @param string $label .
	 * @param string $hint  .
	 * @return void
	 */
	public static function toggle( $name, $value, $label, $hint = '' ) {
		?>
		<label class="dc-toggle">
			<input class="dc-toggle__input" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( (int) $value, 1 ); ?> />
			<span class="dc-toggle__track"></span>
			<span><?php echo esc_html( $label ); ?></span>
		</label>
		<?php if ( $hint ) : ?>
			<p class="dc-hint"><?php echo esc_html( $hint ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * @param string $name  .
	 * @param string $value .
	 * @param string $label .
	 * @param string $type  .
	 * @return void
	 */
	public static function input( $name, $value, $label, $type = 'text' ) {
		$id = sanitize_html_class( str_replace( array( '[', ']' ), array( '_', '' ), $name ) );
		?>
		<div class="dc-field">
			<label class="dc-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<input class="dc-input" id="<?php echo esc_attr( $id ); ?>" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" />
		</div>
		<?php
	}

	/**
	 * @param string               $name    .
	 * @param string               $value   .
	 * @param string               $label   .
	 * @param array<string,string> $options .
	 * @param string               $class   .
	 * @return void
	 */
	public static function select( $name, $value, $label, $options, $class = '' ) {
		$id = sanitize_html_class( str_replace( array( '[', ']' ), array( '_', '' ), $name ) );
		?>
		<div class="dc-field">
			<label class="dc-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<select class="dc-select <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
				<?php foreach ( $options as $opt_value => $opt_label ) : ?>
					<option value="<?php echo esc_attr( (string) $opt_value ); ?>" <?php selected( (string) $value, (string) $opt_value ); ?>>
						<?php echo esc_html( $opt_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * @param string               $name    .
	 * @param string               $value   .
	 * @param string               $label   .
	 * @param array<string,string> $options .
	 * @return void
	 */
	public static function switch_group( $name, $value, $label, $options ) {
		?>
		<div class="dc-field">
			<span class="dc-label"><?php echo esc_html( $label ); ?></span>
			<div class="dc-switch-group dc-switch-group--solid" role="radiogroup">
				<?php foreach ( $options as $opt_value => $opt_label ) : ?>
					<label class="dc-switch-btn">
						<input class="dc-switch-btn__input" type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $opt_value ); ?>" <?php checked( (string) $value, (string) $opt_value ); ?> />
						<span class="dc-switch-btn__label"><?php echo esc_html( $opt_label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Szerokość kontenera: liczba + px/%.
	 *
	 * @param string $name  .
	 * @param mixed  $value .
	 * @param string $unit  px|%.
	 * @param string $label .
	 * @return void
	 */
	public static function width( $name, $value, $unit, $label ) {
		$unit = 'px' === $unit ? 'px' : '%';
		$id   = sanitize_html_class( str_replace( array( '[', ']' ), array( '_', '' ), $name ) );
		?>
		<div class="dc-field">
			<label class="dc-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="dccsg-size">
				<input
					class="dc-input"
					id="<?php echo esc_attr( $id ); ?>"
					type="number"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) (int) $value ); ?>"
					min="1"
					max="<?php echo 'px' === $unit ? '4000' : '100'; ?>"
					step="1"
				/>
				<div class="dc-switch-group dc-switch-group--solid dccsg-size__units" role="radiogroup">
					<?php foreach ( array( 'px', '%' ) as $u ) : ?>
						<label class="dc-switch-btn">
							<input class="dc-switch-btn__input" type="radio" name="<?php echo esc_attr( $name . '_unit' ); ?>" value="<?php echo esc_attr( $u ); ?>" <?php checked( $unit, $u ); ?> />
							<span class="dc-switch-btn__label"><?php echo esc_html( $u ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string $name  .
	 * @param string $value .
	 * @param string $label .
	 * @return void
	 */
	public static function color( $name, $value, $label ) {
		?>
		<div class="dc-field">
			<label class="dc-label"><?php echo esc_html( $label ); ?></label>
			<div class="dc-colorpicker" data-dc-colorpicker data-name="<?php echo esc_attr( $name ); ?>" data-value="<?php echo esc_attr( $value ? $value : '#111111' ); ?>" data-label="<?php echo esc_attr( $label ); ?>"></div>
		</div>
		<?php
	}

	/**
	 * @param string               $prefix .
	 * @param array<string,mixed>  $typo   .
	 * @param string               $kind   text|button|link.
	 * @return void
	 */
	public static function typo( $prefix, $typo, $kind = 'text' ) {
		$typo = is_array( $typo ) ? $typo : array();
		$size = isset( $typo['size'] ) ? $typo['size'] : 14;
		$unit = isset( $typo['size_unit'] ) ? $typo['size_unit'] : 'px';
		?>
		<div class="dc-row dc-row--2">
			<?php
			self::select(
				$prefix . '[family]',
				isset( $typo['family'] ) ? $typo['family'] : 'inherit',
				DCCSG_I18n::t( 'font_family' ),
				DCCSG_Settings::fonts()
			);
			?>
			<div class="dc-field">
				<span class="dc-label"><?php echo esc_html( DCCSG_I18n::t( 'font_size' ) ); ?></span>
				<div class="dccsg-size">
					<input class="dc-input" type="number" step="0.1" min="0" name="<?php echo esc_attr( $prefix . '[size]' ); ?>" value="<?php echo esc_attr( (string) $size ); ?>" />
					<div class="dc-switch-group dc-switch-group--solid dccsg-size__units" role="radiogroup">
						<?php foreach ( array( 'px', 'rem', 'vw', 'vh', '%' ) as $u ) : ?>
							<label class="dc-switch-btn">
								<input class="dc-switch-btn__input" type="radio" name="<?php echo esc_attr( $prefix . '[size_unit]' ); ?>" value="<?php echo esc_attr( $u ); ?>" <?php checked( $unit, $u ); ?> />
								<span class="dc-switch-btn__label"><?php echo esc_html( $u ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		self::switch_group(
			$prefix . '[weight]',
			isset( $typo['weight'] ) ? $typo['weight'] : '400',
			DCCSG_I18n::t( 'font_weight' ),
			array(
				'300' => '300',
				'400' => '400',
				'500' => '500',
				'600' => '600',
				'700' => '700',
				'800' => '800',
			)
		);
		self::switch_group(
			$prefix . '[align]',
			isset( $typo['align'] ) ? $typo['align'] : 'left',
			DCCSG_I18n::t( 'align' ),
			array(
				'left'   => DCCSG_I18n::t( 'align_left' ),
				'center' => DCCSG_I18n::t( 'align_center' ),
				'right'  => DCCSG_I18n::t( 'align_right' ),
			)
		);
		self::toggle( $prefix . '[uppercase]', ! empty( $typo['uppercase'] ) ? 1 : 0, DCCSG_I18n::t( 'uppercase' ) );

		echo '<div class="dc-row dc-row--2">';
		self::color( $prefix . '[color]', isset( $typo['color'] ) ? $typo['color'] : '#111111', DCCSG_I18n::t( 'color' ) );
		if ( 'button' === $kind ) {
			self::color( $prefix . '[bg]', isset( $typo['bg'] ) ? $typo['bg'] : '#1fa28c', DCCSG_I18n::t( 'bg' ) );
		} elseif ( 'link' === $kind ) {
			self::color( $prefix . '[hover_color]', isset( $typo['hover_color'] ) ? $typo['hover_color'] : '#1fa28c', DCCSG_I18n::t( 'hover_color' ) );
		}
		echo '</div>';

		if ( 'button' === $kind ) {
			echo '<div class="dc-row dc-row--2">';
			self::color( $prefix . '[hover_color]', isset( $typo['hover_color'] ) ? $typo['hover_color'] : '#ffffff', DCCSG_I18n::t( 'hover_color' ) );
			self::color( $prefix . '[hover_bg]', isset( $typo['hover_bg'] ) ? $typo['hover_bg'] : '#178675', DCCSG_I18n::t( 'hover_bg' ) );
			echo '</div>';
		}
	}

	/**
	 * @param array<string,mixed> $item .
	 * @param int                 $idx  .
	 * @return void
	 */
	public static function product_row( $item, $idx ) {
		$id    = isset( $item['id'] ) ? (int) $item['id'] : 0;
		$line1 = isset( $item['line1'] ) ? (string) $item['line1'] : '';
		$line2 = isset( $item['line2'] ) ? (string) $item['line2'] : '';
		$title = $id ? get_the_title( $id ) : '';
		$thumb = $id ? get_the_post_thumbnail_url( $id, 'thumbnail' ) : '';
		?>
		<li class="dccsg-product" data-product-row>
			<span class="dashicons dashicons-move dccsg-product__handle" aria-hidden="true"></span>
			<?php if ( $thumb ) : ?>
				<img class="dccsg-product__thumb" src="<?php echo esc_url( $thumb ); ?>" alt="" />
			<?php else : ?>
				<span class="dccsg-product__thumb dccsg-product__thumb--empty"></span>
			<?php endif; ?>
			<div class="dccsg-product__body">
				<input type="hidden" name="products[<?php echo esc_attr( (string) $idx ); ?>][id]" value="<?php echo esc_attr( (string) $id ); ?>" />
				<strong class="dccsg-product__title"><?php echo esc_html( $title ? $title : ( '#' . $id ) ); ?></strong>
				<div class="dc-row dc-row--2">
					<div class="dc-field">
						<label class="dc-label"><?php echo esc_html( DCCSG_I18n::t( 'line1' ) ); ?></label>
						<input class="dc-input" type="text" name="products[<?php echo esc_attr( (string) $idx ); ?>][line1]" value="<?php echo esc_attr( $line1 ); ?>" placeholder="2015" />
					</div>
					<div class="dc-field">
						<label class="dc-label"><?php echo esc_html( DCCSG_I18n::t( 'line2' ) ); ?></label>
						<input class="dc-input" type="text" name="products[<?php echo esc_attr( (string) $idx ); ?>][line2]" value="<?php echo esc_attr( $line2 ); ?>" />
					</div>
				</div>
			</div>
			<button type="button" class="button-link-delete dccsg-product__remove" data-remove-product aria-label="<?php echo esc_attr( DCCSG_I18n::t( 'remove' ) ); ?>">
				<span class="dashicons dashicons-trash"></span>
			</button>
		</li>
		<?php
	}
}
