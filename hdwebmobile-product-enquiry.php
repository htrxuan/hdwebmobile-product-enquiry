<?php

/**
 * Plugin Name: HDWebmobile Product Enquiry
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-product-enquiry/
 * Description: Let customers ask a question about a product before buying. Every field is escaped at the exact point of output, everywhere it could ever render, closing the unauthenticated stored-XSS class found in a competing product-enquiry plugin.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-product-enquiry
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

define('HDENQ_VERSION', '1.0.0');
define('HDENQ_DB_VERSION', '1.0.0');
define('HDENQ_PLUGIN_FILE', __FILE__);
define('HDENQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDENQ_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-activator.php';

register_activation_hook(__FILE__, array(HDENQ_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDENQ_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-core.php';
    HDENQ_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-product-enquiry') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
