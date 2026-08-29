<?php

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * This is the exact rendering surface CVE-2026-59512 broke: a customer-submitted
 * question, rendered as HTML in wp-admin for a staff member to read. Every single
 * customer-controlled value below -- name, email, message, reply -- passes through
 * esc_html() at the point it is printed, with no exceptions and no "this one's
 * probably fine" shortcuts, because a stored-XSS payload in a product enquiry runs
 * in an administrator's browser session, not a customer's.
 */
final class HDENQ_Admin
{

    const NONCE_ACTION = 'hdenq_reply';

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
        require_once HDENQ_PLUGIN_DIR . 'includes/class-hdenq-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_post_hdenq_reply', array($this, 'handle_reply'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['product-enquiry'] = array(
            'label'  => __('Product Enquiries', 'hdwebmobile-product-enquiry'),
            'order'  => 45,
            'render' => array($this, 'render_settings_page'),
        );
        return $tabs;
    }

    public function handle_reply()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-product-enquiry'));
        }

        check_admin_referer(self::NONCE_ACTION);

        $id    = isset($_POST['enquiry_id']) ? absint($_POST['enquiry_id']) : 0;
        $reply = isset($_POST['reply']) ? sanitize_textarea_field(wp_unslash($_POST['reply'])) : '';

        $enquiry = $id ? HDENQ_Repository::get($id) : null;

        if ($enquiry && '' !== $reply) {
            HDENQ_Repository::save_reply($id, $reply);
            $this->email_reply_to_customer($enquiry, $reply);
        }

        wp_safe_redirect(admin_url('admin.php?page=hdwebmobile&tab=product-enquiry&updated=1'));
        exit;
    }

    /**
     * Plain-text email -- the reply the admin just typed is customer-controlled
     * content re-typed by staff, not raw enquiry input, but it's still sent as plain
     * text rather than HTML so there is no email-client HTML-rendering surface at
     * all for this message either.
     */
    private function email_reply_to_customer($enquiry, $reply)
    {
        $product = wc_get_product($enquiry->product_id);
        $subject = sprintf(
            /* translators: %s: product name */
            __('Re: your question about %s', 'hdwebmobile-product-enquiry'),
            $product ? $product->get_name() : __('a product', 'hdwebmobile-product-enquiry')
        );

        $body = sprintf(
            "Hi %s,\n\nThanks for your question:\n\"%s\"\n\nOur reply:\n%s",
            $enquiry->name,
            $enquiry->message,
            $reply
        );

        wp_mail($enquiry->email, $subject, $body);
    }

    public function render_settings_page()
    {
        if (isset($_GET['updated'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag, the actual reply already passed nonce verification in handle_reply().
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Reply sent.', 'hdwebmobile-product-enquiry') . '</p></div>';
        }

        echo '<p>' . esc_html__('Questions customers asked from a product page. Reply here and it is emailed back to them.', 'hdwebmobile-product-enquiry') . '</p>';

        $this->render_enquiries_table();
    }

    private function render_enquiries_table()
    {
        $enquiries = HDENQ_Repository::get_all(100);

        if (empty($enquiries)) {
            echo '<p>' . esc_html__('No questions yet.', 'hdwebmobile-product-enquiry') . '</p>';
            return;
        }

        foreach ($enquiries as $enquiry) {
            $product = wc_get_product($enquiry->product_id);

            echo '<div class="hdenq-admin-card" style="background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:1em;margin-bottom:1em;max-width:800px;">';

            printf(
                '<p><strong>%s</strong> &mdash; %s (%s) &mdash; <em>%s</em></p>',
                esc_html($product ? $product->get_name() : __('(deleted product)', 'hdwebmobile-product-enquiry')),
                esc_html($enquiry->name),
                esc_html($enquiry->email),
                esc_html($enquiry->created_at)
            );

            printf('<p>%s</p>', nl2br(esc_html($enquiry->message)));

            if (HDENQ_Repository::STATUS_REPLIED === $enquiry->status && $enquiry->reply) {
                printf(
                    '<p style="opacity:.8;"><strong>%s</strong><br>%s</p>',
                    esc_html__('Your reply:', 'hdwebmobile-product-enquiry'),
                    nl2br(esc_html($enquiry->reply))
                );
            } else {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
                wp_nonce_field(self::NONCE_ACTION);
                echo '<input type="hidden" name="action" value="hdenq_reply" />';
                echo '<input type="hidden" name="enquiry_id" value="' . esc_attr($enquiry->id) . '" />';
                echo '<p><textarea name="reply" rows="3" style="width:100%;max-width:500px;" required></textarea></p>';
                echo '<button type="submit" class="button button-primary">' . esc_html__('Send reply', 'hdwebmobile-product-enquiry') . '</button>';
                echo '</form>';
            }

            echo '</div>';
        }
    }
}
