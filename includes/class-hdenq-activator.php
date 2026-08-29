<?php

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

class HDENQ_Activator
{

    public static function activate()
    {
        if (!self::is_woocommerce_active()) {
            deactivate_plugins(plugin_basename(HDENQ_PLUGIN_FILE));
            set_transient('hdenq_wc_missing_notice', true, 30);
            return;
        }

        self::maybe_upgrade_db();
    }

    public static function is_woocommerce_active()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('woocommerce/woocommerce.php') || class_exists('WooCommerce');
    }

    public static function maybe_upgrade_db()
    {
        if (get_option('hdenq_db_version') === HDENQ_DB_VERSION) {
            return;
        }

        require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-repository.php';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta(HDENQ_Repository::get_schema_sql());

        update_option('hdenq_db_version', HDENQ_DB_VERSION);
    }
}
