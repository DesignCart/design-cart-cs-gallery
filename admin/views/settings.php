<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 *
 * @var array<string,mixed> $settings
 * @var int                 $gallery_id
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- View included from plugin class.

$updated    = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	)
);
$cat_opts   = array( '0' => DCCSG_I18n::t( 'choose_category' ) );
if ( ! is_wp_error( $categories ) ) {
	foreach ( $categories as $term ) {
		$cat_opts[ (string) $term->term_id ] = $term->name;
	}
}
$typo = isset( $settings['typo'] ) ? $settings['typo'] : array();
$icons = array(
	'item_title'      => 'fa-font',
	'item_meta'       => 'fa-list',
	'overlay_title'   => 'fa-header',
	'overlay_desc'    => 'fa-align-left',
	'overlay_meta'    => 'fa-tags',
	'overlay_price'   => 'fa-money',
	'overlay_qty'     => 'fa-plus-square',
	'overlay_options' => 'fa-sliders',
	'overlay_link'    => 'fa-link',
	'cart_btn'        => 'fa-shopping-cart',
);
?>
<div class="dc-page dccsg-page">
	<div class="dc-hero">
		<div class="dc-hero__mesh"></div>
		<div class="dc-hero__orb dc-hero__orb--1"></div>
		<div class="dc-hero__orb dc-hero__orb--2"></div>
		<div class="dc-hero__inner">
			<div class="dc-hero__row">
				<div class="dc-hero__brand">
					<span class="dc-hero__icon"><i class="fa fa-th"></i></span>
					<div>
						<p class="dc-hero__eyebrow"><?php echo esc_html( DCCSG_I18n::t( 'eyebrow' ) ); ?></p>
						<h1 class="dc-hero__title"><?php echo esc_html( $settings['title'] ? $settings['title'] : DCCSG_I18n::t( 'plugin_title' ) ); ?></h1>
					</div>
				</div>
				<div class="dc-hero__actions">
					<button type="submit" form="dccsg-form" class="dc-btn dc-btn--light"><i class="fa fa-save"></i> <?php echo esc_html( DCCSG_I18n::t( 'save' ) ); ?></button>
					<a class="dc-btn dc-btn--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=dccsg-settings' ) ); ?>"><i class="fa fa-arrow-left"></i> <?php echo esc_html( DCCSG_I18n::t( 'back' ) ); ?></a>
				</div>
			</div>
			<ul class="dc-hero__bc">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dccsg-settings' ) ); ?>"><?php echo esc_html( DCCSG_I18n::t( 'galleries' ) ); ?></a></li>
				<li><?php echo esc_html( $settings['title'] ? $settings['title'] : DCCSG_I18n::t( 'tab_general' ) ); ?></li>
			</ul>
		</div>
	</div>

	<div class="dc-page__wrap">
		<div class="dc-form-card">
			<div class="dc-interface" id="dccsgInterface">
				<?php if ( $updated ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php echo esc_html( DCCSG_I18n::t( 'saved' ) ); ?></p></div>
				<?php endif; ?>

				<form method="post" class="dc-form dc-form--full" id="dccsg-form">
					<?php wp_nonce_field( 'dccsg_save' ); ?>
					<input type="hidden" name="dccsg_save" value="1" />
					<input type="hidden" name="gallery_id" value="<?php echo esc_attr( (string) $gallery_id ); ?>" />

					<nav class="dc-nav dc-tabs" role="tablist">
						<button type="button" class="dc-nav__btn dc-tabs__btn dc-active" data-dc-tab="tab-general" role="tab" aria-selected="true"><i class="fa fa-cog"></i> <?php echo esc_html( DCCSG_I18n::t( 'tab_general' ) ); ?></button>
						<button type="button" class="dc-nav__btn dc-tabs__btn" data-dc-tab="tab-products" role="tab"><i class="fa fa-cubes"></i> <?php echo esc_html( DCCSG_I18n::t( 'tab_products' ) ); ?></button>
						<button type="button" class="dc-nav__btn dc-tabs__btn" data-dc-tab="tab-appearance" role="tab"><i class="fa fa-paint-brush"></i> <?php echo esc_html( DCCSG_I18n::t( 'tab_appearance' ) ); ?></button>
						<button type="button" class="dc-nav__btn dc-tabs__btn" data-dc-tab="tab-cart" role="tab"><i class="fa fa-shopping-cart"></i> <?php echo esc_html( DCCSG_I18n::t( 'tab_cart' ) ); ?></button>
					</nav>

					<div class="dc-form-card__body">

						<div id="tab-general" class="dc-tab-panel dc-active" role="tabpanel">
							<div class="dc-section-card dc-section">
								<div class="dc-section-card__head">
									<span class="dc-section-card__icon"><i class="fa fa-cog"></i></span>
									<div>
										<h3 class="dc-section-card__title"><?php echo esc_html( DCCSG_I18n::t( 'general_title' ) ); ?></h3>
										<p class="dc-section-card__sub"><?php echo esc_html( DCCSG_I18n::t( 'general_sub' ) ); ?></p>
									</div>
								</div>

								<?php DCCSG_Fields::input( 'title', $settings['title'], DCCSG_I18n::t( 'gallery_title' ) ); ?>

								<?php
								DCCSG_Fields::switch_group(
									'source',
									$settings['source'],
									DCCSG_I18n::t( 'source' ),
									array(
										'selected' => DCCSG_I18n::t( 'source_selected' ),
										'category' => DCCSG_I18n::t( 'source_category' ),
										'sale'     => DCCSG_I18n::t( 'source_sale' ),
									)
								);
								?>

								<div class="dc-row dc-row--2">
									<div data-source-category>
										<?php DCCSG_Fields::select( 'category_id', (string) $settings['category_id'], DCCSG_I18n::t( 'category' ), $cat_opts ); ?>
									</div>
									<?php DCCSG_Fields::input( 'limit', (int) $settings['limit'], DCCSG_I18n::t( 'limit' ), 'number' ); ?>
								</div>

								<div class="dc-row dc-row--2">
									<?php DCCSG_Fields::color( 'bg_color', $settings['bg_color'], DCCSG_I18n::t( 'bg_color' ) ); ?>
									<?php DCCSG_Fields::color( 'bg_blur_color', isset( $settings['bg_blur_color'] ) ? $settings['bg_blur_color'] : '#efe6d2', DCCSG_I18n::t( 'bg_blur_color' ) ); ?>
								</div>
								<div class="dc-row dc-row--2">
									<?php DCCSG_Fields::color( 'close_btn_bg', isset( $settings['close_btn_bg'] ) ? $settings['close_btn_bg'] : '#1c2f34', DCCSG_I18n::t( 'close_btn_bg' ) ); ?>
									<?php DCCSG_Fields::color( 'close_btn_color', isset( $settings['close_btn_color'] ) ? $settings['close_btn_color'] : '#ffffff', DCCSG_I18n::t( 'close_btn_color' ) ); ?>
								</div>
								<?php
								DCCSG_Fields::width(
									'container_width',
									isset( $settings['container_width'] ) ? $settings['container_width'] : 100,
									isset( $settings['container_width_unit'] ) ? $settings['container_width_unit'] : '%',
									DCCSG_I18n::t( 'container_width' )
								);
								?>
								<p class="dc-hint"><?php echo esc_html( DCCSG_I18n::t( 'container_width_hint' ) ); ?></p>
								<?php DCCSG_Fields::input( 'scroll_speed', isset( $settings['scroll_speed'] ) ? (int) $settings['scroll_speed'] : 22, DCCSG_I18n::t( 'scroll_speed' ), 'number' ); ?>
								<p class="dc-hint"><?php echo esc_html( DCCSG_I18n::t( 'scroll_speed_hint' ) ); ?></p>
								<?php DCCSG_Fields::toggle( 'show_qty', (int) $settings['show_qty'], DCCSG_I18n::t( 'show_qty' ) ); ?>
								<?php DCCSG_Fields::toggle( 'show_options', (int) $settings['show_options'], DCCSG_I18n::t( 'show_options' ), DCCSG_I18n::t( 'show_options_hint' ) ); ?>

								<p class="dc-hint"><?php echo esc_html( DCCSG_I18n::t( 'shortcode_hint' ) ); ?>
									<button type="button" class="dccsg-tile__code dccsg-tile__code--inline" data-copy-shortcode="<?php echo esc_attr( '[design_cart_cs_gallery id="' . (int) $gallery_id . '"]' ); ?>">
										<code>[design_cart_cs_gallery id="<?php echo esc_html( (string) (int) $gallery_id ); ?>"]</code>
									</button>
								</p>
							</div>
						</div>

						<div id="tab-products" class="dc-tab-panel" role="tabpanel">
							<div class="dc-section-card dc-section">
								<div class="dc-section-card__head">
									<span class="dc-section-card__icon"><i class="fa fa-cubes"></i></span>
									<div>
										<h3 class="dc-section-card__title"><?php echo esc_html( DCCSG_I18n::t( 'products_title' ) ); ?></h3>
										<p class="dc-section-card__sub"><?php echo esc_html( DCCSG_I18n::t( 'products_sub' ) ); ?></p>
									</div>
								</div>

								<div class="dccsg-search" data-product-search>
									<input class="dc-input" type="search" autocomplete="off" placeholder="<?php echo esc_attr( DCCSG_I18n::t( 'search_products' ) ); ?>" data-search-input />
									<ul class="dccsg-search__results" data-search-results hidden></ul>
								</div>

								<div class="dccsg-source-bar">
									<button type="button" class="dc-btn dc-btn--secondary" data-load-source>
										<i class="fa fa-refresh"></i> <?php echo esc_html( DCCSG_I18n::t( 'load_source' ) ); ?>
									</button>
								</div>

								<ul class="dccsg-products" data-product-list>
									<?php
									foreach ( (array) $settings['products'] as $idx => $item ) {
										DCCSG_Fields::product_row( $item, (int) $idx );
									}
									?>
								</ul>
								<p class="dc-hint" data-empty-hint <?php echo empty( $settings['products'] ) ? '' : 'hidden'; ?>>
									<?php echo esc_html( DCCSG_I18n::t( 'no_products' ) ); ?>
								</p>
							</div>
						</div>

						<div id="tab-appearance" class="dc-tab-panel" role="tabpanel">
							<div class="dc-section-card dc-section">
								<div class="dc-section-card__head">
									<span class="dc-section-card__icon"><i class="fa fa-paint-brush"></i></span>
									<div>
										<h3 class="dc-section-card__title"><?php echo esc_html( DCCSG_I18n::t( 'appearance_title' ) ); ?></h3>
										<p class="dc-section-card__sub"><?php echo esc_html( DCCSG_I18n::t( 'appearance_sub' ) ); ?></p>
									</div>
								</div>

								<div class="dc-accordion" data-dc-accordion-single>
									<?php
									$first = true;
									foreach ( DCCSG_Settings::typo_keys() as $key => $label_key ) :
										$icon = isset( $icons[ $key ] ) ? $icons[ $key ] : 'fa-font';
										$kind = DCCSG_Settings::typo_kind( $key );
										$row  = isset( $typo[ $key ] ) ? $typo[ $key ] : array();
										?>
										<div class="dc-accordion__item<?php echo $first ? ' dc-open' : ''; ?>">
											<button type="button" class="dc-accordion__trigger" data-dc-accordion-trigger aria-expanded="<?php echo $first ? 'true' : 'false'; ?>">
												<span class="dc-accordion__icon"><i class="fa <?php echo esc_attr( $icon ); ?>"></i></span>
												<span class="dc-accordion__title">
													<?php echo esc_html( DCCSG_I18n::t( $label_key ) ); ?>
												</span>
												<svg class="dc-accordion__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
											</button>
											<div class="dc-accordion__panel">
												<div class="dc-accordion__content">
													<div class="dc-accordion__body">
														<?php DCCSG_Fields::typo( 'typo[' . $key . ']', $row, $kind ); ?>
													</div>
												</div>
											</div>
										</div>
										<?php
										$first = false;
									endforeach;
									?>
								</div>
							</div>
						</div>

						<div id="tab-cart" class="dc-tab-panel" role="tabpanel">
							<div class="dc-section-card dc-section">
								<div class="dc-section-card__head">
									<span class="dc-section-card__icon"><i class="fa fa-shopping-cart"></i></span>
									<div>
										<h3 class="dc-section-card__title"><?php echo esc_html( DCCSG_I18n::t( 'cart_title' ) ); ?></h3>
										<p class="dc-section-card__sub"><?php echo esc_html( DCCSG_I18n::t( 'cart_sub' ) ); ?></p>
									</div>
								</div>
								<?php DCCSG_Fields::input( 'cart_btn_text', $settings['cart_btn_text'], DCCSG_I18n::t( 'cart_btn_text' ) ); ?>
								<p class="dc-hint"><?php echo esc_html( DCCSG_I18n::t( 'typo_cart_btn' ) ); ?> — <?php echo esc_html( DCCSG_I18n::t( 'tab_appearance' ) ); ?></p>
							</div>
						</div>

						<div class="dc-actions">
							<button type="submit" class="dc-btn dc-btn--primary"><i class="fa fa-save"></i> <?php echo esc_html( DCCSG_I18n::t( 'save' ) ); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
