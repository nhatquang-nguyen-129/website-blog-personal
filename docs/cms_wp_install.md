# WordPress Setup on Windows with CLI-first

This document describes how to set up **WordPress** on Windows with a focus on:

- Lightweight installation
- Minimal GUI usage
- Suitable for WordPress custom feature

---

## 1. Install WordPress

### 1.1. Download wordpress.zip

- Redirect `path` to public folder which contains WordPress source code written in PHP
```bash
cd public
```

- Download `lastest version` from `wordpress.org`
```bash
curl -o wordpress.zip https://wordpress.org/latest.zip
```

### 1.2. Relocate to source code folder

- Unzip downloaded WordPress folder
```bash
tar -xf wordpress.zip
```

- Relocate unzipped WordPress to root folder
```bash
Move-Item wordpress\* .
Remove-Item wordpress -Recurse
Remove-Item wordpress.zip
```

## 2. Config WordPress

### 2.1. Config WordPress database using MySQL

- Copy `wp-config.php` from `wp-config-sample.php` template
```bash
copy wp-config-sample.php wp-config.php`
```

- Config MySQL account in `wp-config.php`
```bash
define('DB_NAME', 'blog');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin');
define('DB_HOST', '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
```
- Config Debug mode
```bash
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

### 2.2. Setup WordPress with UI

- Verfity WordPress version and go to setup UI:
```bash
php -S localhost:8000 -t public
```