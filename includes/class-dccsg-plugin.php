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
 * Bootstrap pluginu.
 */
class DCCSG_Plugin {

	/**
	 * @var DCCSG_Plugin|null
	 */
	private static $instance = null;

	/**
	 * @return DCCSG_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos' ) );
	}

	/**
	 * @return void
	 */
	public function declare_hpos() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', DCCSG_FILE, true );
		}
	}

	/**
	 * @return void
	 */
	public function boot() {
		if ( ! $this->woocommerce_ready() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
			return;
		}

		DCCSG_Settings::migrate();
		DCCSG_Ajax::instance();
		DCCSG_Frontend::instance();

		if ( is_admin() ) {
			DCCSG_Admin::instance();
		}
	}

	/**
	 * @return bool
	 */
	private function woocommerce_ready() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * @return void
	 */
	public function woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>';
		echo esc_html( DCCSG_I18n::t( 'wc_required' ) );
		echo '</p></div>';
	}

	/**
	 * @return void
	 */
	public static function activate() {
		DCCSG_Settings::migrate();
	}

	/**
	 * @return void
	 */
	public static function deactivate() {
	}
}
