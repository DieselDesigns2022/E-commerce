# Reusable PHP Ecommerce System

A lightweight PHP 8 + MySQL ecommerce foundation for isolated small-business storefront installs. Each client should receive their own files and database (or a database prefix if you extend the installer later).

## Current Scope

This build now includes a broad, install-ready foundation for the full ecommerce brief:

- Public storefront: homepage, shop search/filter/sort, product detail, pages, cart, checkout, order success, customer-account placeholders, and secure-download placeholder.
- Admin dashboard: login/logout, dashboard cards, products, categories, orders, customers, coupons, pages, media, digital files, shipping/tax, settings, and owner tools.
- Core app: environment config, PDO prepared statements, auth, CSRF, helpers, mail, Stripe Checkout/session helpers, webhook signature verification, and modular models.
- Database: schema for admins, customers, cart sessions, products, product images, variants, categories, product categories, coupons, coupon redemptions, orders, order items, digital files, downloads, pages, settings, media, password resets, activity logs, and migrations.
- TODO placeholders where deeper work is intentionally scaffolded: full customer auth, tokenized digital downloads, guarded web installer, migration runner, and advanced analytics.

## Installation

1. Copy `.env.example` to `.env` and update database, app URL, mail, and Stripe values.
2. Point your web server document root to `/public`.
3. Import the schema:

   ```bash
   mysql -u your_user -p your_database < database/schema.sql
   ```

4. Create the first owner/admin user by inserting a bcrypt password hash into `admins.password_hash` with `role = 'owner'`.
5. Visit `/admin/login.php` to manage the store.

## Important Paths

- Storefront: `/`
- Shop: `/shop.php`
- Cart: `/cart.php`
- Checkout: `/checkout.php`
- Admin login: `/admin/login.php`
- Admin dashboard: `/admin/index.php`
- Products: `/admin/products.php`
- Categories: `/admin/categories.php`
- Orders: `/admin/orders.php`
- Coupons: `/admin/coupons.php`
- Pages: `/admin/pages.php`
- Media library: `/admin/media.php`
- Digital files: `/admin/digital-files.php`
- Shipping/tax: `/admin/shipping.php`
- Owner tools: `/admin/super/index.php`
- Stripe webhook: `/webhooks/stripe.php`
- Installer placeholder: `/install.php`

## Next Hardening Tasks

- Add a guarded installer that writes `.env`, imports schema, creates the owner admin, and self-locks.
- Complete customer login/register/password reset and account order/download pages.
- Replace TODO download endpoint with signed token validation, streaming, limits, and expiration.
- Add robust Stripe line-item reconciliation, order email templates, and webhook idempotency.
- Add automated tests and migration runner.
