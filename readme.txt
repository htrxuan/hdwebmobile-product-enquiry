=== HDWebmobile Product Enquiry ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, product enquiry, ask a question, pre-sale questions, contact form
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers ask a question about a product before buying. Every field is escaped at the point of output, everywhere.

== Description ==

HDWebmobile Product Enquiry adds a simple "Ask a question about this product" form to every product page. Questions are emailed to the store admin and listed under WooCommerce > HDWebmobile > Product Enquiries, where staff can reply -- the reply is emailed straight back to the customer.

= Why this plugin exists =
A competing "Product Enquiry for WooCommerce" plugin had an unauthenticated stored/reflected XSS vulnerability (CVE-2026-59512): customer-submitted enquiry content was rendered back out as HTML without proper escaping, so an attacker could submit a question containing a script payload that would execute in the browser of any staff member who viewed it in wp-admin -- a direct path to administrator account takeover. This plugin closes that exact vulnerability class by construction:

* Every customer-controlled value -- name, email, message, and later the admin's reply -- passes through `esc_html()` at the exact point it is printed as HTML, both in the admin enquiry list and anywhere it could ever be displayed, with no exceptions.
* Input is still sanitized on the way in (`sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`) as defense in depth, but the actual fix for this vulnerability class lives at output time, not input time -- sanitizing on save and forgetting to escape on display is exactly the mistake that caused the original CVE.
* Admin notification and customer reply emails are sent as plain text, so there is no HTML-rendering surface in the email itself either.
* A honeypot field filters automated spam submissions before they ever reach the database, with no CAPTCHA service or external dependency required.

= Key Features =
* "Ask a question about this product" form on every product page
* Admin email notification the moment a question is submitted
* All questions listed under WooCommerce > HDWebmobile > Product Enquiries
* Reply from wp-admin -- the customer receives it by email automatically
* Honeypot spam protection, no third-party service required

= Limitations (please read before installing) =
* Replies are sent by email only -- there is no customer-facing "my questions" account page in this version
* One question per submission -- no threaded back-and-forth conversation yet

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-product-enquiry` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. That's it -- the enquiry form appears automatically on every product page.

== How to Use ==

= 1. Customers ask a question =
On any product page, a customer fills in their name, email, and question, and submits it -- no account required.

= 2. You get notified =
The store admin email address receives a notification the moment a question comes in.

= 3. You reply from wp-admin =
Under WooCommerce > HDWebmobile > Product Enquiries, type a reply and send it -- it's emailed straight to the customer.

== Screenshots ==

1. The "Ask a question about this product" form on a product page.
2. The Product Enquiries list and reply form under WooCommerce > HDWebmobile.

== Changelog ==

= 1.0.0 =
* Initial release: product-page enquiry form, admin email notification, reply-by-email from wp-admin, honeypot spam protection, output escaped everywhere a customer-submitted value is displayed.
