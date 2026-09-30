# HomeStyle - E-Commerce & Store Management System

## Overview
A complete PHP/MySQL e-commerce platform with customer storefront and admin panel for managing products, orders, customers, and analytics.

## Features

**Customer:** Product browsing with filters, search, cart, wishlist, reviews, order history, bill download, coupon codes, profile management, demo payment (UPI/Card/Net Banking).

**Admin:** Dashboard with stats & charts, product/category CRUD, order management, customer management, coupon creation, review moderation, analytics, profile settings.

## Tech Stack
| Component | Technology |
|-----------|------------|
| Backend | PHP 8.2+ |
| Database | MySQL/MariaDB |
| Frontend | HTML, CSS, JS |
| Charts | Chart.js |
| Icons | Font Awesome 6.5 |

## Installation

1. Copy project to `htdocs/homestore/`
2. Create database `homestore` and import `homestore.sql`
3. Edit `db.php` with your credentials:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "homestore");
   ```
4. Create writable folders: `images/` and `uploads/profiles/`
5. Access: `http://localhost/homestore/`

## Database Tables
`users`, `products`, `product_variants`, `categories`, `cart`, `wishlist`, `orders`, `order_items`, `coupons`, `reviews`

## Key Files
- `index.php` – Storefront
- `admin_dashboard.php` – Admin home
- `cart.php` / `my_orders.php` – Customer orders
- `orders.php` / `products.php` – Admin management
- `analytics.php` – Sales insights
- `bill.php` – Invoice generation

## Security
- Password hashing (`password_hash`)
- Prepared statements
- Input sanitization
- Session-based admin checks
- File upload validation

## Order Flow
Cart → Checkout → "Waiting to be shipped" → Admin updates status → Delivered → Review

## Limitations
- Demo payment only (no real gateway)
- Emails logged to file, not sent
- Basic LIKE search

## License
Educational use.

**Version:** 1.0.0 | **Updated:** April 2026
