# Rongdhonu Fashion — Bangladesh Fashion E-commerce

A fully responsive, fully dynamic online fashion store built with **PHP 8+, MySQL/MariaDB and vanilla JavaScript**, themed for Bangladeshi fashion (Jamdani saree, salwar kameez, panjabi, lehenga, western, kids & accessories). Includes a complete **admin dashboard**.

> Note: this is a classic PHP/MySQL app. It runs on XAMPP/WAMP/LAMP or any PHP host. It is **not** deployable to Qoder Sites hosting (which cannot run PHP).

---

## Features

**Storefront (customers)**
- Home page with hero, category tiles, featured & new-arrival products
- Shop page with category filter, price range, "on sale" filter, search, sorting and pagination
- Product detail page with gallery, stock status, quantity picker, related products and **customer reviews** (star ratings)
- Session shopping cart (add / update / remove / clear) with free-shipping threshold
- Guest **or** registered checkout; Cash on Delivery, bKash, Nagad, Card
- Order confirmation + **order tracking** (by order ID + phone) with a visual status timeline
- Customer accounts: register, login, profile & password edit, order history
- Contact form + newsletter signup
- Prices in Bangladeshi Taka (৳), Asia/Dhaka timezone, mobile-first responsive layout

**Admin dashboard** (`/admin`)
- Login-protected area with stats: revenue, orders, products, customers, low-stock alerts
- Product management: create / edit / delete, image upload, sale price, stock, featured flag
- Category management (CRUD)
- Order management: filter by status, view details, update status (pending → processing → shipped → delivered / cancelled)
- Customer directory with order count & lifetime spend
- Contact-message inbox (read/unread, delete)
- Store settings editor (shipping cost, free-shipping threshold, contact info, social links, about text)

**Security**
- PDO prepared statements everywhere (SQL-injection safe)
- Password hashing with `password_hash()` / `password_verify()`
- CSRF tokens on all state-changing forms
- Output escaping helper `e()` to prevent XSS
- Upload validation (MIME whitelist, size limit) + `.htaccess` blocking script execution in `/uploads`

---

## Requirements

- PHP **8.0+** with `pdo_mysql`, `gd`, `mbstring`, `fileinfo` (all bundled in XAMPP)
- MySQL 5.7+ **or** MariaDB 10.3+
- Apache (XAMPP/WAMP) — or the PHP built-in server for quick testing

---

## Setup (XAMPP)

1. **Install XAMPP** and start **Apache** and **MySQL** from the XAMPP Control Panel.
2. **Copy the project** into your web root, e.g.:
   `C:\xampp\htdocs\e-com-2`
3. **Import the database.** Either:
   - Open **phpMyAdmin** → *Import* → choose `database/schema.sql` → Go.
     (The file auto-creates the `bd_fashion` database and seeds demo data.)
   - Or from a terminal:
     ```bash
     mysql -u root < database/schema.sql
     ```
4. **Check DB credentials** in `config/config.php`. The defaults match XAMPP:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'bd_fashion');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
5. **Open the site:**
   - Storefront: `http://localhost/e-com-2/`
   - Admin: `http://localhost/e-com-2/admin/`

   The base URL is detected automatically, so any sub-folder works.

### Quick test without Apache (PHP built-in server)
```bash
cd path/to/e-com-2
php -S localhost:8000
```
Then visit `http://localhost:8000` and `http://localhost:8000/admin`.

---

## Default logins

| Role     | URL                       | Email                    | Password      |
|----------|---------------------------|--------------------------|---------------|
| Admin    | `/admin/login.php`        | `admin@rongdhonu.com`    | `admin123`    |
| Customer | `/login.php`              | `customer@example.com`   | `customer123` |

> **Change these immediately** for any real deployment (Admin → Settings for store info; edit the `admins` table or add a password-change feature for admin credentials).

---

## Project structure

```
e-com-2/
├─ index.php               Storefront home
├─ shop.php                Product listing + filters/search/sort/pagination
├─ product.php             Product detail + reviews
├─ cart.php                Shopping cart
├─ cart-add.php            Add-to-cart handler (POST)
├─ checkout.php            Checkout / place order
├─ order-success.php       Order confirmation
├─ track-order.php         Order tracking
├─ login.php register.php logout.php account.php
├─ contact.php             Contact + newsletter
├─ config/
│   ├─ config.php          Constants, base-URL detection, bootstrap
│   └─ db.php              PDO connection
├─ includes/
│   ├─ functions.php       Helpers: auth, csrf, cart, uploads, formatting
│   ├─ header.php footer.php product-card.php
├─ admin/                  Admin dashboard (login-protected)
│   ├─ login.php logout.php index.php
│   ├─ products.php product-edit.php categories.php
│   ├─ orders.php order-view.php customers.php
│   ├─ messages.php settings.php
│   └─ includes/header.php footer.php
├─ assets/
│   ├─ css/style.css       Storefront styles (responsive)
│   ├─ css/admin.css       Admin styles (responsive)
│   ├─ js/main.js          Storefront interactions
│   └─ img/                Placeholder + bundled product SVGs
├─ uploads/                Product images uploaded via admin (protected)
└─ database/schema.sql     DB schema + seed data
```

---

## Customising

- **Store name / logo / colors:** edit `config/config.php` and the CSS `:root` variables in `assets/css/style.css`.
- **Shipping rules & contact info:** Admin → Settings.
- **Product images:** upload JPG/PNG/WEBP/GIF (max 4MB) via Admin → Products → Add/Edit.

## Deployment notes

- Set a strong DB password in `config/config.php`.
- Keep `.htaccess` files in place (they disable directory listing and block script execution in `/uploads`).
- For production, turn off `display_errors` in PHP.
