<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Plugin Name:       Design Cart Column Scroll Gallery
 * Plugin URI:        https://www.designcart.pl/
 * Description:       Galeria produktów WooCommerce w układzie Column Scroll (naprzemienne kolumny, podgląd, koszyk).
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Paweł Nosko
 * Author URI:        https://www.designcart.pl/pawel-nosko.html
 * License:           GPL-2.0-or-later
 * Text Domain:       design-cart-cs-gallery
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   9.9
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 */

defined( 'ABSPATH' ) || exit;

define( 'DCCSG_VERSION', '1.0.0' );
define( 'DCCSG_FILE', __FILE__ );
define( 'DCCSG_PATH', plugin_dir_path( __FILE__ ) );
define( 'DCCSG_URL', plugin_dir_url( __FILE__ ) );
define( 'DCCSG_BASENAME', plugin_basename( __FILE__ ) );

require_once DCCSG_PATH . 'includes/class-dccsg-i18n.php';
require_once DCCSG_PATH . 'includes/class-dccsg-settings.php';
require_once DCCSG_PATH . 'includes/class-dccsg-fields.php';
require_once DCCSG_PATH . 'includes/class-dccsg-products.php';
require_once DCCSG_PATH . 'includes/class-dccsg-ajax.php';
require_once DCCSG_PATH . 'includes/class-dccsg-admin.php';
require_once DCCSG_PATH . 'includes/class-dccsg-frontend.php';
require_once DCCSG_PATH . 'includes/class-dccsg-plugin.php';

register_activation_hook( __FILE__, array( 'DCCSG_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'DCCSG_Plugin', 'deactivate' ) );

DCCSG_Plugin::instance();
