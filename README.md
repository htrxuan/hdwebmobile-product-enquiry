# HDWebmobile Product Enquiry

Let customers ask a question about a product before buying. Every field is escaped at the point of output, everywhere.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-product-enquiry/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Product Enquiry adds a simple "Ask a question about this product" form to every product page. Questions are emailed to the store admin and listed under WooCommerce > HDWebmobile > Product Enquiries, where staff can reply -- the reply is emailed straight back to the customer.

## Why this plugin exists

An enquiry form stores text typed by strangers and shows it to your staff in wp-admin, so that text must never be able to run as code in an administrator's browser (stored XSS). This plugin is built so it cannot:

* Every customer-controlled value -- name, email, message, and later the admin's reply -- passes through `esc_html()` at the exact point it is printed as HTML, both in the admin enquiry list and anywhere it could ever be displayed, with no exceptions.
* Input is still sanitized on the way in (`sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`) as defense in depth, but the actual fix for this vulnerability class lives at output time, not input time -- sanitizing on save and forgetting to escape on display is exactly the mistake that causes it.
* Admin notification and customer reply emails are sent as plain text, so there is no HTML-rendering surface in the email itself either.
* A honeypot field filters automated spam submissions before they ever reach the database, with no CAPTCHA service or external dependency required.

## Features

* "Ask a question about this product" form on every product page
* Admin email notification the moment a question is submitted
* All questions listed under WooCommerce > HDWebmobile > Product Enquiries
* Reply from wp-admin -- the customer receives it by email automatically
* Honeypot spam protection, no third-party service required

## Development

Standard WordPress plugin structure:

```
hdwebmobile-product-enquiry.php    Bootstrap
includes/class-hdenq-activator.php
includes/class-hdenq-admin.php
includes/class-hdenq-core.php
includes/class-hdenq-frontend.php
includes/class-hdenq-hub.php
includes/class-hdenq-repository.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

