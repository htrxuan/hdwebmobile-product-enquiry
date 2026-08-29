# HDWebmobile Product Enquiry

Let customers ask a question about a product before buying. Every field is escaped at the point of output, everywhere.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-product-enquiry/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Product Enquiry adds a simple "Ask a question about this product" form to every product page. Questions are emailed to the store admin and listed under WooCommerce > HDWebmobile > Product Enquiries, where staff can reply -- the reply is emailed straight back to the customer.

## Why this plugin exists

A competing "Product Enquiry for WooCommerce" plugin had an unauthenticated stored/reflected XSS vulnerability (CVE-2026-59512): customer-submitted enquiry content was rendered back out as HTML without proper escaping, so an attacker could submit a question containing a script payload that would execute in the browser of any staff member who viewed it in wp-admin -- a direct path to administrator account takeover. This plugin closes that exact vulnerability class by construction:

* Every customer-controlled value -- name, email, message, and later the admin's reply -- passes through `esc_html()` at the exact point it is printed as HTML, both in the admin enquiry list and anywhere it could ever be displayed, with no exceptions.
* Input is still sanitized on the way in (`sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`) as defense in depth, but the actual fix for this vulnerability class lives at output time, not input time -- sanitizing on save and forgetting to escape on display is exactly the mistake that caused the original CVE.
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

