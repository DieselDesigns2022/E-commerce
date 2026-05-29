# Reusable PHP Ecommerce System

A lightweight PHP 8 + MySQL ecommerce foundation for isolated small-business storefront installs. Each client should receive their own files and database (or a database prefix if you extend the installer later). The web server document root must point at `/public`.

## Completed / Working Features

QA-confirmed and currently supported:

- Admin login/logout and dashboard
- Store settings, shipping settings, and manual tax settings
- Product CRUD with categories, active/draft/hidden status, physical/digital type, inventory fields, and product image uploads
- Category CRUD
- Media library uploads
- Editable pages for About, Contact, FAQ, Privacy, Terms, Refund, and Shipping policies
- Coupons with date-only start/end fields, percent/fixed discounts, free-shipping coupons, minimum order, usage limits, and active toggle
- Storefront homepage, shop search/filter/sort, product page, cart, guest checkout, order success page, and admin order management
- Customer registration, login/logout, profile updates, password reset tokens, account dashboard, past orders, order detail pages, and customer downloads list
- Digital file attachment for digital products, private digital file storage, paid-order download grants, signed download links, download limits, expiration dates, and tracked download counts
- Stripe Checkout session creation, Stripe webhook signature verification, webhook idempotency, amount/currency checks, and paid-order processing
- Owner tools for license/status/version/maintenance metadata plus migration status placeholder

## Known Limitations

- The installer is still informational; setup is manual for now.
- Password reset links are displayed on-screen in local/test mode instead of being emailed.
- Digital download emails point customers to their account downloads; richer branded templates are still basic.
- Variants exist in the schema but do not yet have a full admin/storefront UI.
- Inventory is stored but not decremented on paid orders yet.
- Stripe refund/dispute/failed-payment handling is not complete.
- Maintenance mode is stored but not globally enforced yet.
- Automated tests are not yet included.

## Local Setup

### 1. Requirements

- PHP 8+
- MySQL or MariaDB
- PHP extensions: `pdo_mysql`, `curl`
- Git

On Ubuntu/Debian:

```bash
sudo apt update
sudo apt install -y git php php-cli php-mysql php-curl mysql-server
```

### 2. Clone and Configure

```bash
git clone <your-repo-url> ecommerce-test
cd ecommerce-test
cp .env.example .env
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Edit `.env`:

```env
APP_ENV=local
APP_URL=http://127.0.0.1:8000
APP_KEY=paste-generated-key-here
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_store
DB_USERNAME=ecommerce_user
DB_PASSWORD=change-me
MAIL_FROM_ADDRESS=orders@example.com
MAIL_FROM_NAME="Store Orders"
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
```

### 3. Create and Import the Database

```bash
sudo mysql
```

```sql
CREATE DATABASE ecommerce_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON ecommerce_store.* TO 'ecommerce_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
mysql -u ecommerce_user -p ecommerce_store < database/schema.sql
```

### 4. Create the First Owner Admin

Generate a password hash:

```bash
php -r 'echo password_hash("ChangeMe123!", PASSWORD_DEFAULT), PHP_EOL;'
```

Insert the owner admin:

```bash
mysql -u ecommerce_user -p ecommerce_store
```

```sql
INSERT INTO admins (name, email, password_hash, role, is_active, created_at, updated_at)
VALUES ('Store Owner', 'admin@example.com', 'PASTE_HASH_HERE', 'owner', 1, NOW(), NOW());
EXIT;
```

### 5. Run Locally

```bash
php -S 127.0.0.1:8000 -t public
```

Visit:

- Storefront: <http://127.0.0.1:8000/>
- Shop: <http://127.0.0.1:8000/shop.php>
- Admin: <http://127.0.0.1:8000/admin/login.php>
- Customer login: <http://127.0.0.1:8000/account/login.php>

Admin credentials are the email/password you inserted above, for example:

- Email: `admin@example.com`
- Password: `ChangeMe123!`

## Testing Checklist

1. Log in to `/admin/login.php`.
2. Create a category in `/admin/categories.php`.
3. Create an active physical product with an image in `/admin/products.php`.
4. Visit `/shop.php`, view the product, add it to cart, and complete checkout.
5. Confirm the order appears in `/admin/orders.php`.
6. Create a coupon in `/admin/coupons.php` and apply it in the cart.
7. Create a customer account at `/account/register.php` and confirm the dashboard works.
8. Create a digital product, attach a file in `/admin/digital-files.php`, purchase it with a logged-in customer account, then confirm the download appears in `/account/index.php` after payment/manual success.

## Stripe Test Mode

For local no-Stripe testing, leave `STRIPE_SECRET_KEY` blank. Checkout will create an order and redirect to a local manual-success URL.

For real Stripe test mode:

1. Add your test secret key to `.env`:

   ```env
   STRIPE_SECRET_KEY=sk_test_...
   ```

2. Use Stripe CLI to forward webhooks:

   ```bash
   stripe listen --forward-to http://127.0.0.1:8000/webhooks/stripe.php
   ```

3. Copy the printed `whsec_...` value into `.env`:

   ```env
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

4. Restart the PHP server and test checkout with Stripe card `4242 4242 4242 4242`.

## VPS Deployment Notes

1. Install nginx, MySQL/MariaDB, PHP-FPM, `php-mysql`, and `php-curl`.
2. Clone this repository to `/var/www/ecommerce`.
3. Copy `.env.example` to `.env` and set production values.
4. Import `database/schema.sql`.
5. Create the owner admin manually.
6. Set nginx root to `/var/www/ecommerce/public`.
7. Ensure `public/uploads`, `storage/digital-files`, and `storage/logs` are writable by the web server user.
8. Add HTTPS with Certbot before taking real payments.

## Important Routes

- `/` storefront
- `/shop.php` product catalog
- `/product.php?id=ID` product detail
- `/cart.php` cart
- `/checkout.php` checkout
- `/order-success.php` order success
- `/account/register.php` customer registration
- `/account/login.php` customer login
- `/account/index.php` customer dashboard/orders/downloads
- `/admin/login.php` admin login
- `/admin/products.php` products
- `/admin/categories.php` categories
- `/admin/orders.php` orders
- `/admin/coupons.php` coupons
- `/admin/pages.php` pages
- `/admin/media.php` media library
- `/admin/digital-files.php` digital files
- `/admin/settings.php` store settings
- `/admin/shipping.php` shipping/tax
- `/admin/super/index.php` owner tools
- `/webhooks/stripe.php` Stripe webhook

## Roadmap

- Full variant management UI
- Inventory decrementing and low-stock alerts
- Richer email templates and queued mail delivery
- Refund, dispute, and failed-payment handling
- Guarded web installer and migration runner
- Backup/update tooling
- Maintenance-mode middleware
- Automated tests
