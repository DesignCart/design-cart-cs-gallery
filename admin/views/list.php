<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 *
 * @var array<int,array<string,mixed>> $galleries
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- View included from plugin class.

$deleted = isset( $_GET['deleted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
						<h1 class="dc-hero__title"><?php echo esc_html( DCCSG_I18n::t( 'plugin_title' ) ); ?></h1>
					</div>
				</div>
				<div class="dc-hero__actions">
					<form method="post">
						<?php wp_nonce_field( 'dccsg_instances' ); ?>
						<button type="submit" name="dccsg_create" value="1" class="dc-btn dc-btn--light">
							<i class="fa fa-plus"></i> <?php echo esc_html( DCCSG_I18n::t( 'add_gallery' ) ); ?>
						</button>
					</form>
				</div>
			</div>
			<ul class="dc-hero__bc">
				<li><?php echo esc_html( DCCSG_I18n::t( 'plugin_menu' ) ); ?></li>
				<li><?php echo esc_html( DCCSG_I18n::t( 'galleries' ) ); ?></li>
			</ul>
		</div>
	</div>

	<div class="dc-page__wrap">
		<div class="dc-form-card">
			<div class="dc-interface">
				<div class="dc-form-card__body">
					<?php if ( $deleted ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php echo esc_html( DCCSG_I18n::t( 'deleted' ) ); ?></p></div>
					<?php endif; ?>

					<p class="dccsg-dashboard__lead"><?php echo esc_html( DCCSG_I18n::t( 'galleries_sub' ) ); ?></p>

					<?php if ( empty( $galleries ) ) : ?>
						<p class="dc-hint"><?php echo esc_html( DCCSG_I18n::t( 'no_galleries' ) ); ?></p>
					<?php else : ?>
						<div class="dccsg-tiles">
							<?php foreach ( $galleries as $id => $gallery ) : ?>
								<?php
								$count     = isset( $gallery['products'] ) && is_array( $gallery['products'] ) ? count( $gallery['products'] ) : 0;
								$shortcode = '[design_cart_cs_gallery id="' . (int) $id . '"]';
								?>
								<div class="dccsg-tile">
									<a class="dccsg-tile__main" href="<?php echo esc_url( admin_url( 'admin.php?page=dccsg-settings&gallery=' . (int) $id ) ); ?>">
										<span class="dccsg-tile__icon"><i class="fa fa-th"></i></span>
										<span class="dccsg-tile__title"><?php echo esc_html( $gallery['title'] ? $gallery['title'] : sprintf( DCCSG_I18n::t( 'gallery_n' ), $id ) ); ?></span>
										<span class="dccsg-tile__sub"><?php echo esc_html( sprintf( DCCSG_I18n::t( 'products_count' ), $count ) ); ?></span>
									</a>
									<button type="button" class="dccsg-tile__code" data-copy-shortcode="<?php echo esc_attr( $shortcode ); ?>" title="<?php echo esc_attr( DCCSG_I18n::t( 'copied' ) ); ?>">
										<code><?php echo esc_html( $shortcode ); ?></code>
									</button>
									<div class="dccsg-tile__actions">
										<a class="dc-btn dc-btn--secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=dccsg-settings&gallery=' . (int) $id ) ); ?>">
											<i class="fa fa-pencil"></i> <?php echo esc_html( DCCSG_I18n::t( 'edit_gallery' ) ); ?>
										</a>
										<form method="post">
											<?php wp_nonce_field( 'dccsg_instances' ); ?>
											<input type="hidden" name="gallery_id" value="<?php echo esc_attr( (string) $id ); ?>" />
											<button type="submit" name="dccsg_duplicate" value="1" class="dc-btn dc-btn--ghost">
												<i class="fa fa-copy"></i> <?php echo esc_html( DCCSG_I18n::t( 'duplicate' ) ); ?>
											</button>
										</form>
										<form method="post" data-confirm-delete>
											<?php wp_nonce_field( 'dccsg_instances' ); ?>
											<input type="hidden" name="gallery_id" value="<?php echo esc_attr( (string) $id ); ?>" />
											<button type="submit" name="dccsg_delete" value="1" class="dc-btn dc-btn--ghost dccsg-tile__delete">
												<i class="fa fa-trash"></i> <?php echo esc_html( DCCSG_I18n::t( 'remove' ) ); ?>
											</button>
										</form>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
