# Course Review Hub (PHP)

A simple PHP + MySQL web app for posting and browsing course reviews with tags. Includes authentication (register/login/logout), role-based access (Admin), course management, and review CRUD.

## Requirements
- PHP 8.0+
- MySQL 8.x (or compatible)
- Web server (Apache, Nginx) or `php -S` for local dev

## Setup
1. Clone or copy this folder to your web root.
2. Create a MySQL database (e.g., `course_review_hub`).
3. Import schema:
   - Using CLI: `mysql -u <user> -p <db> < database/schema.sql`
   - Or via phpMyAdmin: import `database/schema.sql`
4. Configure DB in `config/config.php` (or set env vars):
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
5. Start server (example):
   - `php -S localhost:8000 -t public`
6. Visit: `http://localhost:8000/`

## Default Admin
After seeding, you can promote a user to admin by setting `role = 'admin'` in `users` table.

## Structure
- `public/` public entry points (index, auth, pages, admin)
- `includes/` header/footer/auth helpers
- `config/` database config
- `assets/` static assets
- `database/` schema

## Notes
- Passwords stored with `password_hash()` (bcrypt default).
- PDO with prepared statements used for SQL queries.
- Session-based auth and role checks.
