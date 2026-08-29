<?php

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

final class HDENQ_Core
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-repository.php';
        require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-frontend.php';
        require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-admin.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        HDENQ_Frontend::get_instance();

        if (is_admin()) {
            HDENQ_Admin::get_instance();
        }
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdenq_wc_missing_notice')) {
            return;
        }
        delete_transient('hdenq_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Product Enquiry requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-product-enquiry'); ?>
            </p>
        </div>
        <?php
    }
}
