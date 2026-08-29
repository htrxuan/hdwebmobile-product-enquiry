<?php

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Storage only -- every value here is saved exactly as sanitized on the way in
 * (sanitize_text_field()/sanitize_email()/sanitize_textarea_field()), but nothing
 * in this class is responsible for escaping on the way OUT. That is deliberate: the
 * competing plugin's CVE-2026-59512 vulnerability was an output-escaping failure,
 * not an input-sanitization one, so the fix lives entirely at render time in
 * HDENQ_Admin and HDENQ_Frontend -- every echo of a customer-submitted field in
 * this plugin passes through esc_html()/esc_attr() at the point it is printed,
 * never relying on "it was already sanitized when stored" as a substitute.
 */
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
class HDENQ_Repository
{

    const STATUS_NEW     = 'new';
    const STATUS_REPLIED = 'replied';

    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'hdenq_enquiries';
    }

    public static function get_schema_sql()
    {
        global $wpdb;
        $table           = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            reply TEXT DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            replied_at DATETIME DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY status (status)
        ) {$charset_collate};";
    }

    public static function create($product_id, $user_id, $name, $email, $message)
    {
        global $wpdb;
        $wpdb->insert(
            self::get_table_name(),
            array(
                'product_id' => $product_id,
                'user_id'    => $user_id ?: null,
                'name'       => $name,
                'email'      => $email,
                'message'    => $message,
                'status'     => self::STATUS_NEW,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        return (int) $wpdb->insert_id;
    }

    public static function get($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM %i WHERE id = %d', self::get_table_name(), $id));
    }

    public static function get_all($limit = 100)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            'SELECT * FROM %i ORDER BY id DESC LIMIT %d',
            self::get_table_name(),
            $limit
        ));
    }

    public static function save_reply($id, $reply)
    {
        global $wpdb;
        return false !== $wpdb->update(
            self::get_table_name(),
            array(
                'reply'      => $reply,
                'status'     => self::STATUS_REPLIED,
                'replied_at' => current_time('mysql'),
            ),
            array('id' => $id),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
}
