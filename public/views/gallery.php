<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 *
 * @var array<string,mixed>              $settings
 * @var array<int,array<string,mixed>>   $products
 * @var array<int,array<int,array<string,mixed>>> $columns
 * @var int                              $gallery_id
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- View included from plugin class.

$gallery_id = isset( $gallery_id ) ? (int) $gallery_id : 0;
$show_qty   = ! empty( $settings['show_qty'] );
$show_opts  = ! empty( $settings['show_options'] );
$btn_text   = $settings['cart_btn_text'] ? $settings['cart_btn_text'] : DCCSG_Settings::defaults()['cart_btn_text'];
$wrap_class = 'dccsg';
$cw_unit    = ( isset( $settings['container_width_unit'] ) && 'px' === $settings['container_width_unit'] ) ? 'px' : '%';
$cw_value   = isset( $settings['container_width'] ) ? (float) $settings['container_width'] : 100;
if ( $cw_value < 1 ) {
	$cw_value = 1;
}
$css_width  = ( 'px' === $cw_unit ) ? ( (int) $cw_value ) . 'px' : $cw_value . '%';
$grid_style = 'width:' . $css_width . ';max-width:100%;margin-left:auto;margin-right:auto;';
?>
<div class="dccsg-pin dccsg-breakout" data-dccsg-pin>
<div class="<?php echo esc_attr( $wrap_class ); ?>" data-dccsg data-dccsg-id="<?php echo esc_attr( (string) $gallery_id ); ?>" id="dccsg-<?php echo esc_attr( (string) $gallery_id ); ?>" data-scroll-speed="<?php echo esc_attr( (string) ( isset( $settings['scroll_speed'] ) ? max( 10, min( 100, (int) $settings['scroll_speed'] ) ) : 22 ) ); ?>" style="<?php echo esc_attr( '--dccsg-container-width:' . $css_width . ';' ); ?>">
	<div class="dccsg-blob" data-blob aria-hidden="true"></div>

	<div class="dccsg-stage" data-dccsg-stage>
		<div class="dccsg-columns" style="<?php echo esc_attr( $grid_style ); ?>">
			<?php foreach ( $columns as $col_index => $items ) : ?>
				<?php $odd = ( 1 !== $col_index ); ?>
				<div class="dccsg-column-wrap<?php echo $odd ? ' dccsg-column-wrap--odd' : ' dccsg-column-wrap--mid'; ?>">
					<div class="dccsg-column<?php echo $odd ? ' dccsg-column--odd' : ''; ?>">
						<?php foreach ( $items as $item ) : ?>
							<figure class="dccsg-item" data-pos="<?php echo esc_attr( (string) $item['pos'] ); ?>">
								<button type="button" class="dccsg-item__imgwrap" data-open-product data-pos="<?php echo esc_attr( (string) $item['pos'] ); ?>" data-image="<?php echo esc_url( isset( $item['image_full'] ) ? $item['image_full'] : $item['image'] ); ?>" aria-label="<?php echo esc_attr( $item['name'] ); ?>">
									<span class="dccsg-item__img" style="background-image:url(<?php echo esc_url( $item['image'] ); ?>)"></span>
								</button>
								<figcaption class="dccsg-item__caption">
									<span class="dccsg-item__name"><?php echo esc_html( $item['name'] ); ?></span>
									<?php if ( ! empty( $item['line1'] ) ) : ?>
										<span class="dccsg-item__meta"><?php echo esc_html( $item['line1'] ); ?></span>
									<?php endif; ?>
								</figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="dccsg-overlay" data-overlay data-dccsg-id="<?php echo esc_attr( (string) $gallery_id ); ?>" hidden>
		<div class="dccsg-overlay__top">
			<button type="button" class="dccsg-back" data-back aria-label="<?php echo esc_attr( DCCSG_I18n::t( 'back' ) ); ?>">
				<svg viewBox="0 0 50 9" width="100%" aria-hidden="true"><path d="M0 4.5l5-3M0 4.5l5 3M50 4.5h-77"></path></svg>
				<span><?php echo esc_html( DCCSG_I18n::t( 'back' ) ); ?></span>
			</button>
			<button type="button" class="dccsg-close" data-close aria-label="<?php echo esc_attr( DCCSG_I18n::t( 'close' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
			</button>
		</div>

		<div class="dccsg-overlay__body">
			<div class="dccsg-overlay__visual">
				<img class="dccsg-overlay__photo" data-overlay-img alt="" />
			</div>
			<div class="dccsg-overlay__card">
				<?php foreach ( $products as $item ) : ?>
					<div class="dccsg-content__item" data-product-id="<?php echo esc_attr( (string) $item['id'] ); ?>" data-pos="<?php echo esc_attr( (string) ( $item['pos'] ?? '' ) ); ?>" data-product-type="<?php echo esc_attr( $item['type'] ); ?>" hidden>
						<h2 class="dccsg-content__title"><?php echo esc_html( $item['name'] ); ?></h2>

						<?php if ( $item['line1'] || $item['line2'] ) : ?>
							<div class="dccsg-content__meta">
								<?php if ( $item['line1'] ) : ?>
									<span><?php echo esc_html( $item['line1'] ); ?></span>
								<?php endif; ?>
								<?php if ( $item['line2'] ) : ?>
									<span><?php echo esc_html( $item['line2'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="dccsg-content__price" data-price><?php echo wp_kses_post( $item['price_html'] ); ?></div>

						<?php if ( $item['short_desc'] ) : ?>
							<div class="dccsg-content__desc"><?php echo wp_kses_post( $item['short_desc'] ); ?></div>
						<?php endif; ?>

						<form class="dccsg-cart" data-cart-form>
							<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $item['id'] ); ?>" />
							<input type="hidden" name="variation_id" value="0" data-variation-id />

							<?php if ( $show_opts && ! empty( $item['attributes'] ) ) : ?>
								<div class="dccsg-options">
									<?php foreach ( $item['attributes'] as $attribute ) : ?>
										<label class="dccsg-options__field">
											<span class="dccsg-options__label"><?php echo esc_html( $attribute['label'] ); ?></span>
											<select class="dccsg-options__select" name="<?php echo esc_attr( $attribute['name'] ); ?>" data-attribute required>
												<option value=""><?php echo esc_html( DCCSG_I18n::t( 'select_options' ) ); ?></option>
												<?php foreach ( $attribute['options'] as $option ) : ?>
													<option value="<?php echo esc_attr( $option['slug'] ); ?>"><?php echo esc_html( $option['label'] ); ?></option>
												<?php endforeach; ?>
											</select>
										</label>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="dccsg-cart__row">
								<?php if ( $show_qty ) : ?>
									<div class="dccsg-qty">
										<button type="button" class="dccsg-qty__btn" data-qty-minus aria-label="<?php echo esc_attr( DCCSG_I18n::t( 'qty_minus' ) ); ?>">−</button>
										<input class="dccsg-qty__input" type="number" name="quantity" value="<?php echo esc_attr( (string) $item['min_qty'] ); ?>" min="<?php echo esc_attr( (string) $item['min_qty'] ); ?>" <?php echo $item['max_qty'] > 0 ? 'max="' . esc_attr( (string) $item['max_qty'] ) . '"' : ''; ?> />
										<button type="button" class="dccsg-qty__btn" data-qty-plus aria-label="<?php echo esc_attr( DCCSG_I18n::t( 'qty_plus' ) ); ?>">+</button>
									</div>
								<?php else : ?>
									<input type="hidden" name="quantity" value="1" />
								<?php endif; ?>

								<button type="submit" class="dccsg-cart__btn" data-add-to-cart <?php disabled( ( ! $item['purchasable'] && ! $item['is_variable'] ) || ( $item['is_variable'] && $show_opts ) ); ?>>
									<?php echo esc_html( $btn_text ); ?>
								</button>
							</div>
							<p class="dccsg-cart__msg" data-cart-msg hidden></p>
						</form>

						<a class="dccsg-content__link" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( DCCSG_I18n::t( 'view_product' ) ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
</div>
