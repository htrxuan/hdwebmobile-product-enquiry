<?php

namespace htrxuan\hdenq;

if (!defined('ABSPATH')) {
    exit;
}

final class HDENQ_Frontend
{

    const NONCE_ACTION = 'hdenq_submit';

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
        add_action('woocommerce_single_product_summary', array($this, 'render_form'), 35);
        add_action('template_redirect', array($this, 'handle_submission'));
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_assets'));
    }

    public function maybe_enqueue_assets()
    {
        if (is_product()) {
            wp_enqueue_style('hdenq-frontend', HDENQ_PLUGIN_URL . 'assets/css/hdenq-frontend.css', array(), HDENQ_VERSION);
        }
    }

    public function render_form()
    {
        global $product;
        if (!$product) {
            return;
        }

        if (isset($_GET['hdenq_sent'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag from a post/redirect/get after the actual submission already passed nonce verification in handle_submission().
            echo '<div class="hdenq-notice hdenq-success">' . esc_html__('Thanks! Your question has been sent to the seller.', 'hdwebmobile-product-enquiry') . '</div>';
            return;
        }

        $current_user = wp_get_current_user();
        $default_name  = $current_user->exists() ? $current_user->display_name : '';
        $default_email = $current_user->exists() ? $current_user->user_email : '';

        echo '<div class="hdenq-enquiry-box">';
        echo '<h3>' . esc_html__('Ask a question about this product', 'hdwebmobile-product-enquiry') . '</h3>';
        echo '<form method="post" class="hdenq-enquiry-form">';
        wp_nonce_field(self::NONCE_ACTION, 'hdenq_nonce');
        printf('<input type="hidden" name="hdenq_product_id" value="%d" />', esc_attr($product->get_id()));

        // Honeypot: a field real visitors never see or fill, hidden purely with CSS
        // (not type="hidden", since some bots skip those) -- any non-empty value
        // here means the submission is automated, so it is silently dropped in
        // handle_submission() without ever touching the database.
        echo '<div class="hdenq-honeypot" aria-hidden="true"><label>' . esc_html__('Leave this field empty', 'hdwebmobile-product-enquiry') . '<input type="text" name="hdenq_website" tabindex="-1" autocomplete="off" /></label></div>';

        printf(
            '<p><label for="hdenq_name">%s</label><input type="text" id="hdenq_name" name="hdenq_name" value="%s" required /></p>',
            esc_html__('Your name', 'hdwebmobile-product-enquiry'),
            esc_attr($default_name)
        );
        printf(
            '<p><label for="hdenq_email">%s</label><input type="email" id="hdenq_email" name="hdenq_email" value="%s" required /></p>',
            esc_html__('Your email', 'hdwebmobile-product-enquiry'),
            esc_attr($default_email)
        );
        printf(
            '<p><label for="hdenq_message">%s</label><textarea id="hdenq_message" name="hdenq_message" rows="4" required></textarea></p>',
            esc_html__('Your question', 'hdwebmobile-product-enquiry')
        );
        echo '<button type="submit" class="button">' . esc_html__('Send question', 'hdwebmobile-product-enquiry') . '</button>';
        echo '</form>';
        echo '</div>';
    }

    public function handle_submission()
    {
        if (!is_product() || !isset($_POST['hdenq_nonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hdenq_nonce'])), self::NONCE_ACTION)) {
            return;
        }

        if (!empty($_POST['hdenq_website'])) {
            return; // Honeypot tripped -- treat as spam, no error shown to avoid tipping off the bot.
        }

        $product_id = isset($_POST['hdenq_product_id']) ? absint($_POST['hdenq_product_id']) : 0;
        $product    = $product_id ? wc_get_product($product_id) : null;
        if (!$product) {
            return;
        }

        $name    = isset($_POST['hdenq_name']) ? sanitize_text_field(wp_unslash($_POST['hdenq_name'])) : '';
        $email   = isset($_POST['hdenq_email']) ? sanitize_email(wp_unslash($_POST['hdenq_email'])) : '';
        $message = isset($_POST['hdenq_message']) ? sanitize_textarea_field(wp_unslash($_POST['hdenq_message'])) : '';

        if ('' === $name || !is_email($email) || '' === $message) {
            return;
        }

        $user_id = get_current_user_id();
        HDENQ_Repository::create($product_id, $user_id, $name, $email, $message);

        $this->notify_admin($product, $name, $email, $message);

        wp_safe_redirect(add_query_arg('hdenq_sent', 1, get_permalink($product_id)));
        exit;
    }

    /**
     * The admin notification email is plain text (wp_mail() default content type),
     * so there is no HTML-rendering/XSS surface in the email itself -- the actual
     * output-escaping fix that matters is in HDENQ_Admin, where this same message
     * gets displayed as HTML in wp-admin.
     */
    private function notify_admin($product, $name, $email, $message)
    {
        $subject = sprintf(
            /* translators: %s: product name */
            __('New product question: %s', 'hdwebmobile-product-enquiry'),
            $product->get_name()
        );

        $body = sprintf(
            "%s (%s) asked a question about \"%s\":\n\n%s\n\nReply from WooCommerce > HDWebmobile > Product Enquiries.",
            $name,
            $email,
            $product->get_name(),
            $message
        );

        wp_mail(get_option('admin_email'), $subject, $body);
    }
}
