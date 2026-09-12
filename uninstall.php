<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'dccsg_settings' );
delete_option( 'dccsg_galleries' );
delete_option( 'dccsg_gallery_next_id' );
